#!/usr/bin/env bash
set -euo pipefail

USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"

MYSQL_ROOT_PWD="${MYSQL_ROOT_PWD:-admin}"
DB_NAME="${DB_NAME:-dejavu}"

echo "🚀 Deployment sur $USER@$HOST:$DEST …"

# 1) Créer le dossier sur la VM
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST << EOF
  sudo mkdir -p "$DEST"
  sudo chown -R "$USER":"$USER" "$DEST"
EOF

# 2) Rsync
rsync -avz \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ $USER@$HOST:"$DEST"

# 3) Déploiement sur la VM
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
  set -euo pipefail

  echo "• Installation de MySQL si manquant"
  if ! command -v mysql &> /dev/null; then
    sudo apt-get update
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
  fi

  echo "• Activation et démarrage de MySQL"
  sudo systemctl enable mysql
  sudo systemctl start mysql

  echo "• Configuration de root pour utiliser un mot de passe"
  sudo mysql << SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_ROOT_PWD}';
FLUSH PRIVILEGES;
SQL

  echo "• Création de la base '${DB_NAME}'"
  sudo mysql -u root -p"${MYSQL_ROOT_PWD}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"

  SQL_FILE=\$(ls "$DEST"/*.sql | head -n1)
  if [ -z "\$SQL_FILE" ]; then
    echo "❌ Aucun fichier .sql trouvé dans $DEST" >&2
    exit 1
  fi
  echo "• Import de la base depuis \$SQL_FILE"
  sudo mysql -u root -p"${MYSQL_ROOT_PWD}" "${DB_NAME}" < "\$SQL_FILE"

  echo "• Installation du backend PHP"
  cd "$DEST/backend"
  composer install --no-dev --optimize-autoloader

  echo "• Build React"
  cd "$DEST/frontend"
  npm install
  npm run build

  echo "• Déploiement des assets statiques"
  sudo rm -rf /var/www/html/*
  sudo cp -r build/* /var/www/html/

  echo "• Permissions"
  sudo chown -R www-data:www-data /var/www/html
  sudo chmod -R 755 /var/www/html

  echo "• Redémarrage du serveur web"
  sudo systemctl restart apache2 || sudo systemctl restart nginx

  echo "✅ Déploiement terminé !"
EOF
