import React, { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import ProductCard from "../compenents/ProductCard";
import "../styles/AllProducts.css";
import API_BASE_URL from "../config"; // ← base URL centralisée

export default function SearchResults() {
  const location = useLocation();
  const query = new URLSearchParams(location.search).get("q") || "";

  const [annonces, setAnnonces] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCat, setSelectedCat] = useState("");
  const [selectedEtat, setSelectedEtat] = useState("");

  // Charger les catégories au montage
  useEffect(() => {
    fetch(`${API_BASE_URL}/categorie`)
      .then(res => {
        if (!res.ok) throw new Error("Erreur réseau");
        return res.json();
      })
      .then(setCategories)
      .catch(err => {
        console.error("Erreur chargement catégories :", err);
        setCategories([]);
      });
  }, []);

  // Recharger les produits à chaque changement
  useEffect(() => {
    const params = new URLSearchParams();
    if (selectedCat)  params.set("categorie", parseInt(selectedCat, 10));
    if (selectedEtat) params.set("etat", selectedEtat);
    if (query)        params.set("q", query);

    fetch(`${API_BASE_URL}/api/produit?${params.toString()}`)
      .then(res => {
        if (!res.ok) throw new Error("Erreur chargement produits");
        return res.json();
      })
      .then(setAnnonces)
      .catch(err => {
        console.error("Erreur :", err);
        setAnnonces([]);
      });
  }, [query, selectedCat, selectedEtat]);

  return (
    <main className="all-products-page">
      <div className="all-products-header">
        <h2>Résultats pour « {query} »</h2>

        <div className="filters">
          <select
            value={selectedCat}
            onChange={(e) => setSelectedCat(e.target.value)}
          >
            <option value="">Toutes les catégories</option>
            {categories.map((cat) => (
              <option key={cat.id_categorie} value={cat.id_categorie}>
                {cat.nom}
              </option>
            ))}
          </select>

          <select
            value={selectedEtat}
            onChange={(e) => setSelectedEtat(e.target.value)}
          >
            <option value="">Tous les états</option>
            <option value="parfait état">Parfait état</option>
            <option value="très bon état">Très bon état</option>
            <option value="correct">Correct</option>
          </select>
        </div>
      </div>

      <div className="products-grid-all">
        {annonces.length > 0 ? (
          annonces.map((prod) => (
            <ProductCard
              key={prod.id}
              id={prod.id}
              titre={prod.titre}
              image={prod.image}
              prix={prod.prix}
              etat={prod.etat}
              quantite={prod.quantite}
            />
          ))
        ) : (
          <p className="status">Aucun produit trouvé.</p>
        )}
      </div>
    </main>
  );
}
