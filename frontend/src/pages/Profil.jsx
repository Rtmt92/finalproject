import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ClientBanner from '../compenents/ClientBanner';

export default function Profil() {
  const [client, setClient] = useState(null);
  const [panierProduits, setPanierProduits] = useState([]);
  const [prixTotal, setPrixTotal] = useState(0);
  const [idPanier, setIdPanier] = useState(null);

  const navigate = useNavigate();

  useEffect(() => {
    fetch('http://localhost:3000/api/me', { credentials: 'include' })
      .then(res => res.json())
      .then(data => setClient(data))
      .catch(err => console.error('Erreur profil:', err));
  }, []);

  useEffect(() => {
    if (!client) return;

    fetch('http://localhost:3000/panier', { credentials: 'include' })
      .then(res => res.json())
      .then(data => {
        if (!data || !data.id_panier) return;
        setIdPanier(data.id_panier);
        setPrixTotal(data.prix_total);
        setPanierProduits(data.produits);
      })
      .catch(err => console.error('Erreur panier:', err));
  }, [client]);

  const handleDeleteFromPanier = async (idProduit) => {
    if (!idPanier) return;
    const res = await fetch(`http://localhost:3000/panier_produit/${idPanier}/${idProduit}`, {
      method: 'DELETE',
      credentials: 'include',
    });

    if (res.ok) {
      setPanierProduits(prev => prev.filter(p => p.id_produit !== idProduit));
      fetch('http://localhost:3000/panier', { credentials: 'include' })
        .then(res => res.json())
        .then(data => setPrixTotal(data.prix_total));
    } else {
      alert("Erreur lors de la suppression.");
    }
  };

  const handleDeleteAccount = async () => {
    if (!window.confirm("Supprimer votre compte ?")) return;
    const res = await fetch(`http://localhost:3000/client/${client.id_client}`, {
      method: 'DELETE',
      credentials: 'include',
    });
    if (res.ok) {
      navigate('/login');
    } else {
      alert("Erreur lors de la suppression.");
    }
  };

  if (!client) return <div>Chargement...</div>;

  return (
    <div className="profil-container">
      <ClientBanner
        nom={client.nom}
        prenom={client.prenom}
        description={client.description}
        photo={client.photo_profil}
        id={client.id_client}
      />

      <h3 className="section-title">Votre panier</h3>
      <div className="panier-section">
        {panierProduits.length === 0 ? (
          <p>Votre panier est vide.</p>
        ) : (
          panierProduits.map((prod, index) => (
            <div key={index} className="panier-item">
              <div className="banner">
                <div className="banner-image">
                  {prod.image ? (
                    <img src={prod.image} alt={prod.titre || "Produit"} />
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
                  <p className="prix">{prod.prix || prod.price || 0} €</p>
                </div>
              </div>
              <button
                className="btn-danger"
                onClick={() => handleDeleteFromPanier(prod.id_produit)}
              >
                Supprimer
              </button>
            </div>
          ))
        )}
        <div className="panier-total">
          <strong>Total : {prixTotal} €</strong>
        </div>
      </div>

      <div className="profil-actions">
        <button className="btn-valider">Valider mon panier</button>
        <button onClick={handleDeleteAccount} className="btn-danger">SUPPRIMER MON COMPTE</button>
      </div>
    </div>
  );
}
