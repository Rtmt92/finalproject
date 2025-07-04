#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"   # ← votre clé SSH locale

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
# 3) GÉNÉRATION + EXÉCUTION DU SCRIPT À DISTANCE
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash -s << 'EOF_REMOTE'
#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "-> Recréation de la base $DB"
sudo mysql -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\`;"

echo "-> Import du dump SQL"
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [ -n "\$SQL_FILE" ]; then
  sudo mysql "\$DB" < "\$SQL_FILE"
else
  echo "⚠️ Aucun dump trouvé dans \$DEST"
fi

echo "-> Installation des dépendances PHP"
cd "\$DEST/backend"
sudo composer install --no-dev --optimize-autoloader

echo "-> Build du front-end"
cd "\$DEST/frontend"
sudo npm ci
sudo npm run build

echo "-> Déploiement des fichiers statiques"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "-> Ajustement des permissions"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

echo "-> Redémarrage de Nginx"
sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF_REMOTE
