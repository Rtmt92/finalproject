#!/usr/bin/env bash
set -euo pipefail

#################################################
# 1) CONFIGURATION – A ADAPTER SELON TON ENV
#################################################
USER="azureuser"
HOST="${HOST:-4.233.136.179}"      # on peut surcharger via env en CI
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"            # on écrit la clé dans ~/.ssh/id_rsa en CI
DB="dejavu"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

#################################################
# 2) RSYNC DES FICHIERS
#################################################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
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
# on passe la SQL dans des quotes simples pour éviter toute interprétation shell
mysql -e 'DROP DATABASE IF EXISTS `'"$DB"'`; CREATE DATABASE `'"$DB"'`;'

SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [ -n "\$SQL_FILE" ]; then
  echo "→ Import du dump \$SQL_FILE"
  mysql "\$DB" < "\$SQL_FILE"
else
  echo "⚠️ Aucun dump SQL trouvé, j'ignore l'import"
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
