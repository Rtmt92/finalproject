#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"   # ← Chemin vers ta clé PEM
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
# 3) GÉNÉRATION + EXECUTION DU SCRIPT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash -s << 'REMOTE_EOF'
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"

echo "→ Vérification de la base '$DB_NAME'…"
# Vérifier si la base existe
if sudo mysql -sNe "SHOW DATABASES LIKE '$DB_NAME';" | grep -q "^$DB_NAME\$"; then
  echo "• Base existante, import des données seulement"
else
  echo "• Base absente, création + import complet"
  sudo mysql -e "CREATE DATABASE \`$DB_NAME\`;"
fi

# Import du dump SQL si présent
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [ -n "\$SQL_FILE" ]; then
  echo "→ Import depuis \$SQL_FILE"
  sudo mysql "$DB_NAME" < "\$SQL_FILE"
else
  echo "❌ Aucun fichier .sql trouvé dans \$DEST"
fi

# Installer Composer si manquant
if ! command -v composer >/dev/null 2>&1; then
  echo "→ Installation de Composer"
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# Installer Node.js & npm si manquant
if ! command -v npm >/dev/null 2>&1; then
  echo "→ Installation de Node.js et npm"
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

# Back-end : dépendances PHP
echo "→ Installation back-end"
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# Front-end : build React
echo "→ Build front-end"
cd "\$DEST/frontend"
npm ci
npm run build

# Déploiement statique
echo "→ Déploiement sous /var/www/html"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

# Permissions et reload Nginx
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
REMOTE_EOF
