#!/usr/bin/env bash
set -euo pipefail

USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

# 1) Rsync
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

# 2) Copie et exécution du script complet à distance
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
# Sauvegarde l'ancien script si présent
sudo mv /tmp/deploy_full.sh /tmp/deploy_full.sh.bak 2>/dev/null || true

# Écrit le nouveau script
cat > /tmp/deploy_full.sh << 'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "→ (Re)création de la BDD"
/usr/bin/mysql -e "DROP DATABASE IF EXISTS \\\`${DB}\\\`; CREATE DATABASE \\\`${DB}\\\`;"

SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -f "\$SQL_FILE" ]; then
  echo "→ Import du dump"
/usr/bin/mysql "\$DB" < "\$SQL_FILE"
else
  echo "❌ Aucun .sql trouvé dans \$DEST"
fi

echo "→ Installation back-end (Composer)"
if ! command -v composer &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

echo "→ Build front-end (npm)"
if ! command -v npm &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi
cd "\$DEST/frontend"
npm ci
npm run build

echo "→ Déploiement des fichiers statiques"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "→ Permissions"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

echo "→ Redémarrage de Nginx"
sudo systemctl restart nginx

echo "✅ Déploiement complet terminé !"
SCRIPT

# Rendre exécutable et lancer
sudo chmod +x /tmp/deploy_full.sh
sudo /tmp/deploy_full.sh
EOF
