import React, { useEffect, useState } from "react";
import ClientBanner from "../compenents/ClientBanner"; // Vérifie le bon chemin
import "../styles/AdminClient.css";

const AdminClient = () => {
  const [clients, setClients] = useState([]);

  useEffect(() => {
    fetch("http://localhost:3000/client")
      .then((res) => res.json())
      .then((data) => {
        setClients(Array.isArray(data) ? data : data.clients || []);
      })
      .catch((err) => console.error("Erreur fetch clients :", err));
  }, []);

  const handleDelete = async (id) => {
    if (!window.confirm("Supprimer ce client ?")) return;

    const res = await fetch(`http://localhost:3000/client/${id}`, {
      method: "DELETE",
    });

    if (res.ok) {
      setClients((prev) => prev.filter((c) => c.id_client !== id));
    } else {
      alert("Échec de la suppression");
    }
  };

  return (
    <div className="admin-client-page">
      <h2 className="page-title">Toutes les clients</h2>
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
