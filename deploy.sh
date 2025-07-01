#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

########################
# 2) RSYNC DU PROJET
# (le dossier /var/www/dejavu existe déjà)
########################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ $USER@$HOST:"$DEST"

########################
# 3) DÉPLOIEMENT SUR LA VM
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
  set -euo pipefail

  echo "• Installer MySQL si nécessaire"
  if ! command -v mysql &> /dev/null; then
    sudo apt-get update
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
  fi

  echo "• Démarrer MySQL"
  sudo systemctl enable --now mysql

  # Choix automatique du dump SQL
  SQL_FILE=\$(ls "$DEST"/*.sql 2>/dev/null | head -n1)
  if [ -z "\$SQL_FILE" ]; then
    echo "❌ Aucun .sql trouvé dans $DEST" >&2
    exit 1
  fi
  echo "• Import de la base depuis \$SQL_FILE"

  # Créer la base (socket auth)
  sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS dejavu;
SQL

  # Importer les données
  sudo mysql dejavu < "\$SQL_FILE"

  echo "• Installer les dépendances PHP"
  cd "$DEST/backend"
  composer install --no-dev --optimize-autoloader

  echo "• Builder le frontend React"
  cd "$DEST/frontend"
  npm ci
  npm run build

  echo "• Déployer les assets statiques"
  sudo rm -rf /var/www/html/*
  sudo cp -r build/* /var/www/html/

  echo "• Ajuster les permissions"
  sudo chown -R www-data:www-data /var/www/html
  sudo chmod -R 755 /var/www/html

  echo "• Redémarrer le serveur web"
  sudo systemctl restart apache2 || sudo systemctl restart nginx

  echo "✅ Déploiement terminé !"
EOF
