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
    quantite: "",
    etat: "très bon état",
  });

  const [categories, setCategories] = useState([]);
  const [images, setImages] = useState([]);

  const etats = ["parfait état", "très bon état", "correct"];

  useEffect(() => {
    fetch(`http://localhost:3000/api/produit/${id}`)
      .then((res) => res.json())
      .then((data) => {
        setForm({
          nom_produit: data.nom_produit || "",
          prix: data.prix || "",
          description: data.description || "",
          id_categorie: data.id_categorie || "",
          quantite: data.quantite || 1,
          etat: data.etat || "très bon état",
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
    if (!res.ok) {
      alert("Erreur lors de la mise à jour");
      return;
    }
    alert("Produit mis à jour !");
    navigate("/admin");
  };

  const handleDeleteImage = async (imageId) => {
    const res = await fetch(`http://localhost:3000/api/produit/${id}/image/${imageId}`, {
      method: "DELETE",
    });
    if (!res.ok) {
      alert("Erreur suppression image");
      return;
    }
    setImages((prev) => prev.filter((img) => img.id_image !== imageId));
  };

  const handleDelete = async () => {
    if (!window.confirm("Supprimer ce produit ?")) return;
    const res = await fetch(`http://localhost:3000/api/produit/${id}`, {
      method: "DELETE",
    });
    if (!res.ok) {
      alert("Erreur lors de la suppression");
      return;
    }
    alert("Produit supprimé !");
    navigate("/admin");
  };

  return (
    <div className="edit-page">
      <form className="edit-form" onSubmit={(e) => e.preventDefault()}>
        <input
          type="text"
          name="nom_produit"
          placeholder="Nom du produit"
          value={form.nom_produit}
          onChange={handleChange}
        />

        <div className="row">
          <input
            type="number"
            name="prix"
            placeholder="Prix"
            value={form.prix}
            onChange={handleChange}
          />
          <select name="id_categorie" value={form.id_categorie} onChange={handleChange}>
            <option value="">Catégorie</option>
            {categories.map((cat) => (
              <option key={cat.id_categorie} value={cat.id_categorie}>
                {cat.nom}
              </option>
            ))}
          </select>
        </div>

        <div className="row">
          <input
            type="number"
            name="quantite"
            placeholder="Quantité"
            min="0"
            value={form.quantite}
            onChange={handleChange}
          />
          <select name="etat" value={form.etat} onChange={handleChange}>
            {etats.map((e, i) => (
              <option key={i} value={e}>
                {e}
              </option>
            ))}
          </select>
        </div>

        <textarea
          name="description"
          placeholder="Description"
          value={form.description}
          onChange={handleChange}
          rows="4"
        />

        <div className="images-wrapper">
          {images.map((img, i) => (
            <div key={i} className="img-preview">
              <img src={img.lien} alt={`img-${i}`} />
              <button type="button" onClick={() => handleDeleteImage(img.id_image)}>×</button>
            </div>
          ))}
        </div>

        <label className="add-images-label">
          Ajouter des Images
          <input type="file" multiple hidden />
        </label>

        <button type="button" className="submit-btn" onClick={handleUpdate}>
          Valider
        </button>

        <button type="button" className="delete-btn" onClick={handleDelete}>
          SUPPRIMER L'ANNONCE
        </button>
      </form>
    </div>
  );
};

export default EditProduct;
