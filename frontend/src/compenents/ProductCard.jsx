// frontend/src/compenents/ProductCard.jsx
import React from 'react';
import { Link } from 'react-router-dom';
import '../styles/ProductCard.css';

export default function ProductCard({
  id,
  titre,
  image,
  prix,
  etat,
  quantite
}) {
  return (
    <Link to={`/product/${id}`} className="product-card-link">
      <div className="product-card">
        <p className="product-title">{titre}</p>

        {image ? (
          <img
            src={image.startsWith('http') ? image : `http://localhost:8000/${image}`}
            alt={titre}
            className="product-img"
          />
        ) : (
          <div className="image-placeholder" />
        )}

        <div className="product-info">
          <p className="product-price">Prix : {prix} €</p>
          <p className="product-etat">État : {etat || 'N/A'}</p>
          <p className="product-quantite">Quantité : {quantite ?? 'N/A'}</p>
        </div>
      </div>
    </Link>
  );
}
