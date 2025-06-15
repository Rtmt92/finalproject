import React from "react";
import { useNavigate } from "react-router-dom";
import "../styles/ClientBanner.css";

const ClientBanner = ({ id, nom, prenom, description, photo, onDelete }) => {
  const navigate = useNavigate();

  return (
    <div className="client-banner">
      <div className="client-info">
        <img src={photo} alt={`client-${nom}`} className="client-avatar" />

        <div className="client-text">
          <div className="client-header">
            <h3>{nom} {prenom}</h3>
            <button
              className="edit-icon"
              onClick={() => navigate(`/admin/client/edit/${id}`)}
              title="Modifier le client"
            >
              ✏️
            </button>
          </div>
          <p><strong>Biographie:</strong> {description}</p>
        </div>
      </div>

      <button className="client-delete-btn" onClick={() => onDelete(id)}>
        Supprimer
      </button>
    </div>
  );
};

export default ClientBanner;
