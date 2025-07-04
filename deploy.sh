#!/usr/bin/env bash
set -euo pipefail

########################
# 1) VARIABLES
########################
USER="azureuser"
HOST="${AZURE_VM_IP}"      # via env de GitHub Actions
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"

DB_NAME="dejavu"
DB_USER="dejavu"
DB_PASS="admin"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

########################
# 2) RSYNC DU PROJET
########################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

########################
# 3) CRÉER & LANCER LE SCRIPT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"
DB_USER="dejavu"
DB_PASS="admin"

# 1) Installer MySQL
if ! command -v mysql &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

# 2) Activer MySQL
sudo systemctl enable --now mysql

# 3) (Re)création base & user
sudo mysql <<SQL
DROP DATABASE IF EXISTS $DB_NAME;
CREATE DATABASE $DB_NAME;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

# 4) Import du dump si présent
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [ -n "\$SQL_FILE" ]; then
  sudo mysql "\$DB_NAME" < "\$SQL_FILE"
else
  echo "ℹ️ Aucun dump .sql à importer."
fi

# 5) Installer Composer
if ! command -v composer &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# 6) Installer Node.js + npm
if ! command -v npm &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

# 7) Back-end
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# 8) Front-end
cd "\$DEST/frontend"
npm ci
npm run build

# 9) Déployer le build React
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

# 10) Droits & reload
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF
