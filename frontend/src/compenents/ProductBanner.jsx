import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/ProductBanner.css";
import API_BASE_URL from "../config";
import { Trash2 } from 'lucide-react';

const ProductBanner = ({
  titre,
  description,
  prix,
  image,
  id,
  etat,
  quantite,
  clickable = true,
  onDelete,  // Ajout de la prop onDelete
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
      style={{ cursor: clickable ? "pointer" : "default", position: 'relative' }} // position relative pour placer la croix
    >
      {onDelete && (
        <button
          className="btn-delete"
          onClick={(e) => {
            e.stopPropagation(); // empêcher le clic de remonter et déclencher la navigation
            onDelete(id);
          }}
          aria-label="Supprimer ce produit"
          title="Supprimer ce produit"
        >
          <Trash2 size={20} color="white" />
        </button>
      )}
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
        <p className="prix">{parseFloat(prix).toFixed(2)}€</p>
      </div>
    </div>
  );
};

export default ProductBanner;
