import React, { useState } from 'react';
import '../styles/CreateAnnounce.css'; 

const CreateAnnounce = () => {
  const [formData, setFormData] = useState({
    name: '',
    price: '',
    category: '',
    description: '',
    images: [],
  });

  const handleChange = (e) => {
    const { name, value, files } = e.target;
    if (name === 'images') {
      setFormData({ ...formData, images: files });
    } else {
      setFormData({ ...formData, [name]: value });
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Formulaire soumis :', formData);
  };

  return (
    <form className="form" onSubmit={handleSubmit}>
      <input
        type="text"
        name="name"
        placeholder="Nom du produit"
        value={formData.name}
        onChange={handleChange}
        className="full-input"
      />

      <div className="row">
        <input
          type="text"
          name="price"
          placeholder="Prix"
          value={formData.price}
          onChange={handleChange}
          className="half-input"
        />
        <select
          name="category"
          value={formData.category}
          onChange={handleChange}
          className="half-input"
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
        rows="5"
        className="textarea"
      />

      <input
        type="file"
        name="images"
        onChange={handleChange}
        multiple
        className="full-input"
      />

      <button type="submit" className="button">
        Valider
      </button>
    </form>
  );
};

export default CreateAnnounce;