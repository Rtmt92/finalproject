#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"                           # Nom d’utilisateur SSH
HOST="4.233.136.179"                       # IP publique de la VM
DEST="/var/www/dejavu"                     # Chemin du projet sur la VM
KEY="$HOME/.ssh/id_rsa"                    # Votre clé privée SSH

# Paramètres MySQL (depuis .env global – ou passed via GitHub Actions)
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_NAME="${DB_DATABASE:-dejavu}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-admin}"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

########################
# 2) CRÉER LE DOSSIER DISTANT
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST << EOF
  sudo mkdir -p "$DEST"
  sudo chown -R "$USER":"$USER" "$DEST"
EOF

########################
# 3) SYNCHRONISATION DU CODE
########################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ $USER@$HOST:"$DEST"

########################
# 4) DÉPLOIEMENT SUR LA VM
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << EOF
  set -euo pipefail

  echo "• Installation de MySQL si besoin"
  if ! command -v mysql &> /dev/null; then
    sudo apt-get update
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server
  fi

  echo "• Activation et démarrage de MySQL"
  sudo systemctl enable mysql
  sudo systemctl start mysql

  echo "• Configuration de root@localhost avec mot de passe"
  sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS'; FLUSH PRIVILEGES;"

  echo "• Création de la base '$DB_NAME'"
  sudo mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -e "CREATE DATABASE IF NOT EXISTS \\\`$DB_NAME\\\`;"

  SQL_FILE=\$(ls "$DEST"/*.sql 2>/dev/null | head -n1)
  if [ -z "\$SQL_FILE" ]; then
    echo "❌ Aucune dump SQL trouvée dans $DEST" >&2
    exit 1
  fi
  echo "• Import de la base depuis \$SQL_FILE"
  sudo mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" "$DB_NAME" < "\$SQL_FILE"

  echo "• Installation des dépendances PHP"
  cd "$DEST/backend"
  composer install --no-dev --optimize-autoloader

  echo "• Build du frontend React"
  cd "$DEST/frontend"
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
