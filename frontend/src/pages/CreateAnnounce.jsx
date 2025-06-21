import React, { useState } from 'react';
import '../styles/CreateAnnounce.css';

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

  const etatOptions = ['parfait état', 'très bon état', 'correct'];

  const handleChange = (e) => {
    const { name, value, files } = e.target;
    if (name === 'images') {
      setFormData({ ...formData, images: files });
    } else {
      setFormData({ ...formData, [name]: value });
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const payload = {
      nom_produit: formData.nom_produit,
      prix: parseFloat(formData.prix),
      id_categorie: parseInt(formData.id_categorie),
      description: formData.description,
      etat: formData.etat,
      quantite: parseInt(formData.quantite),
    };

    try {
      const response = await fetch('http://localhost:8000/api/produit', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const result = await response.json();

      if (response.ok) {
        alert('Produit créé avec succès !');
        setFormData({
          nom_produit: '',
          prix: '',
          id_categorie: '',
          description: '',
          etat: 'très bon état',
          quantite: 1,
          images: [],
        });
      } else {
        alert('Erreur lors de la création : ' + (result.error || 'Erreur inconnue'));
      }
    } catch (err) {
      alert('Erreur de connexion au serveur');
      console.error(err);
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
        >
          <option value="">Catégorie</option>
          <option value="1">Électronique</option>
          <option value="2">Vêtements</option>
          <option value="3">Livres</option>
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
