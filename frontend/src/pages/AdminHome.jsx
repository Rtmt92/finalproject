import React, { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import ProductBanner from "../compenents/ProductBanner";
import "../styles/AdminHome.css";

const API_BASE = "http://localhost:8000";

export default function AdminHome() {
  const [annonces, setAnnonces]     = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCat,  setSelectedCat]  = useState("");
  const [selectedEtat, setSelectedEtat] = useState("");
  const { search: locationSearch }  = useLocation();

  // On lit ?search=… dans l'URL
  const query = new URLSearchParams(locationSearch).get("search") || "";

  // Charger les catégories au montage
  useEffect(() => {
    fetch(`${API_BASE}/categorie`)
      .then(res => res.json())
      .then(setCategories)
      .catch(err => console.error("Erreur chargement catégories :", err));
  }, []);

  // Dès que selectedCat, selectedEtat ou query change → re-fetch
  useEffect(() => {
    const params = new URLSearchParams();

    if (selectedCat)  params.set("categorie", selectedCat);
    if (selectedEtat) params.set("etat", selectedEtat);
    if (query)        params.set("q", query);

    const url = `${API_BASE}/api/produit?${params.toString()}`;

    fetch(url)
      .then(res => res.json())
      .then(setAnnonces)
      .catch(err => console.error("Erreur chargement produits :", err));
  }, [selectedCat, selectedEtat, query]);

  return (
    <div className="admin-home">
      <h2 className="admin-title">Toutes les offres</h2>

      <div className="admin-layout">
        <div className="admin-sidebar">
          <select
            className="category-select"
            value={selectedCat}
            onChange={e => setSelectedCat(e.target.value)}
          >
            <option value="">Toutes les catégories</option>
            {categories.map(cat => (
              <option key={cat.id_categorie} value={cat.id_categorie}>
                {cat.nom}
              </option>
            ))}
          </select>

          <select
            className="etat-select"
            value={selectedEtat}
            onChange={e => setSelectedEtat(e.target.value)}
          >
            <option value="">Tous les états</option>
            <option value="parfait état">Parfait état</option>
            <option value="très bon état">Très bon état</option>
            <option value="correct">Correct</option>
          </select>
        </div>

        <div className="admin-content">
          {annonces.map(a => (
            <ProductBanner
              key={a.id}
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
}
