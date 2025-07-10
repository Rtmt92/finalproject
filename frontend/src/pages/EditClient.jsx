import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import '../styles/EditClient.css'; 
import API_BASE_URL from '../config';

const EditClient = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  const [form, setForm] = useState({
    nom: "",
    prenom: "",
    email: "",
    mot_de_passe: "",
    role: "client",
  });

  useEffect(() => {
    const token = localStorage.getItem("token");
    fetch(`${API_BASE_URL}/client/${id}`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    .then((res) => {
      if (!res.ok) {
        throw new Error("Non autorisé");
      }
      return res.json();
    })
    .then((data) => {
      setForm({
        nom: data.nom || "",
        prenom: data.prenom || "",
        email: data.email || "",
        mot_de_passe: "",
        role: data.role || "client",
      });
    })
    .catch(() => {
      alert("Vous n'êtes pas autorisé à accéder à cette page.");
      navigate("/login");
    });
  }, [id, navigate]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async () => {
    const token = localStorage.getItem("token");
    const res = await fetch(`${API_BASE_URL}/client/${id}`, {
      method: "PATCH",
      headers: { 
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`
      },
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

    const token = localStorage.getItem("token");
    const res = await fetch(`${API_BASE_URL}/client/${id}`, {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${token}`
      }
    });

    if (res.ok) {
      alert("Client supprimé !");
      navigate("/admin/client");
    } else {
      alert("Échec de la suppression");
    }
  };

  return (
    <div className="edit-wrapper">
      <div className="edit-product-page">
        <h2 className="client-edit-title">Modifier : {form.nom} {form.prenom}</h2>

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

        <select name="role" value={form.role} onChange={handleChange}>
          <option value="client">Client</option>
          <option value="admin">Admin</option>
        </select>

        <button type="submit" onClick={handleSubmit}>
          Valider
        </button>

        <button className="delete-button" onClick={handleDelete}>
          Supprimer le client
        </button>
      </div>
    </div>
  );
};

export default EditClient;
