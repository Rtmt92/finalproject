#!/usr/bin/env bash
set -euo pipefail

########################
# CONFIGURATION
########################
USER="azureuser"
HOST="4.233.136.179"
DEST="/var/www/dejavu"
KEY="$HOME/.ssh/id_rsa"

echo "🚀 Début du déploiement vers $USER@$HOST:$DEST"

########################
# 1) Vérification de la clé SSH
########################
if [[ ! -f "$KEY" ]]; then
  echo "❌ Clé SSH manquante : $KEY"
  exit 1
fi

########################
# 2) Synchronisation du projet
########################
echo "🔄 Synchronisation des fichiers avec rsync…"

rsync -az --delete \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'frontend/build' \
  -e "ssh -i $KEY -o StrictHostKeyChecking=no" \
  ./ "$USER@$HOST:$DEST"

echo "✅ Fichiers synchronisés."

########################
# 3) Lancement du script distant
########################
echo "🚀 Lancement du script de déploiement complet sur la VM…"

ssh -i "$KEY" -o StrictHostKeyChecking=no $USER@$HOST bash << EOF
  set -euo pipefail
  echo "📁 Script de déploiement distant en cours…"
  if [[ ! -x "$DEST/deploy_full.sh" ]]; then
    echo "⚠️  Script deploy_full.sh non exécutable, tentative de correction…"
    sudo chmod +x "$DEST/deploy_full.sh"
  fi
  sudo "$DEST/deploy_full.sh"
EOF

echo "🎉 Déploiement terminé avec succès !"
