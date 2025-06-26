// src/pages/AdminHome.jsx
import React, { useEffect, useState } from "react";
import ProductBanner from "../compenents/ProductBanner";
import "../styles/AdminHome.css";

const AdminHome = () => {
  const [annonces, setAnnonces] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCat, setSelectedCat] = useState('');
  const [selectedEtat, setSelectedEtat] = useState('');

  useEffect(() => {
    fetchCategories();
  }, []);

  useEffect(() => {
    fetchAnnonces(selectedCat, selectedEtat);
  }, [selectedCat, selectedEtat]);

  const fetchAnnonces = async (categoryId = '', etat = '') => {
    try {
      let url = "http://localhost:3000/api/produit";
      const params = [];

      if (categoryId) params.push(`categorie=${categoryId}`);
      if (etat)       params.push(`etat=${encodeURIComponent(etat)}`);

      if (params.length > 0) {
        url += '?' + params.join('&');
      }

      const res = await fetch(url);
      const data = await res.json();
      setAnnonces(data);
    } catch (err) {
      console.error("Erreur lors du chargement des produits :", err);
    }
  };

  const fetchCategories = async () => {
    try {
      const res = await fetch("http://localhost:3000/categorie");
      const data = await res.json();
      setCategories(data);
    } catch (err) {
      console.error("Erreur lors du chargement des catégories :", err);
    }
  };

  return (
    <div className="admin-home">
      <h2 className="admin-title">Toutes les offres</h2>

      <div className="admin-layout">
        <div className="admin-sidebar">
          <select className="category-select" value={selectedCat} onChange={e => setSelectedCat(e.target.value)}>
            <option value="">Toutes les catégories</option>
            {Array.isArray(categories) && categories.map(cat => (
              <option key={cat.id_categorie} value={cat.id_categorie}>{cat.nom}</option>
            ))}
          </select>

          <select className="etat-select" value={selectedEtat} onChange={e => setSelectedEtat(e.target.value)}>
            <option value="">Tous les états</option>
            <option value="parfait état">Parfait état</option>
            <option value="très bon état">Très bon état</option>
            <option value="correct">Correct</option>
          </select>
        </div>

        <div className="admin-content">
          {Array.isArray(annonces) && annonces.map((a, i) => (
            <ProductBanner
            key={i}
            id={a.id}
            titre={a.titre}
            description={a.description}
            prix={a.prix}
            image={a.image}
            etat={a.etat}
            quantite={a.quantite}
            />

          ))}
        </div>
      </div>
    </div>
  );
};

export default AdminHome;
