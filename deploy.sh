#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"    # ← Chemin vers ta clé PEM
DB_NAME="dejavu"
DB_USER="dejavu"
DB_PASS="admin"

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
# 3) GÉNÉRATION DU SCRIPT DISTANT + EXÉCUTION
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST sudo bash -s << 'EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"
DB_USER="dejavu"
DB_PASS="admin"

# --- 1) Installer MySQL si besoin ---
if ! command -v mysql &>/dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

# --- 2) Démarrer et activer MySQL ---
systemctl enable --now mysql

# --- 3) (Re)création de la BDD et de l’utilisateur ---
mysql -e "
DROP DATABASE IF EXISTS \\\`${DB_NAME}\\\`;
CREATE DATABASE \\\`${DB_NAME}\\\`;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \\\`${DB_NAME}\\\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
"

# --- 4) Import du dump SQL ---
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -z "\$SQL_FILE" ]; then
  echo "❌ Aucun .sql trouvé dans \$DEST" >&2
  exit 1
fi
mysql "\$DB_NAME" < "\$SQL_FILE"

# --- 5) Installer Composer si besoin ---
if ! command -v composer &>/dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# --- 6) Installer Node.js + npm si besoin ---
if ! command -v npm &>/dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

# --- 7) Backend PHP ---
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# --- 8) Frontend React ---
cd "\$DEST/frontend"
npm ci
npm run build

# --- 9) Déployer les assets statiques ---
mkdir -p /var/www/html
rm -rf /var/www/html/*
cp -r build/* /var/www/html/

# --- 10) Ajuster les droits et redémarrer nginx ---
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF
