// src/pages/AdminClient.jsx
import React, { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import ClientBanner from "../compenents/ClientBanner";
import "../styles/AdminClient.css";
import API_BASE_URL from "../config";

const AdminClient = () => {
  const [clients, setClients] = useState([]);
  const { search: locationSearch } = useLocation();

  const query = new URLSearchParams(locationSearch).get("search") || "";

  useEffect(() => {
    const params = new URLSearchParams();
    if (query) params.set("search", query);

    fetch(`${API_BASE_URL}/client?${params.toString()}`, {
      headers: {
        // "Authorization": `Bearer ${localStorage.getItem("token")}`
      }
    })
      .then((res) => res.json())
      .then((data) => {
        setClients(Array.isArray(data) ? data : data.clients || []);
      })
      .catch((err) => console.error("Erreur fetch clients :", err));
  }, [query]);

  const handleDelete = async (id) => {
    if (!window.confirm("Supprimer ce client ?")) return;

    const res = await fetch(`${API_BASE_URL}/client/${id}`, {
      method: "DELETE",
      headers: {
        // "Authorization": `Bearer ${localStorage.getItem("token")}`
      }
    });

    if (res.ok) {
      setClients((prev) => prev.filter((c) => c.id_client !== id));
    } else {
      alert("Échec de la suppression");
    }
  };

  return (
    <div className="admin-client-page">
      <h2 className="page-title">Tous les clients</h2>
      {clients.map((client) => (
        <ClientBanner
          key={client.id_client}
          id={client.id_client}
          nom={client.nom}
          prenom={client.prenom}
          description={client.description}
          email={client.email}
          photo={client.photo_profil}
          onDelete={handleDelete}
        />
      ))}
    </div>
  );
};

export default AdminClient;
