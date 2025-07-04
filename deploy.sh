#!/usr/bin/env bash
set -euo pipefail

#################################################
# 1) CONFIGURATION – A ADAPTER SELON TON ENV
#################################################
USER="azureuser"
HOST="${HOST:-4.233.136.179}"      # on pourra surcharger en CI via $HOST
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"            # <— on passe sur id_rsa, pas DejaVu_key.pem
DB="dejavu"

#################################################
# 2) RSYNC DES FICHIERS
#################################################
echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

#################################################
# 3) EXÉCUTION À DISTANCE
#################################################
ssh -i "$KEY" -o StrictHostKeyChecking=no \
    "$USER@$HOST" sudo bash -s << 'EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "→ (Re)création de la base '$DB'"
mysql -e "DROP DATABASE IF EXISTS \\\`${DB}\\\`; CREATE DATABASE \\\`${DB}\\\`;"

SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [ -n "\$SQL_FILE" ]; then
  echo "→ Import \$SQL_FILE"
  mysql "\$DB" < "\$SQL_FILE"
else
  echo "⚠️ Pas de dump SQL trouvé, j'ignore l'import"
fi

echo "→ Composer (backend)"
if ! command -v composer &>/dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

echo "→ npm (frontend)"
if ! command -v npm &>/dev/null; then
  apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi
cd "\$DEST/frontend"
npm ci
npm run build

echo "→ Déploiement statique"
mkdir -p /var/www/html
rm -rf /var/www/html/*
cp -r build/* /var/www/html/

echo "→ Permissions & reload"
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF
