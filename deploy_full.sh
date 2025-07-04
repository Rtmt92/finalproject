#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "→ (Re)création de la base $DB…"
sudo mysql -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\`;"

SQL_FILE=$(ls "$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [[ -n "$SQL_FILE" ]]; then
  echo "→ Import du dump SQL depuis $SQL_FILE"
  sudo mysql "$DB" < "$SQL_FILE"
else
  echo "⚠️ Aucun .sql trouvé dans $DEST"
fi

echo "→ Installation des dépendances PHP (Composer)…"
cd "$DEST/backend"
sudo composer install --no-dev --optimize-autoloader

echo "→ Build du front-end React…"
cd "$DEST/frontend"
sudo npm ci
sudo npm run build

echo "→ Déploiement des fichiers statiques…"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "→ Ajustement des permissions et redémarrage de Nginx…"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart nginx

echo "✅ Déploiement complet terminé !"
