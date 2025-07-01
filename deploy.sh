#!/usr/bin/env bash
set -euo pipefail

USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"
DB_NAME="dejavu"

echo "🚀 Déploiement vers $USER@$HOST:$DEST …"

# 1) Rsync du projet
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

# 2) Génération du script distant
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
cat > /tmp/deploy_remote.sh << 'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB_NAME="dejavu"

# Installer MySQL si besoin
if ! command -v mysql &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

# Démarrer MySQL
sudo systemctl enable --now mysql

# Trouver le dump SQL
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -z "\$SQL_FILE" ]; then
  echo "❌ Aucun .sql trouvé dans \$DEST" >&2
  exit 1
fi

# Création + import DB en socket
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \\\`$DB_NAME\\\`;
USE \\\`$DB_NAME\\\`;
SOURCE \$SQL_FILE;
SQL

# Back-end PHP
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# Front-end React
cd "\$DEST/frontend"
npm ci
npm run build

# Déploiement statique
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

# Permissions & restart
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart apache2 || sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
SCRIPT

# 2a) Nettoyer les CRLF s’il y en a
sudo sed -i 's/\r$//' /tmp/deploy_remote.sh

# 2b) Rendre exécutable
sudo chmod +x /tmp/deploy_remote.sh
EOF

# 3) Exécution du script sur la VM
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST sudo bash /tmp/deploy_remote.sh
