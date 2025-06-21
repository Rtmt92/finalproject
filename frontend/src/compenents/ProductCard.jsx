import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import '../styles/ProductCard.css';

export default function ProductCard() {
  const [prod, setProd]       = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState('');

  useEffect(() => {
    fetch('/api/produit/random')
      .then(res => {
        if (!res.ok) throw new Error(`Status ${res.status}`);
        return res.json();
      })
      .then(data => {
        setProd(data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Erreur fetch produit random:', err);
        setError('Impossible de charger le produit');
        setLoading(false);
      });
  }, []);

  if (loading) return <div className="product-card">Chargement…</div>;
  if (error)   return <div className="product-card error">{error}</div>;
  if (!prod)   return null;

  return (
    <Link
      to={`/product/${prod.id_produit}`}
      className="product-card-link"
    >
      <div className="product-card">
        <p className="product-title">{prod.nom_produit}</p>

        {prod.image_url ? (
          <img
            src={prod.image_url}
            alt={prod.nom_produit}
            className="product-img"
          />
        ) : (
          <div className="image-placeholder" />
        )}

        <div className="product-info">
          <p className="product-price">Prix : {prod.prix}€</p>
          <p className="product-etat">État : {prod.etat || 'N/A'}</p>
          <p className="product-quantite">Quantité : {prod.quantite ?? 'N/A'}</p>
        </div>
      </div>
    </Link>
  );
}
