#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION LOCALE
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"
DB_NAME="dejavu"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

########################
# 2) RSYNC DU PROJET
########################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -T -q -o LogLevel=ERROR -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

########################
# 3) DÉPLOIEMENT SUR LA VM
########################
ssh -T -q -o LogLevel=ERROR -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash -s << 'EOF'
set -euo pipefail

# Redéfinition locale côté VM
DEST="/var/www/dejavu"
DB_NAME="dejavu"

echo "• Installation de MySQL si nécessaire"
if ! command -v mysql &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

echo "• Démarrage de MySQL"
sudo systemctl enable --now mysql

# Trouver le dump SQL
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -z "\$SQL_FILE" ]; then
  echo "❌ Aucun .sql trouvé dans \$DEST" >&2
  exit 1
fi
echo "• Import de la base depuis \$SQL_FILE"

# Création + import en une seule passe (socket auth)
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;
USE \`$DB_NAME\`;
SOURCE \$SQL_FILE;
SQL

echo "• Installation des dépendances PHP"
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

echo "• Build du frontend React"
cd "\$DEST/frontend"
npm ci
npm run build

echo "• Déploiement des assets statiques"
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "• Ajustement des permissions"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

echo "• Redémarrage du serveur web"
sudo systemctl restart apache2 || sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF
