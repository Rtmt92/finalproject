import React from 'react';
import '../styles/ProductCard.css';

const ProductCard = () => {
    return (
      <div className="product-card">
        <p className="product-title">PC complet neuf</p>
        <div className="image-placeholder"></div>
        <div className="product-info">
          <p className="product-offer">offre de quentin</p>
          <p className="product-condition">Etat neuf</p>
          <p className="product-price">35$</p>
        </div>
      </div>
    );
  };

export default ProductCard;