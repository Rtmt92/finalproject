#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"   # ← Chemin vers ta clé PEM
DB_NAME="dejavu"

echo "🚀 Déploiement vers $USER@$HOST:$DEST …"

########################
# 2) RSYNC DU PROJET
########################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

########################
# 3) SCRIPT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash -s << 'REMOTE_EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "→ (Re)création de la base '$DB'"
sudo mysql -e "DROP DATABASE IF EXISTS \`${DB}\`; CREATE DATABASE \`${DB}\`;"

# Import du dump
if sql=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1); then
  echo "→ Import depuis \$sql"
  sudo mysql "\$DB" < "\$sql"
else
  echo "❌ Aucun .sql dans \$DEST"
fi

# Composer
if ! command -v composer &>/dev/null; then
  echo "→ Installation de Composer"
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# Node.js / npm
if ! command -v npm &>/dev/null; then
  echo "→ Installation Node.js & npm"
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

echo "→ Back-end PHP"
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

echo "→ Front-end React"
cd "\$DEST/frontend"
npm ci
npm run build

echo "→ Déploiement statique sous /var/www/html"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "→ Permissions & reload nginx"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
REMOTE_EOF
