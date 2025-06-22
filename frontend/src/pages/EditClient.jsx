import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import '../styles/EditClient.css'; 


const EditClient = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  const [form, setForm] = useState({
    nom: "",
    prenom: "",
    email: "",
    mot_de_passe: "",
    role: "client", // valeur par défaut
  });

  useEffect(() => {
    fetch(`http://localhost:3000/client/${id}`)
      .then((res) => res.json())
      .then((data) => {
        setForm({
          nom: data.nom || "",
          prenom: data.prenom || "",
          email: data.email || "",
          mot_de_passe: "",
          role: data.role || "client",
        });
      });
  }, [id]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async () => {
    const res = await fetch(`http://localhost:3000/client/${id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(form),
    });

    if (res.ok) {
      alert("Client modifié !");
      navigate("/admin/client");
    } else {
      alert("Erreur de modification");
    }
  };

  const handleDelete = async () => {
    const confirmation = window.confirm("Supprimer définitivement ce client ?");
    if (!confirmation) return;

    const res = await fetch(`http://localhost:3000/client/${id}`, {
        method: "DELETE",
    });

    if (res.ok) {
        alert("Client supprimé !");
        navigate("/admin/client");
    } else {
        alert("Échec de la suppression");
    }
    };


  return (
    <div className="edit-product-page">
      <h2 className="client-edit-title">Modifier : {form.nom}</h2>

      <input
        type="text"
        name="nom"
        placeholder="Nom"
        value={form.nom}
        onChange={handleChange}
      />
      <input
        type="text"
        name="prenom"
        placeholder="Prenom"
        value={form.prenom}
        onChange={handleChange}
      />
      <input
        type="email"
        name="email"
        placeholder="Email"
        value={form.email}
        onChange={handleChange}
      />
      <input
        type="password"
        name="mot_de_passe"
        placeholder="Mot de passe"
        value={form.mot_de_passe}
        onChange={handleChange}
      />

      <select name="role" value={form.role} onChange={handleChange}>
        <option value="client">Client</option>
        <option value="admin">Admin</option>
      </select>

      <button type="submit" onClick={handleSubmit}>
        Valider
      </button>

        <button className="delete-button" onClick={handleDelete} style={{ backgroundColor: "darkred", color: "white", marginTop: "1rem" }}>
            Supprimer le client
        </button>

    </div>
  );
};

export default EditClient;
