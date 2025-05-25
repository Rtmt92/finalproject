import React, { useState } from 'react';
import '../styles/UpdateAnnounce.css';

const UpdateAnnounce = ({ initialData, onDelete }) => {
  const [formData, setFormData] = useState({
    name: initialData?.name || '',
    price: initialData?.price || '',
    category: initialData?.category || '',
    description: initialData?.description || '',
    images: initialData?.images || [],
    newImages: [],
  });

  const handleChange = (e) => {
    const { name, value, files } = e.target;
    if (name === 'newImages') {
      setFormData((prev) => ({ ...prev, newImages: Array.from(files) }));
    } else {
      setFormData((prev) => ({ ...prev, [name]: value }));
    }
  };

  const handleDeleteImage = (index) => {
    setFormData((prev) => {
      const updatedImages = [...prev.images];
      updatedImages.splice(index, 1);
      return { ...prev, images: updatedImages };
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const updatedData = {
      ...formData,
      images: formData.images,
      newImages: formData.newImages,
    };
    console.log('Annonce mise à jour :', updatedData);
    // Ici tu peux envoyer updatedData à ton backend plus tard
  };

  return (
    <form onSubmit={handleSubmit} className="form">
      <input
        type="text"
        name="name"
        placeholder="Nom du produit"
        value={formData.name}
        onChange={handleChange}
        className="fullInput"
      />

      <div className="row">
        <input
          type="text"
          name="price"
          placeholder="Prix"
          value={formData.price}
          onChange={handleChange}
          className="halfInput"
        />
        <select
          name="category"
          value={formData.category}
          onChange={handleChange}
          className="halfInput"
        >
          <option value="">Catégorie</option>
          <option value="electronique">Électronique</option>
          <option value="vetements">Vêtements</option>
          <option value="livres">Livres</option>
        </select>
      </div>

      <textarea
        name="description"
        placeholder="Description"
        value={formData.description}
        onChange={handleChange}
        rows={5}
        className="textarea"
      />

      <div className="imagePreviewContainer">
        {formData.images.map((img, index) => (
          <div key={index} className="imageWrapper">
            <img
              src={typeof img === 'string' ? img : URL.createObjectURL(img)}
              alt={`img-${index}`}
              className="image"
            />
            <button
              type="button"
              onClick={() => handleDeleteImage(index)}
              className="deleteImageBtn"
            >
              ❌
            </button>
          </div>
        ))}
      </div>

      <input
        type="file"
        name="newImages"
        onChange={handleChange}
        multiple
        className="fullInput"
      />

      <button type="submit" className="button">
        Valider
      </button>

      <button type="button" onClick={onDelete} className="deleteBtn">
        SUPPRIMER MON ANNONCE
      </button>
    </form>
  );
};

export default UpdateAnnounce;
