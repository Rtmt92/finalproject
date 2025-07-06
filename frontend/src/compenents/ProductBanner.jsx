// src/components/ProductBanner.jsx
import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/ProductBanner.css";
import API_BASE_URL from "../config";

const ProductBanner = ({
  titre,
  description,
  prix,
  image,
  id,
  etat,
  quantite,
  clickable = true,
}) => {
  const navigate = useNavigate();

  const handleClick = () => {
    if (clickable) {
      navigate(`/admin/edit-product/${id}`);
    }
  };

  return (
    <div
      className="banner"
      onClick={clickable ? handleClick : undefined}
      style={{ cursor: clickable ? "pointer" : "default" }}
    >
      <div className="banner-image">
        {image ? (
          <img src={`${API_BASE_URL}/${image}`} alt={titre} />
        ) : (
          <div className="no-image">Image</div>
        )}
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
