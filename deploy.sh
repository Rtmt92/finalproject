#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"

# Injectées par GitHub Actions, ou valeurs par défaut
MYSQL_ROOT_PWD="${MYSQL_ROOT_PWD:-admin}"
DB_NAME="${DB_NAME:-dejavu}"

echo "🚀 Deployment sur $USER@$HOST:$DEST …"

########################
# 2) CRÉER LE DOSSIER DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST << EOF
  sudo mkdir -p "$DEST"
  sudo chown -R "$USER":"$USER" "$DEST"
EOF

########################
# 3) RSYNC DU PROJET
########################
rsync -avz \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ $USER@$HOST:"$DEST"

########################
# 4) SCRIPT DISTANT (avec injection des vars)
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << EOF
  set -euo pipefail

  #--- importer les variables
  MYSQL_ROOT_PWD="${MYSQL_ROOT_PWD}"
  DB_NAME="${DB_NAME}"

  echo "• Installation de MySQL si manquant"
  if ! command -v mysql &> /dev/null; then
    sudo apt-get update
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
  fi

  echo "• Démarrage et activation de MySQL"
  sudo systemctl enable mysql
  sudo systemctl start mysql

  echo "• Configuration de root pour utiliser un mot de passe"
  sudo mysql << SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '\$MYSQL_ROOT_PWD';
FLUSH PRIVILEGES;
SQL

  echo "• Création et import de la base '\$DB_NAME'"
  sudo mysql -u root -p"\$MYSQL_ROOT_PWD" -e "CREATE DATABASE IF NOT EXISTS \\\`\$DB_NAME\\\`;"
  sudo mysql -u root -p"\$MYSQL_ROOT_PWD" "\$DB_NAME" < "$DEST/dejavu.sql"

  echo "• Installation du backend PHP"
  cd "$DEST/backend"
  composer install --no-dev --optimize-autoloader

  echo "• Build React"
  cd "$DEST/frontend"
  npm install
  npm run build

  echo "• Déploiement du build"
  sudo rm -rf /var/www/html/*
  sudo cp -r build/* /var/www/html/

  echo "• Permissions"
  sudo chown -R www-data:www-data /var/www/html
  sudo chmod -R 755 /var/www/html

  echo "• Redémarrage du serveur web"
  sudo systemctl restart apache2 || sudo systemctl restart nginx

  echo "✅ Déploiement terminé !"
EOF
