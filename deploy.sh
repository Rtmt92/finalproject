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
# 1) Synchronisation du projet
########################
rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

########################
# 2) Copie et exécution du script de déploiement complet
########################
ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << EOF
  set -euo pipefail
  # Rendre exécutable le script que vous venez de synchroniser
  sudo chmod +x "$DEST/deploy_full.sh"
  # Lancer le déploiement
  sudo "$DEST/deploy_full.sh"
EOF
