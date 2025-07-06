#!/usr/bin/env bash
set -euo pipefail

DEST="/var/www/dejavu"
DB="dejavu"

echo "→ Attribution des droits à azureuser pour éviter les erreurs rsync…"
sudo chown -R azureuser:azureuser "$DEST"

echo "→ (Re)création de la base $DB…"
sudo mysql -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\`;"

SQL_FILE=$(ls "$DEST"/*.sql 2>/dev/null | head -n1 || true)
if [[ -n "$SQL_FILE" ]]; then
  echo "→ Import du dump SQL depuis $SQL_FILE"
  sudo mysql "$DB" < "$SQL_FILE"
else
  echo "⚠️  Aucun .sql trouvé dans $DEST"
fi

echo "→ Vérification de la présence du .env backend…"
if [[ ! -f "$DEST/backend/.env" ]]; then
  echo "⚠️  Le fichier $DEST/backend/.env est manquant !"
else
  echo "✅ Fichier .env trouvé."
fi

echo "→ Installation des dépendances PHP (Composer)…"
cd "$DEST/backend"
composer install --no-dev --optimize-autoloader

echo "→ Préparation du front-end React…"
cd "$DEST/frontend"
sudo chown -R azureuser:azureuser "$DEST/frontend"
npm ci
npm run build

echo "→ Déploiement des fichiers statiques frontend…"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "→ Ajustement des permissions /var/www/html pour Apache…"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

echo "→ Redémarrage d'Apache…"
sudo systemctl restart apache2

echo "✅ Déploiement terminé avec succès !"
