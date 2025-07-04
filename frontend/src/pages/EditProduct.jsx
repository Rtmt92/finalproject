// src/pages/EditProduct.jsx
import React, { useEffect, useState, useRef } from "react";
import { useParams, useNavigate } from "react-router-dom";
import "../styles/EditProduct.css";

const API_BASE = "http://localhost:8000";

export default function EditProduct() {
  const { id } = useParams();
  const navigate = useNavigate();
  const fileInputRef = useRef(null);

  const [form, setForm] = useState({
    nom_produit: "",
    prix: "",
    description: "",
    id_categorie: "",     // catégorie sélectionnée (ou vide)
    quantite: "",
    etat: "très bon état",
  });
  const [categories, setCategories] = useState([]);
  const [images, setImages] = useState([]);
  const etats = ["parfait état", "très bon état", "correct"];

  const normalizeUrl = (url) => {
    if (!url) return "";
    if (url.startsWith("http")) return url;
    return `${API_BASE}/${url.replace(/^\/+/, "")}`;
  };

  useEffect(() => {
    // 1) Charger le produit et ses images
    fetch(`${API_BASE}/api/produit/${id}`)
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
        if (Array.isArray(data.images)) {
          setImages(
            data.images.map((img) => ({
              id_image: img.id_image,
              url: normalizeUrl(img.lien),
            }))
          );
        }
      })
      .catch(console.error);

    // 2) Charger les catégories
    fetch(`${API_BASE}/categorie`)
      .then((res) => {
        if (!res.ok) throw new Error("Impossible de charger les catégories");
        return res.json();
      })
      .then((data) => setCategories(data))
      .catch((err) => {
        console.error(err);
        alert("Échec du chargement des catégories");
      });
  }, [id]);

  const handleChange = (e) =>
    setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

  const handleUpdate = async () => {
    const res = await fetch(`${API_BASE}/api/produit/${id}`, {
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
    const res = await fetch(
      `${API_BASE}/api/produit/${id}/image/${imageId}`,
      { method: "DELETE" }
    );
    if (!res.ok) {
      alert("Erreur suppression image");
      return;
    }
    setImages((prev) => prev.filter((img) => img.id_image !== imageId));
  };

  const handleDeleteProduct = async () => {
    if (!window.confirm("Supprimer ce produit ?")) return;
    const res = await fetch(`${API_BASE}/api/produit/${id}`, {
      method: "DELETE",
    });
    if (!res.ok) {
      alert("Erreur lors de la suppression");
      return;
    }
    alert("Produit supprimé !");
    navigate("/admin");
  };

  const handleFilesChange = (e) => {
    const files = Array.from(e.target.files);
    const token = localStorage.getItem("token");

    files.forEach((file) => {
      const preview = URL.createObjectURL(file);
      setImages((prev) => [...prev, { id_image: null, preview }]);

      const formData = new FormData();
      formData.append("image", file);

      fetch(`${API_BASE}/api/produit/${id}/image`, {
        method: "POST",
        headers: { Authorization: `Bearer ${token}` },
        body: formData,
      })
        .then(async (res) => {
          const text = await res.text();
          let json = null;
          try { json = JSON.parse(text); } catch {}
          if (!res.ok) {
            throw new Error((json && json.error) || res.statusText);
          }
          if (!json.url) throw new Error("Aucune URL retournée");
          return json.url;
        })
        .then((url) => {
          const abs = normalizeUrl(url);
          setImages((prev) =>
            prev
              .filter((img) => !img.preview)
              .concat([{ id_image: null, url: abs }])
          );
        })
        .catch((err) => {
          console.error("Upload error:", err);
          alert("Échec upload : " + err.message);
        });
    });

    e.target.value = "";
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

          <select
            name="id_categorie"
            value={form.id_categorie}
            onChange={handleChange}
          >
            {/* Option “pas de catégorie” */}
            <option value="">Aucune catégorie</option>
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
              <img src={img.preview || img.url} alt={`img-${i}`} />
              <button
                type="button"
                onClick={() =>
                  img.id_image
                    ? handleDeleteImage(img.id_image)
                    : setImages((prev) => prev.filter((_, idx) => idx !== i))
                }
              >
                ×
              </button>
            </div>
          ))}
        </div>

        <label
          className="add-images-label"
          onClick={() => fileInputRef.current.click()}
        >
          Ajouter des Images
        </label>
        <input
          ref={fileInputRef}
          type="file"
          multiple
          hidden
          accept="image/*"
          onChange={handleFilesChange}
        />

        <button
          type="button"
          className="submit-btn"
          onClick={handleUpdate}
        >
          Valider
        </button>
        <button
          type="button"
          className="delete-btn"
          onClick={handleDeleteProduct}
        >
          SUPPRIMER L'ANNONCE
        </button>
      </form>
    </div>
  );
}
