import React, { useEffect, useState } from "react";
import "../styles/EditProduct.css";

const AdminCategorie = () => {
  const [nom, setNom] = useState("");
  const [categories, setCategories] = useState([]);
  const [selectedId, setSelectedId] = useState("");
  const [modifNom, setModifNom] = useState("");

  const fetchCategories = () => {
    fetch("http://localhost:3000/categorie")
      .then((res) => res.json())
      .then((data) => setCategories(data));
  };

  useEffect(() => {
    fetchCategories();
  }, []);

  const handleAdd = async () => {
    if (!nom.trim()) return;
    const res = await fetch("http://localhost:3000/categorie", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ nom }),
    });
    if (res.ok) {
      setNom("");
      fetchCategories();
    }
  };

  const handleUpdate = async () => {
    if (!selectedId || !modifNom.trim()) return;
    const res = await fetch(`http://localhost:3000/categorie/${selectedId}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ nom: modifNom }),
    });
    if (res.ok) {
      setModifNom("");
      setSelectedId("");
      fetchCategories();
    }
  };

  const handleDelete = async () => {
    if (!selectedId) return;
    const res = await fetch(`http://localhost:3000/categorie/${selectedId}`, {
      method: "DELETE",
    });
    if (res.ok) {
      setModifNom("");
      setSelectedId("");
      fetchCategories();
    }
  };

  return (
    <div className="edit-product-page">
      <h3 className="client-edit-title">Ajouter une categorie</h3>
      <input
        type="text"
        placeholder="Nom de la categorie"
        value={nom}
        onChange={(e) => setNom(e.target.value)}
      />
      <button onClick={handleAdd}>Ajouter la categorie</button>

      <hr />

      <h3 className="client-edit-title">Modifier une categorie</h3>
      <select
        value={selectedId}
        onChange={(e) => setSelectedId(e.target.value)}
      >
        <option value="">-- Choisir une categorie --</option>
        {categories.map((cat) => (
          <option key={cat.id_categorie} value={cat.id_categorie}>
            {cat.nom}
          </option>
        ))}
      </select>

      <textarea
        placeholder="Modifier le nom"
        value={modifNom}
        onChange={(e) => setModifNom(e.target.value)}
      />

      <button onClick={handleUpdate}>MODIFIER LA CATEGORIE</button>
      <button className="delete-button" onClick={handleDelete}>
        SUPPRIMER LA CATEGORIE
      </button>
    </div>
  );
};

export default AdminCategorie;
