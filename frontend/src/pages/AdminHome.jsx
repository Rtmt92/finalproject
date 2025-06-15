// src/pages/AdminHome.jsx
import React, { useEffect, useState } from "react";
import ProductBanner from "../compenents/ProductBanner";
import "../styles/AdminHome.css";

const AdminHome = () => {
  const [annonces, setAnnonces] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCat, setSelectedCat] = useState('');

  useEffect(() => {
    fetchAnnonces();
    fetchCategories();
  }, []);

  const fetchAnnonces = async (categoryId = '') => {
    try {
      const url = categoryId
        ? `http://localhost:3000/api/produit?categorie=${categoryId}`
        : "http://localhost:3000/api/produit";
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

  const handleCategoryChange = (e) => {
    const id = e.target.value;
    setSelectedCat(id);
    fetchAnnonces(id);
  };

  return (
    <div className="admin-home">
      <h2 className="admin-title">Toutes les offres</h2>

      <div className="admin-layout">
        <div className="admin-sidebar">
          <select className="category-select" value={selectedCat} onChange={handleCategoryChange}>
            <option value="">Toutes les catégories</option>
            {Array.isArray(categories) && categories.map(cat => (
              <option key={cat.id_categorie} value={cat.id_categorie}>{cat.nom}</option>
            ))}
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
            />

          ))}
        </div>
      </div>
    </div>
  );
};

export default AdminHome;
