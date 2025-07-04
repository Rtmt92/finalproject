#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION LOCALE
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"    # ← Chemin vers votre clé PEM

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
# 3) GÉNÉRATION DU SCRIPT REMOTE (full_deploy.sh)
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
cat > /tmp/full_deploy.sh << 'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "→ (Re)création de la BDD"
sudo mysql -e "DROP DATABASE IF EXISTS $DB; CREATE DATABASE $DB;"

echo "→ Import du dump"
# backticks pour compatibilité POSIX
SQL_FILE=\`ls "\$DEST"/*.sql 2>/dev/null | head -n1 || true\`
if [ -n "\$SQL_FILE" ]; then
  sudo mysql "$DB" < "\$SQL_FILE"
  echo "→ Import terminé depuis \$SQL_FILE"
else
  echo "⚠️ Aucun .sql trouvé dans \$DEST"
fi

echo "→ Installation des dépendances back-end (Composer)"
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

echo "→ Déploiement des assets statiques"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "→ Permissions & redémarrage de Nginx"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement complet terminé !"
SCRIPT

sudo chmod +x /tmp/full_deploy.sh
EOF

########################
# 4) EXÉCUTION DU SCRIPT REMOTE
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST sudo bash /tmp/full_deploy.sh
