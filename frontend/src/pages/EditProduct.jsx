// src/pages/EditProduct.jsx
import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import "../styles/EditProduct.css";

const EditProduct = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  const [form, setForm] = useState({
    nom_produit: "",
    prix: "",
    description: "",
    id_categorie: "",
  });

  const [categories, setCategories] = useState([]);
  const [images, setImages] = useState([]);

  useEffect(() => {
    fetch(`http://localhost:3000/api/produit/${id}`)
      .then((res) => res.json())
      .then((data) => {
        setForm({
          nom_produit: data.nom_produit || "",
          prix: data.prix || "",
          description: data.description || "",
          id_categorie: data.id_categorie || "",
        });
        setImages(data.images || []);
      });

    fetch("http://localhost:3000/categorie")
      .then((res) => res.json())
      .then(setCategories);
  }, [id]);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleUpdate = async () => {
    const res = await fetch(`http://localhost:3000/api/produit/${id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(form),
    });
    const data = await res.json();
    if (!res.ok) {
      alert("Erreur lors de la mise à jour");
      return;
    }
    alert("Produit mis à jour !");
    navigate("/admin");
  };

  const handleDeleteImage = async (imageId) => {
    try {
      const res = await fetch(
        `http://localhost:3000/api/produit/${id}/image/${imageId}`,
        {
          method: "DELETE",
        }
      );
      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || "Erreur suppression image");
      }

      setImages((prev) => prev.filter((img) => img.id_image !== imageId));
    } catch (err) {
      alert("Erreur lors de la suppression de l'image");
      console.error(err);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm("Supprimer ce produit ?")) return;
    const res = await fetch(`http://localhost:3000/api/produit/${id}`, {
      method: "DELETE",
    });
    const data = await res.json();
    if (!res.ok) {
      alert("Erreur lors de la suppression");
      return;
    }
    alert("Produit supprimé !");
    navigate("/admin");
  };

  return (
    <div className="edit-product-page">
      <form className="edit-product-form" onSubmit={(e) => e.preventDefault()}>
        <input
          type="text"
          name="nom_produit"
          placeholder="Nom du produit"
          value={form.nom_produit}
          onChange={handleChange}
        />

        <div className="input-row">
          <input
            type="number"
            name="prix"
            placeholder="Prix"
            value={form.prix}
            onChange={handleChange}
          />
          <select
            name="id_categorie"
            value={form.id_categorie}
            onChange={handleChange}
          >
            <option value="">Catégorie</option>
            {categories.map((cat) => (
              <option key={cat.id_categorie} value={cat.id_categorie}>
                {cat.nom}
              </option>
            ))}
          </select>
        </div>

        <textarea
          name="description"
          placeholder="Description"
          value={form.description}
          onChange={handleChange}
        />

        <div className="image-preview-container">
          {images.map((img, i) => (
            <div key={i} className="image-preview-wrapper">
              <img src={img.lien} alt={`img-${i}`} />
              <button type="button" onClick={() => handleDeleteImage(img.id_image)}>×</button>
            </div>
          ))}
        </div>

        <label className="image-upload-label">
          Ajouter des Images
          <input type="file" multiple hidden />
        </label>

        <div className="action-buttons">
          <button type="submit" onClick={handleUpdate}>
            Valider
          </button>
        </div>

        <button className="delete-button" onClick={handleDelete}>
          SUPPRIMER L'ANNONCE
        </button>
      </form>
    </div>
  );
};

export default EditProduct;
