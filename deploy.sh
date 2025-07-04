#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"
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
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"

# a) Vérifier si la DB existe déjà
DB_EXISTS=$(sudo mysql -sNe "SHOW DATABASES LIKE '$DB_NAME';")
if [ -z "$DB_EXISTS" ]; then
  echo "• Base '$DB_NAME' absente : import complet"
  # créer la base et importer
  sudo mysql <<SQL
CREATE DATABASE \`$DB_NAME\`;
SQL
  SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
  [ -z "\$SQL_FILE" ] && { echo "❌ Aucun .sql trouvé dans \$DEST"; exit 1; }
  sudo mysql "$DB_NAME" < "\$SQL_FILE"
else
  echo "• Base '$DB_NAME' déjà présente, on ne modifie pas le schéma"
  # optionnel : ré-import partiel ou skip
  SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
  if [ -n "\$SQL_FILE" ]; then
    echo "→ Import des données (tables existantes seront écrasées si définies en dump)…"
    sudo mysql "$DB_NAME" < "\$SQL_FILE"
  fi
fi

# b) Installer les dépendances PHP
if ! command -v composer &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# c) Installer Node.js + npm si besoin
if ! command -v npm &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

# d) Back-end PHP
cd "$DEST/backend"
composer install --no-dev --optimize-autoloader

# e) Front-end React
cd "$DEST/frontend"
npm ci
npm run build

# f) Déployer les assets statiques
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

# g) Permissions & restart
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF
