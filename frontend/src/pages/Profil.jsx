import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ClientBanner from '../compenents/ClientBanner';
import ProductBanner from '../compenents/ProductBanner';

export default function Profil() {
  const [client, setClient] = useState(null);
  const [panier, setPanier] = useState([]);
  const [total, setTotal] = useState(0);
  const navigate = useNavigate();

  useEffect(() => {
    fetch('http://localhost:3000/api/me', {
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => setClient(data))
      .catch(err => console.error('Erreur profil:', err));
  }, []);

  useEffect(() => {
    fetch('http://localhost:3000/panier', {
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => {
        setPanier(data.produits || []);
        setTotal(data.prix_total || 0);
      })
      .catch(err => console.error('Erreur panier:', err));
  }, []);

  const handleDeleteAccount = async () => {
    if (!window.confirm('Supprimer votre compte ?')) return;
    const res = await fetch(`http://localhost:3000/client/${client.id_client}`, {
      method: 'DELETE',
      credentials: 'include'
    });
    if (res.ok) {
      navigate('/login');
    } else {
      alert('Erreur lors de la suppression.');
    }
  };

  const handleValidatePanier = () => {
    alert('Panier validé (fonction à implémenter)');
  };

  if (!client) return <div>Chargement du profil...</div>;

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
        {panier.map((prod, index) => (
          <ProductBanner key={index} produit={prod} />
        ))}
        <div className="panier-total">
          <span>Total: {total}€</span>
        </div>
      </div>

      <div className="profil-actions">
        <button onClick={handleValidatePanier} className="btn-valider">Valider mon panier</button>
        <button onClick={handleDeleteAccount} className="btn-danger">SUPPRIMER MON COMPTE</button>
      </div>
    </div>
  );
}
