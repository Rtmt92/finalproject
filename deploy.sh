#!/usr/bin/env bash
set -euo pipefail

USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"

# Copier votre SQL mis à jour dans le dépôt : 'dejavu.sql'
# et n'utilisez plus MYSQL_ROOT_PWD pour root.
DB_NAME="dejavu"
APP_DB_USER="dejavu"
APP_DB_PWD="admin"

echo "🚀 Deployment sur $USER@$HOST:$DEST …"

# 1) Créer le dossier
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

# 3) Sur la VM : installer MySQL, créer la BDD + user, importer le SQL
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << EOF
  set -euo pipefail

  # Installer MySQL si nécessaire
  if ! command -v mysql &> /dev/null; then
    sudo apt-get update
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
  fi

  # Démarrer MySQL
  sudo systemctl enable mysql
  sudo systemctl start mysql

  # Créer base + user applicatif
  sudo mysql << SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
CREATE USER IF NOT EXISTS '${APP_DB_USER}'@'localhost' IDENTIFIED BY '${APP_DB_PWD}';
GRANT ALL ON \`${DB_NAME}\`.* TO '${APP_DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

  # Importer le SQL de structure/données
  SQL_FILE=\$(ls "$DEST"/*.sql | head -n1)
  if [ -z "\$SQL_FILE" ]; then
    echo "❌ Aucun .sql trouvé dans $DEST" >&2
    exit 1
  fi
  echo "• Import de la base depuis \$SQL_FILE"
  sudo mysql \`${DB_NAME}\` < "\$SQL_FILE"

  # Backend PHP
  cd "$DEST/backend"
  composer install --no-dev --optimize-autoloader

  # Frontend React
  cd "$DEST/frontend"
  npm install
  npm run build

  # Déploiement statique
  sudo rm -rf /var/www/html/*
  sudo cp -r build/* /var/www/html/

  # Permissions + restart
  sudo chown -R www-data:www-data /var/www/html
  sudo chmod -R 755 /var/www/html
  sudo systemctl restart apache2 || sudo systemctl restart nginx

  echo "✅ Déploiement terminé !"
EOF
