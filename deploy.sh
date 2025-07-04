#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"   # ← Assurez-vous que c’est bien votre clé privée

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
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash -s << 'EOF'
cat > /tmp/deploy_full.sh << 'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "-> Recreate database"
sudo mysql -e "DROP DATABASE IF EXISTS $DB; CREATE DATABASE $DB;"

echo "-> Import SQL dump"
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -f "\$SQL_FILE" ]; then
  sudo mysql "\$DB" < "\$SQL_FILE"
else
  echo "⚠️ No SQL dump found in \$DEST"
fi

echo "-> Install PHP dependencies"
cd "\$DEST/backend"
sudo composer install --no-dev --optimize-autoloader

echo "-> Build frontend"
cd "\$DEST/frontend"
sudo npm ci
sudo npm run build

echo "-> Deploy static files"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "-> Set permissions"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

echo "-> Restart nginx"
sudo systemctl restart nginx

echo "✅ Deployment complete!"
SCRIPT

chmod +x /tmp/deploy_full.sh
sudo /tmp/deploy_full.sh
EOF
