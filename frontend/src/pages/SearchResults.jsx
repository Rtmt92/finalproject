// frontend/src/pages/SearchResults.jsx
import React, { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import ProductBanner from "../compenents/ProductBanner";

const SearchResults = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const query = new URLSearchParams(location.search).get("q") || "";

  const [annonces, setAnnonces] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCat, setSelectedCat] = useState("");
  const [selectedEtat, setSelectedEtat] = useState("");

  const token = localStorage.getItem("token");

  useEffect(() => {
    if (!token) {
      navigate("/login", { state: { from: location }, replace: true });
      return;
    }

    fetchCategories();
    fetchAnnonces();
  }, [query, selectedCat, selectedEtat]);

  const fetchAnnonces = async () => {
    let url = `http://localhost:3000/api/produit?q=${encodeURIComponent(query)}`;
    if (selectedCat) url += `&categorie=${selectedCat}`;
    if (selectedEtat) url += `&etat=${encodeURIComponent(selectedEtat)}`;

    const res = await fetch(url);
    const data = await res.json();
    setAnnonces(data);
  };

  const fetchCategories = async () => {
    const res = await fetch("http://localhost:3000/categorie");
    const data = await res.json();
    setCategories(data);
  };

  return (
    <div className="admin-home">
      <h2 className="admin-title">Résultats pour « {query} »</h2>

      <div className="admin-layout">
        <div className="admin-sidebar">
          <select value={selectedCat} onChange={(e) => setSelectedCat(e.target.value)}>
            <option value="">Toutes les catégories</option>
            {categories.map((c) => (
              <option key={c.id_categorie} value={c.id_categorie}>{c.nom}</option>
            ))}
          </select>

          <select value={selectedEtat} onChange={(e) => setSelectedEtat(e.target.value)}>
            <option value="">Tous les états</option>
            <option value="parfait état">Parfait état</option>
            <option value="très bon état">Très bon état</option>
            <option value="correct">Correct</option>
          </select>
        </div>

        <div className="admin-content">
          {annonces.length > 0 ? annonces.map((a, i) => (
            <ProductBanner key={i} {...a} />
          )) : (
            <p>Aucun produit trouvé.</p>
          )}
        </div>
      </div>
    </div>
  );
};

export default SearchResults;
