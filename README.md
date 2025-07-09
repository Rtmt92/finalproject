# FinalProject – Marketplace de composants PC d’occasion

**FinalProject** est une plateforme web de mise en relation entre acheteurs et vendeurs de matériel informatique d’occasion. Elle permet de parcourir des annonces, de filtrer les composants par catégorie, d’ajouter au panier, de payer en ligne (Stripe), et de gérer les ventes et utilisateurs via un tableau de bord admin.

---

## Fonctionnalités principales

- Recherche de composants avec filtres par catégorie, nom...
- Pages produit avec détails, photos, et prix
- Panier interactif et gestion de commande
- Authentification sécurisée via JWT (utilisateurs et admins)
- Paiement intégré via Stripe
- Tableau de bord Admin : gestion utilisateurs, produits, catégories
- API REST en PHP avec support JWT

---

## Technologies utilisées

### Frontend
- React.js
- React Router DOM
- Fetch API pour les appels au backend

### Backend
- PHP (Vanilla + architecture simple)
- Authentification JWT
- Intégration Stripe
- Base de données SQL 

### Autres
- Workflows CI/CD (GitHub Actions)
- Environnement configurable via `.env`

---

## Installation locale

### 1. Cloner le projet

```bash
git clone https://github.com/Rtmt92/finalproject.git
cd finalproject
```

### 2. Installation du frontend (React)

```bash
npm install
npm start
```

Lancer le frontend sur `http://localhost:3000`

### 3. Lancer le backend PHP

- Utilise XAMPP, Laragon ou un serveur Apache/PHP local
- Place le dossier `backend/` dans ton répertoire `htdocs`
- Configure les accès base de données dans `.env` ou `config` selon besoin

Exécution simple avec :

```bash
php -S localhost:8000 -t backend
```

### 4. Configuration base de données

- Importer le fichier `dejavu.sql` dans ton SGBD (ex : phpMyAdmin, MySQL Workbench)

---

## Structure du projet

```
finalproject/
├── backend/
│   ├── controllers/
│   ├── config/
│   ├── payment-intent.php
│   └── ...
├── frontend/ (ou racine React)
├── dejavu.sql
├── .env.example
└── .github/workflows/
```

---

## Authentification

- Le backend utilise des tokens JWT
- L’API valide l’utilisateur à chaque requête avec le header `Authorization: Bearer <token>`

---

## Scripts de déploiement

- `deploy.sh` – Déploiement automatique du backend
- `deploy_full.sh` – Déploiement full (frontend + backend)

---

## Contact

Pour toute question, suggestion ou bug : `rayantoumert.rt@gmail.com` ou via [Issues GitHub](https://github.com/Rtmt92/finalproject/issues)
