#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION LOCALE
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"
DB_NAME="dejavu"
DB_USER="dejavu"
DB_PASS="admin"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

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
# 3) CRÉATION DU SCRIPT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF_REMOTE'
cat > /tmp/deploy_remote.sh << 'EOF_SCRIPT'
#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"
DB_USER="dejavu"
DB_PASS="admin"

# 1) Installer MySQL si besoin
if ! command -v mysql &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

# 2) Démarrer et activer MySQL
sudo systemctl enable --now mysql

# 3) (Re)création de la base et de l’utilisateur
sudo mysql <<SQL
DROP DATABASE IF EXISTS \`dejavu\`;
CREATE DATABASE \`dejavu\`;
CREATE USER IF NOT EXISTS 'dejavu'@'localhost' IDENTIFIED WITH mysql_native_password BY 'admin';
GRANT ALL PRIVILEGES ON \`dejavu\`.* TO 'dejavu'@'localhost';
FLUSH PRIVILEGES;
SQL

# 4) Import du dump SQL
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -z "\$SQL_FILE" ]; then
  echo "❌ Aucun .sql trouvé dans \$DEST" >&2
  exit 1
fi
sudo mysql "\$DB_NAME" < "\$SQL_FILE"

# 5) Installer Composer si besoin
if ! command -v composer &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# 6) Installer Node.js + npm si besoin
if ! command -v npm &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

# 7) Installer les dépendances back-end
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# 8) Builder le front-end React
cd "\$DEST/frontend"
npm ci
npm run build

# 9) Déployer les assets statiques
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

# 10) Ajuster les droits et redémarrer nginx
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
EOF_SCRIPT

# rendre exécutable et exécuter
sudo chmod +x /tmp/deploy_remote.sh
sudo bash /tmp/deploy_remote.sh
EOF_REMOTE
