import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ClientBanner from '../compenents/ClientBanner';
import '../styles/Profil.css';

export default function Profil() {
  const [client, setClient] = useState(null);
  const [panierProduits, setPanierProduits] = useState([]);
  const [prixTotal, setPrixTotal] = useState(0);
  const [idPanier, setIdPanier] = useState(null);
  const navigate = useNavigate();
  const token = localStorage.getItem("token");

  const getImageUrl = (lien) => lien ? `http://localhost:8000/${lien}` : null;

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
      .catch(err => {
        console.error('Erreur profil:', err);
        navigate('/login');
      });
  }, []);

  useEffect(() => {
    if (client) refreshPanier();
  }, [client]);

  const refreshPanier = () => {
    fetch('http://localhost:8000/panier', {
      headers: { 'Authorization': `Bearer ${token}` },
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

  const handleDeleteFromPanier = async (idProduit) => {
    if (!idPanier) return;
    const res = await fetch(`http://localhost:8000/panier_produit/${idPanier}/${idProduit}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (res.ok) refreshPanier();
    else alert("Erreur lors de la suppression.");
  };

  const handleDeleteAccount = async () => {
    if (!window.confirm("Supprimer votre compte ?")) return;
    const res = await fetch(`http://localhost:8000/client/${client.id_client}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` },
    });
    if (res.ok) navigate('/login');
    else alert("Erreur lors de la suppression.");
  };

  if (!client) return <div>Chargement...</div>;

  const produitsUniques = panierProduits.filter(
    (prod, index, self) => index === self.findIndex(p => p.id_produit === prod.id_produit)
  );

  return (
    <div className="profil-container">
      <ClientBanner {...client} photo={client.photo_profil} mode="edit" />

      <h3 className="section-title">Votre panier</h3>
      <div className="panier-section">
        {produitsUniques.length === 0 ? (
          <p>Votre panier est vide.</p>
        ) : (
          produitsUniques.map((prod, index) => {
            const firstImage = getImageUrl(prod.images?.[0]?.lien);
            return (
              <div key={index} className="panier-item">
                <div className="banner">
                  <div className="banner-image">
                    {firstImage ? (
                      <img src={firstImage} alt="Produit" />
                    ) : (
                      <div className="no-image">Image</div>
                    )}
                  </div>
                  <div className="banner-details">
                    <h3>{prod.titre || "Produit"}</h3>
                    <p>{prod.description || "Pas de description"}</p>
                    <p><strong>État :</strong> {prod.etat || 'Non spécifié'}</p>
                    <p><strong>Quantité :</strong> {prod.quantite ?? 'NC'}</p>
                  </div>
                  <div className="banner-price">
                    <p className="prix">{parseFloat(prod.prix || prod.price || 0).toFixed(2)} €</p>
                  </div>
                </div>
              </div>
            );
          })
        )}
        <div className="panier-total">
          <strong>Total : {Number(prixTotal).toFixed(2)} €</strong>
        </div>
      </div>

      <div className="profil-actions">
        <button className="btn-valider" onClick={() => navigate('/pay')}>Valider mon panier</button>
        <button onClick={handleDeleteAccount} className="btn-danger">SUPPRIMER MON COMPTE</button>
      </div>
    </div>
  );
}
