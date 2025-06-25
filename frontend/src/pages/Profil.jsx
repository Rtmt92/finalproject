import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ClientBanner from '../compenents/ClientBanner';

export default function Profil() {
  const [client, setClient] = useState(null);
  const [panierProduits, setPanierProduits] = useState([]);
  const [prixTotal, setPrixTotal] = useState(0);
  const [idPanier, setIdPanier] = useState(null);
  const [passwordForm, setPasswordForm] = useState({
    ancien: '',
    nouveau: '',
    confirmation: ''
  });
  const [messagePwd, setMessagePwd] = useState('');

  const navigate = useNavigate();
  const token = localStorage.getItem("token");

  useEffect(() => {
    fetch('http://localhost:3000/api/me', {
      credentials: 'include',
    })
      .then(res => res.json())
      .then(data => setClient(data))
      .catch(err => console.error('Erreur profil:', err));
  }, []);

  const refreshPanier = () => {
    fetch('http://localhost:3000/panier', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    })
      .then(res => res.json())
      .then(data => {
        if (!data || !data.id_panier) return;
        setIdPanier(data.id_panier);
        setPrixTotal(data.prix_total);
        setPanierProduits(data.produits);
      })
      .catch(err => console.error('Erreur panier:', err));
  };

  useEffect(() => {
    if (client) refreshPanier();
  }, [client]);

  const handleDeleteFromPanier = async (idProduit) => {
    if (!idPanier) return;
    const res = await fetch(`http://localhost:3000/panier_produit/${idPanier}/${idProduit}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });

    if (res.ok) refreshPanier();
    else alert("Erreur lors de la suppression.");
  };

  const handleDeleteAccount = async () => {
    if (!window.confirm("Supprimer votre compte ?")) return;
    const res = await fetch(`http://localhost:3000/client/${client.id_client}`, {
      method: 'DELETE',
      credentials: 'include',
    });
    if (res.ok) navigate('/login');
    else alert("Erreur lors de la suppression.");
  };

  const handleChangePassword = async (e) => {
    e.preventDefault();
    setMessagePwd('');
    if (passwordForm.nouveau !== passwordForm.confirmation) {
      return setMessagePwd("Les mots de passe ne correspondent pas.");
    }

    const res = await fetch(`http://localhost:3000/client/${client.id_client}/password`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(passwordForm)
    });

    const body = await res.json();
    if (res.ok) {
      setMessagePwd("Mot de passe mis à jour.");
      setPasswordForm({ ancien: '', nouveau: '', confirmation: '' });
    } else {
      setMessagePwd(body.error || 'Erreur lors du changement de mot de passe');
    }
  };

  if (!client) return <div>Chargement...</div>;

  return (
    <div className="profil-container">
      <ClientBanner {...client} mode="edit" />

      <h3 className="section-title">Votre panier</h3>
      <div className="panier-section">
        {panierProduits.length === 0 ? (
          <p>Votre panier est vide.</p>
        ) : (
          panierProduits.map((prod, index) => (
            <div key={index} className="panier-item">
              <div className="banner">
                <div className="banner-image">
                  {prod.image ? <img src={prod.image} alt={prod.titre || "Produit"} /> : <div className="no-image">Image</div>}
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
              <button className="btn-danger" onClick={() => handleDeleteFromPanier(prod.id_produit)}>Supprimer</button>
            </div>
          ))
        )}
        <div className="panier-total">
          <strong>Total : {prixTotal} €</strong>
        </div>
      </div>

      <div className="change-password">
        <h3>Changer mon mot de passe</h3>
        <form onSubmit={handleChangePassword}>
          <input type="password" placeholder="Ancien mot de passe" value={passwordForm.ancien} onChange={e => setPasswordForm({ ...passwordForm, ancien: e.target.value })} required />
          <input type="password" placeholder="Nouveau mot de passe" value={passwordForm.nouveau} onChange={e => setPasswordForm({ ...passwordForm, nouveau: e.target.value })} required />
          <input type="password" placeholder="Confirmer le mot de passe" value={passwordForm.confirmation} onChange={e => setPasswordForm({ ...passwordForm, confirmation: e.target.value })} required />
          <button type="submit">Changer le mot de passe</button>
          {messagePwd && <p>{messagePwd}</p>}
        </form>
      </div>

      <div className="profil-actions">
        <button className="btn-valider" onClick={() => navigate('/pay')}>Valider mon panier</button>
        <button onClick={handleDeleteAccount} className="btn-danger">SUPPRIMER MON COMPTE</button>
      </div>
    </div>
  );
}
