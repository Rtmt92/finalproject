#!/usr/bin/env bash
set -euo pipefail

########################
# CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"   # Chemin vers votre clé privée

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST …"

########################
# 0) Fix des permissions AVANT synchronisation
########################
echo "🔧 Correction des permissions sur le serveur distant…"
ssh -i "$KEY" -o StrictHostKeyChecking=no "$USER@$HOST" "sudo chmod -R 777 $DEST"

########################
# 1) Synchronisation du projet
########################
echo "🔄 Synchronisation des fichiers avec rsync…"
rsync -azO --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
  --exclude 'backend/uploads' \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

########################
# 2) Copie et exécution du script de déploiement complet
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << EOF
  set -euo pipefail
  echo "🛠️ Exécution de deploy_full.sh sur la VM distante…"
  sudo chmod +x "$DEST/deploy_full.sh"
  sudo "$DEST/deploy_full.sh"
EOF
