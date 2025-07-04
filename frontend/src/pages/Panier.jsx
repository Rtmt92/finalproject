// src/pages/Panier.jsx
import React, { useEffect, useState } from 'react';
import { useNavigate }               from 'react-router-dom';
import ProductBanner                 from '../compenents/ProductBanner';
import '../styles/Profil.css';

export default function Panier() {
  const [items, setItems] = useState([]);
  const [total, setTotal] = useState(0);
  const navigate         = useNavigate();

  // Utility: read guestCart from localStorage as array of numbers
  const readGuestCart = () => {
    try {
      return JSON.parse(localStorage.getItem('guestCart'))?.map(id => Number(id)) || [];
    } catch {
      return [];
    }
  };

  // Save guestCart back to localStorage
  const writeGuestCart = arr => {
    localStorage.setItem('guestCart', JSON.stringify(arr));
  };

  // Load guest cart
  useEffect(() => {
    const guestIds = readGuestCart();
    if (guestIds.length === 0) {
      setItems([]);
      setTotal(0);
      return;
    }

    Promise.all(
      guestIds.map(id =>
        fetch(`http://localhost:8000/api/produit/${id}`)
          .then(r => r.json())
          .then(p => ({
            id_produit: p.id_produit,
            titre:      p.nom_produit,
            description:p.description,
            prix:       p.prix,
            etat:       p.etat,
            quantite:   1,
            image:      p.images?.[0]?.lien || null
          }))
      )
    ).then(prods => {
      setItems(prods);
      setTotal(prods.reduce((sum, p) => sum + parseFloat(p.prix), 0));
    });
  }, []);

  // Remove an item and persist change
  const removeFromCart = idProduit => {
    const guestIds = readGuestCart().filter(id => id !== idProduit);
    writeGuestCart(guestIds);

    // Update state
    const remaining = items.filter(p => p.id_produit !== idProduit);
    setItems(remaining);
    setTotal(remaining.reduce((sum, p) => sum + parseFloat(p.prix), 0));
  };

  // On validate: prompt to login
  const handleValidate = () => {
    alert('Vous devez être connecté pour valider votre commande.');
    navigate('/login');
  };

  return (
    <div className="profil-container">
      <h3 className="section-title">Votre panier</h3>
      <div className="panier-section">
        {items.length === 0 ? (
          <p>Votre panier est vide.</p>
        ) : (
          items.map(p => (
            <div key={p.id_produit} className="panier-item">
              <ProductBanner
                id={p.id_produit}
                titre={p.titre}
                description={p.description}
                prix={p.prix}
                etat={p.etat}
                quantite={p.quantite}
                image={p.image}
                clickable={false}
              />
              <button
                className="btn-supprimer"
                onClick={() => removeFromCart(p.id_produit)}
              >
                ×
              </button>
            </div>
          ))
        )}
        <div className="panier-total">
          <strong>Total : {total.toFixed(2)} €</strong>
        </div>
      </div>

      <div className="profil-actions">
        <button className="btn-valider" onClick={handleValidate}>
          Valider mon panier
        </button>
      </div>
    </div>
  );
}
