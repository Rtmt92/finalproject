// src/components/ProductBanner.jsx
import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/ProductBanner.css";

const ProductBanner = ({ titre, description, prix, image, id, auteur, etat, quantite }) => {
  const navigate = useNavigate();

  const handleClick = () => {
    navigate(`/admin/edit-product/${id}`);
  };

  return (
    <div className="banner" onClick={handleClick}>
      <div className="banner-image">
        {image ? <img src={image} alt={titre} /> : <div className="no-image">Image</div>}
      </div>
      <div className="banner-details">
        <h3>{titre}</h3>
        <p>{description}</p>
        <p><strong>État :</strong> {etat || 'N/A'}</p>
        <p><strong>Quantité :</strong> {quantite ?? 'N/A'}</p>
      </div>
      <div className="banner-price">
        <p className="prix">{parseFloat(prix).toFixed(2)}$</p>
      </div>
    </div>
  );
};

export default ProductBanner;
