#!/usr/bin/env bash
set -euo pipefail

########################
# 1) CONFIGURATION
########################
USER="azureuser"                            # Nom d'utilisateur SSH
HOST="4.233.136.179"                        # IP publique de la VM
DEST="/var/www/dejavu"                      # Racine de votre projet sur la VM
KEY="$HOME/.ssh/id_rsa"                     # Chemin vers votre clé privée

# Credentials MySQL (passés depuis GitHub Actions via env)
MYSQL_ROOT_PWD="${MYSQL_ROOT_PWD:-Thibault0709}"
DB_NAME="${DB_NAME:-dejavu}"

echo "🚀 Début du déploiement sur $USER@$HOST:$DEST …"

########################
# 2) PRÉPARER LE DISTANT
########################
echo "📂 Création du répertoire distant et mise en place des permissions…"
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST << EOF
  sudo mkdir -p "$DEST"
  sudo chown -R "$USER":"$USER" "$DEST"
EOF

########################
# 3) RSYNC DU PROJET
########################
echo "🔄 Synchronisation des fichiers avec rsync…"
rsync -avz \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude "$(basename "$KEY")" \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ $USER@$HOST:"$DEST"

########################
# 4) COMMANDES DISTANTES
########################
echo "🔧 Exécution des commandes sur la VM distante…"
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << 'EOF'
  set -euo pipefail

  echo "• Démarrage de MySQL"
  sudo systemctl start mysql

  echo "• Création et import de la base de données"
  mysql -u root -p"$MYSQL_ROOT_PWD" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;"
  mysql -u root -p"$MYSQL_ROOT_PWD" "$DB_NAME" < "$DEST/dejavu.sql"

  echo "• Installation du backend PHP"
  cd "$DEST/backend"
  composer install --no-dev --optimize-autoloader

  echo "• Construction du frontend React"
  cd "$DEST/frontend"
  npm install
  npm run build

  echo "• Déploiement des fichiers statiques"
  sudo rm -rf /var/www/html/*
  sudo cp -r build/* /var/www/html/

  echo "• Ajustement des permissions"
  sudo chown -R www-data:www-data /var/www/html
  sudo chmod -R 755 /var/www/html

  echo "• Redémarrage du serveur web"
  sudo systemctl restart apache2 || sudo systemctl restart nginx

  echo "✅ Déploiement terminé sur la VM !"
EOF
