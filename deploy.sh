#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION LOCALE
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/Downloads/DejaVu_key.pem"    # ← Chemin vers votre PEM
DB_NAME="dejavu"
DB_USER="root"
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
# 3) GÉNÉRATION DU SCRIPT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
cat > /tmp/deploy_remote.sh << 'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"
DB_USER="root"
DB_PASS="admin"

# 1) Installer MySQL si besoin
if ! command -v mysql &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

# 2) Démarrer & activer MySQL
sudo systemctl enable --now mysql

# 3) (Re)création de la base & de l’utilisateur
sudo mysql <<EOF_SQL
DROP DATABASE IF EXISTS \`$DB_NAME\`;
CREATE DATABASE \`$DB_NAME\`;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost'
  IDENTIFIED WITH mysql_native_password BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF_SQL

# 4) Import du dump SQL
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -z "\$SQL_FILE" ]; then
  echo "❌ Aucun .sql trouvé dans \$DEST" >&2
  exit 1
fi
sudo mysql "\$DB_NAME" < "\$SQL_FILE"

# 5) Installer Composer si besoin
if ! command -v composer &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer
fi

# 6) Installer Node.js + npm si besoin
if ! command -v npm &>/dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs npm
fi

# 7) Back-end PHP
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# 8) Front-end React
cd "\$DEST/frontend"
npm ci
npm run build

# 9) Assets statiques
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

# 10) Permissions & restart nginx
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
SCRIPT

# Rendre exécutable
sudo chmod +x /tmp/deploy_remote.sh
EOF

########################
# 4) EXÉCUTION DU SCRIPT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST sudo bash /tmp/deploy_remote.sh
