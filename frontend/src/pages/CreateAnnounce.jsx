import React, { useState, useEffect } from 'react';
import '../styles/CreateAnnounce.css';
import API_BASE_URL from '../config';

const CreateAnnounce = () => {
  const [formData, setFormData] = useState({
    nom_produit: '',
    prix: '',
    id_categorie: '',
    description: '',
    etat: 'très bon état',
    quantite: 1,
    images: [],
  });

  const [categories, setCategories] = useState([]);
  const [imagePreviews, setImagePreviews] = useState([]);
  const [loading, setLoading] = useState(false);

  const etatOptions = ['parfait état', 'très bon état', 'correct'];

  useEffect(() => {
    fetch(`${API_BASE_URL}/categorie`)
      .then((res) => res.json())
      .then((data) => {
        if (Array.isArray(data)) {
          setCategories(data);
        } else if (Array.isArray(data.categories)) {
          setCategories(data.categories);
        } else {
          console.error("Format de données inattendu :", data);
          setCategories([]);
        }
      })
      .catch((err) => {
        console.error("Erreur chargement catégories :", err);
        setCategories([]);
      });
  }, []);

  const handleChange = (e) => {
    const { name, value, files } = e.target;

    if (name === 'images') {
      const validFiles = Array.from(files).filter(file =>
        file.type === 'image/jpeg' || file.type === 'image/png'
      );

      if (validFiles.length !== files.length) {
        alert("Seuls les fichiers JPG ou PNG sont autorisés.");
      }

      setFormData((prev) => ({
        ...prev,
        images: [...prev.images, ...validFiles]
      }));

      const newPreviews = validFiles.map(file => ({
        file,
        url: URL.createObjectURL(file)
      }));

      setImagePreviews(prev => [...prev, ...newPreviews]);
    } else {
      setFormData((prev) => ({ ...prev, [name]: value }));
    }
  };

  const handleRemoveImage = (indexToRemove) => {
    setImagePreviews(prev => prev.filter((_, i) => i !== indexToRemove));
    setFormData(prev => ({
      ...prev,
      images: prev.images.filter((_, i) => i !== indexToRemove),
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    const payload = {
      nom_produit: formData.nom_produit,
      prix: parseFloat(formData.prix),
      id_categorie: parseInt(formData.id_categorie),
      description: formData.description,
      etat: formData.etat,
      quantite: parseInt(formData.quantite),
    };

    try {
      const res = await fetch(`${API_BASE_URL}/api/produit`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const produit = await res.json();
      if (!res.ok || !produit?.id_produit) {
        alert("Erreur lors de la création du produit");
        setLoading(false);
        return;
      }

      if (!formData.images || formData.images.length === 0) {
        alert("Veuillez sélectionner au moins une image");
        setLoading(false);
        return;
      }

      const formImages = new FormData();
      formImages.append('id_produit', produit.id_produit);

      formData.images.forEach(img => {
        formImages.append('images[]', img);
      });

      const resUpload = await fetch(`${API_BASE_URL}/api/upload-images`, {
        method: 'POST',
        body: formImages,
      });

      const result = await resUpload.json();
      if (!resUpload.ok || !Array.isArray(result.uploaded)) {
        alert("Erreur lors de l'envoi des images");
        setLoading(false);
        return;
      }

      alert("Annonce créée avec succès !");
      setFormData({
        nom_produit: '',
        prix: '',
        id_categorie: '',
        description: '',
        etat: 'très bon état',
        quantite: 1,
        images: [],
      });
      setImagePreviews([]);
    } catch (err) {
      alert("Erreur réseau");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form className="form" onSubmit={handleSubmit}>
      <input
        type="text"
        name="nom_produit"
        placeholder="Nom du produit"
        value={formData.nom_produit}
        onChange={handleChange}
        className="full-input"
      />

      <div className="row">
        <input
          type="number"
          name="prix"
          step="0.01"
          placeholder="Prix"
          value={formData.prix}
          onChange={handleChange}
          className="half-input"
        />
        <select
          name="id_categorie"
          value={formData.id_categorie}
          onChange={handleChange}
          className="half-input"
          required
        >
          <option value="">Catégorie</option>
          {categories.map(cat => (
            <option key={cat.id_categorie} value={cat.id_categorie}>
              {cat.nom}
            </option>
          ))}
        </select>
      </div>

      <div className="row">
        <select
          name="etat"
          value={formData.etat}
          onChange={handleChange}
          className="half-input"
        >
          {etatOptions.map((etat, index) => (
            <option key={index} value={etat}>{etat}</option>
          ))}
        </select>

        <input
          type="number"
          name="quantite"
          min="1"
          placeholder="Quantité"
          value={formData.quantite}
          onChange={handleChange}
          className="half-input"
        />
      </div>

      <textarea
        name="description"
        placeholder="Description"
        value={formData.description}
        onChange={handleChange}
        rows="5"
        className="textarea"
      />

      <input
        type="file"
        name="images"
        accept=".jpg,.jpeg,.png"
        onChange={handleChange}
        multiple
        className="full-input"
      />

      <div className="image-previews">
        {imagePreviews.map((img, i) => (
          <div key={i} className="image-preview-item">
            <img src={img.url} alt={`preview-${i}`} />
            <button type="button" onClick={() => handleRemoveImage(i)} className="btn-remove-image">
              ✕
            </button>
          </div>
        ))}
      </div>

      <button type="submit" className="button" disabled={loading}>
        {loading ? "Chargement..." : "Valider"}
      </button>
    </form>
  );
};

export default CreateAnnounce;
