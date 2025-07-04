#!/usr/bin/env bash
set -euo pipefail

########################
# 0) CONFIGURATION CI
########################
# En CI, on fait :
#   echo "${{ secrets.AZURE_SSH_KEY }}" > ~/.ssh/id_rsa
#   chmod 600 ~/.ssh/id_rsa
#   ssh-keyscan -H ${{ secrets.AZURE_VM_IP }} >> ~/.ssh/known_hosts
#
USER="azureuser"
HOST="${AZURE_VM_IP:-4.233.136.179}"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"
DB="dejavu"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

########################
# 1) RSYNC
########################
rsync -az --delete \
  --exclude node_modules \
  --exclude vendor \
  --exclude .env \
  --exclude frontend/build \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

########################
# 2) DÉPLOIEMENT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no "$USER@$HOST" sudo bash -s << 'EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "→ (Re)création de la base '$DB'"
# on utilise mysql -e '…' pour ne pas truffer de backslashes
mysql -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\`;"

# repérer le dump SQL
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [ -n "\$SQL_FILE" ]; then
  echo "→ Import du dump \$SQL_FILE"
  mysql "\$DB" < "\$SQL_FILE"
else
  echo "⚠️ Aucun .sql trouvé, j'ignore l'import"
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
