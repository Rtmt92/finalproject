// src/pages/Profil.jsx
import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ClientBanner from '../compenents/ClientBanner';
import ProductBanner from '../compenents/ProductBanner';
import '../styles/Profil.css';

export default function Profil() {
  const [client, setClient] = useState(null);
  const [panierProduits, setPanierProduits] = useState([]);
  const [prixTotal, setPrixTotal] = useState(0);
  const [idPanier, setIdPanier] = useState(null);

  const navigate = useNavigate();
  const token = localStorage.getItem("token");

  // Récupère les infos du client
  useEffect(() => {
    if (!token) return;
    fetch('http://localhost:8000/api/me', {
      headers: { Authorization: `Bearer ${token}` }
    })
      .then(res => {
        if (!res.ok) throw new Error("Unauthorized");
        return res.json();
      })
      .then(data => setClient(data))
      .catch(() => navigate('/login'));
  }, [token, navigate]);

  // Dès qu'on a le client, on refresh le panier
  useEffect(() => {
    if (!client) return;
    refreshPanier();
  }, [client]);

  // Va chercher le panier (avec champ `image` pour chaque produit)
  const refreshPanier = () => {
    fetch('http://localhost:8000/panier', {
      headers: { Authorization: `Bearer ${token}` },
    })
      .then(res => res.json())
      .then(data => {
        if (!data?.id_panier) return;
        setIdPanier(data.id_panier);
        setPrixTotal(data.prix_total);
        setPanierProduits(data.produits);
      })
      .catch(err => console.error('Erreur panier:', err));
  };

  // Supprimer un produit du panier
  const handleDeleteFromPanier = async (idProduit) => {
    if (!idPanier) return;
    const res = await fetch(
      `http://localhost:8000/panier_produit/${idPanier}/${idProduit}`,
      {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      }
    );
    if (res.ok) refreshPanier();
    else alert("Erreur lors de la suppression.");
  };

  // Supprimer le compte
  const handleDeleteAccount = async () => {
    if (!window.confirm("Supprimer votre compte ?")) return;
    const res = await fetch(
      `http://localhost:8000/client/${client.id_client}`,
      {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      }
    );
    if (res.ok) navigate('/login');
    else alert("Erreur lors de la suppression.");
  };

  if (!client) return <div>Chargement...</div>;

  // Éventuellement dédupliquer si plusieurs lignes pour même produit
  const produitsUniques = panierProduits.filter(
    (prod, idx, arr) => idx === arr.findIndex(p => p.id_produit === prod.id_produit)
  );

  return (
    <div className="profil-container">
      <ClientBanner {...client} photo={client.photo_profil} mode="edit" />

      <h3 className="section-title">Votre panier</h3>
      <div className="panier-section">
        {produitsUniques.length === 0 ? (
          <p>Votre panier est vide.</p>
        ) : (
          produitsUniques.map(prod => (
            <ProductBanner
              key={prod.id_produit}
              id={prod.id_produit}
              titre={prod.titre}
              description={prod.description}
              prix={prod.prix}
              etat={prod.etat}
              quantite={prod.quantite}
              image={prod.image}              // <-- on passe directement `prod.image`
            />
          ))
        )}

        <div className="panier-total">
          <strong>Total : {Number(prixTotal).toFixed(2)} €</strong>
        </div>
      </div>

      <div className="profil-actions">
        <button
          className="btn-valider"
          onClick={() => navigate('/pay')}
        >
          Valider mon panier
        </button>
        <button
          className="btn-danger"
          onClick={handleDeleteAccount}
        >
          SUPPRIMER MON COMPTE
        </button>
      </div>
    </div>
  );
}
