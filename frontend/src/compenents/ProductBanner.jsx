// src/compenents/ProductBanner.jsx
import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/ProductBanner.css";

const ProductBanner = ({ titre, description, prix, image, id, auteur }) => {
  const navigate = useNavigate();

  const handleClick = () => {
    navigate(`/edit-product/${id}`);
  };

  return (
    <div className="banner" onClick={handleClick}>
      <div className="banner-image">
        {image ? <img src={image} alt={titre} /> : <div className="no-image">Image</div>}
      </div>
      <div className="banner-details">
        <h3>{titre}</h3>
        <p>{description}</p>
      </div>
      <div className="banner-price">
        <p className="prix">{prix}$</p>
      </div>
    </div>
  );
};

export default ProductBanner;
