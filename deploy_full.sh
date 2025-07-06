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
sudo composer install --no-dev --optimize-autoloader

echo "→ Vérification et création du fichier .htaccess si nécessaire…"
HTACCESS_PATH="$DEST/backend/public/.htaccess"

if [ ! -f "$HTACCESS_PATH" ]; then
  cat <<EOF | sudo tee "$HTACCESS_PATH" > /dev/null
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^ index.php [QSA,L]
</IfModule>
EOF
  echo "✅ .htaccess créé à $HTACCESS_PATH"
else
  echo "ℹ️ .htaccess déjà présent, aucune action."
fi

echo "→ Ajustement des permissions backend (www-data)…"
sudo chown -R www-data:www-data "$DEST/backend"

echo "→ Préparation du front-end React…"
cd "$DEST/frontend"
sudo chown -R azureuser:azureuser "$DEST/frontend"
sudo npm ci
sudo npm run build

echo "→ Déploiement des fichiers statiques frontend…"
sudo mkdir -p /var/www/html
sudo rm -rf /var/www/html/*
sudo cp -r build/* /var/www/html/

echo "→ Ajustement des permissions /var/www/html…"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

echo "→ Redémarrage d'Apache (et non nginx)…"
sudo systemctl restart apache2

echo "✅ Déploiement complet terminé avec succès !"
