import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ClientBanner from '../compenents/ClientBanner';
import ProductBanner from '../compenents/ProductBanner';
import API_BASE_URL from '../config'; // ✅ URL API centralisée
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
    fetch(`${API_BASE_URL}/api/me`, {
      headers: { Authorization: `Bearer ${token}` }
    })
      .then(res => {
        if (!res.ok) throw new Error("Unauthorized");
        return res.json();
      })
      .then(data => setClient(data))
      .catch(() => navigate('/login'));
  }, [token, navigate]);

  // Charge le panier une fois le client chargé
  useEffect(() => {
    if (client) refreshPanier();
  }, [client]);

  // Récupère le panier
  const refreshPanier = () => {
    fetch(`${API_BASE_URL}/panier`, {
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
      `${API_BASE_URL}/panier_produit/${idPanier}/${idProduit}`,
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
      `${API_BASE_URL}/client/${client.id_client}`,
      {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` }
      }
    );

    if (res.ok) navigate('/login');
    else alert("Erreur lors de la suppression.");
  };

  if (!client) return <div>Chargement...</div>;

  // Supprimer les doublons (si même produit plusieurs fois)
  const produitsUniques = panierProduits.filter(
    (prod, idx, arr) =>
      idx === arr.findIndex(p => p.id_produit === prod.id_produit)
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
            <div key={prod.id_produit} className="panier-item">
              <ProductBanner
                id={prod.id_produit}
                titre={prod.titre}
                description={prod.description}
                prix={prod.prix}
                etat={prod.etat}
                quantite={prod.quantite}
                image={prod.image}
                clickable={false}
              />
              <button
                className="btn-supprimer"
                onClick={() => handleDeleteFromPanier(prod.id_produit)}
              >
                ×
              </button>
            </div>
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
