#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"                            # Nom d'utilisateur SSH
HOST="4.233.136.179"                        # IP publique de la VM
DEST="/var/www/dejavu"                      # Racine de votre projet sur la VM
KEY="$HOME/.ssh/id_rsa"                     # Chemin vers votre clé privée

# Credentials MySQL (passés depuis GitHub Actions via env)
MYSQL_ROOT_PWD="${MYSQL_ROOT_PWD:-Thibault0709}"
DB_NAME="${DB_NAME:-dejavu}"

echo "🚀 Début du déploiement sur $USER@$HOST:$DEST …"

########################
# 2) RSYNC DU PROJET
########################
rsync -avz \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename $KEY)" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ $USER@$HOST:$DEST

########################
# 3) COMMANDES DISTANTES
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
  set -euo pipefail
  echo "🔧 Configuration sur la VM distante…"

  # 3.1 Démarrer MySQL
  sudo systemctl start mysql

  # 3.2 Importer / créer la BDD
  sudo mysql -u root -p"$MYSQL_ROOT_PWD" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;"
  sudo mysql -u root -p"$MYSQL_ROOT_PWD" "$DB_NAME" < "$DEST/dejavu.sql"

  # 3.3 Backend PHP
  cd "$DEST/backend"
  composer install --no-dev --optimize-autoloader

  # 3.4 Frontend React
  cd "$DEST/frontend"
  npm install
  npm run build

  # 3.5 Déployer le build statique
  sudo rm -rf /var/www/html/*
  sudo cp -r build/* /var/www/html/

  # 3.6 Permissions
  sudo chown -R www-data:www-data /var/www/html
  sudo chmod -R 755 /var/www/html

  # 3.7 Redémarrer le serveur web
  sudo systemctl restart apache2 || sudo systemctl restart nginx

  echo "✅ Déploiement terminé sur la VM !"
EOF
