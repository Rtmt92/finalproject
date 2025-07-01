#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION LOCALE
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"

echo "🚀 Déploiement vers $USER@$HOST:$DEST …"

########################
# 2) RSYNC DU PROJET
########################
rsync -az \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \        # ← on exclut le build React
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

########################
# 3) DÉPLOIEMENT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST sudo bash -s << 'EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"
DB_USER="dejavu"
DB_PASS="admin"

# 1) Installer MySQL si besoin
if ! command -v mysql &> /dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

# 2) Démarrer MySQL
systemctl enable --now mysql

# 3) (Re)création de la BDD et de l’utilisateur
mysql <<SQL
DROP DATABASE IF EXISTS \`$DB_NAME\`;
CREATE DATABASE \`$DB_NAME\`;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

# 4) Import SQL
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -z "\$SQL_FILE" ]; then
  echo "❌ Aucun .sql trouvé dans \$DEST" >&2
  exit 1
fi
mysql "\$DB_NAME" < "\$SQL_FILE"

# 5) Installer Composer si besoin
if ! command -v composer &> /dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# 6) Installer Node.js + npm si besoin
if ! command -v npm &> /dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

# 7) Backend PHP
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# 8) Frontend React
cd "\$DEST/frontend"
npm ci
npm run build

# 9) Assets statiques
mkdir -p /var/www/html
rm -rf /var/www/html/*
cp -r build/* /var/www/html/

# 10) Redémarrer Nginx
systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF
