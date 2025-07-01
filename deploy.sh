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

echo "🚀 Déploiement vers $USER@$HOST:$DEST …"

########################
# 2) RSYNC DU PROJET
########################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
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

# 1) Installer MySQL si besoin
if ! command -v mysql &> /dev/null; then
  sudo apt-get update
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
fi

# 2) Démarrer MySQL
sudo systemctl enable --now mysql

# 3) Trouver le dump SQL
SQL_FILE=\$(ls "\$DEST"/*.sql 2>/dev/null | head -n1)
if [ -z "\$SQL_FILE" ]; then
  echo "❌ Aucun .sql trouvé dans \$DEST" >&2
  exit 1
fi

# 4) Création + import DB via socket (Option A)
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \\\`\$DB_NAME\\\`;
USE \\\`\$DB_NAME\\\`;
SOURCE \$SQL_FILE;
SQL

# 5) Installer le backend PHP
cd "\$DEST/backend"
composer install --no-dev --optimize-autoloader

# 6) Builder le frontend React
cd "\$DEST/frontend"
npm ci
npm run build

# 7) Déployer les assets statiques
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

# 8) Permissions & restart
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart apache2 || sudo systemctl restart nginx

echo "✅ Déploiement terminé !"
SCRIPT

# Rendre exécutable
sudo chmod +x /tmp/deploy_remote.sh
EOF

########################
# 4) EXÉCUTION DU SCRIPT DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST sudo bash /tmp/deploy_remote.sh
