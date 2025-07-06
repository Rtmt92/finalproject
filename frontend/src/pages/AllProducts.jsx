// src/pages/AllProducts.jsx
import React, { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import ProductCard from "../compenents/ProductCard";
import "../styles/AllProducts.css";
import API_BASE_URL from "../config";

export default function AllProducts() {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCat, setSelectedCat] = useState("");
  const [selectedEtat, setSelectedEtat] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const { search: locationSearch } = useLocation();

  const query = new URLSearchParams(locationSearch).get("search") || "";

  // Charger les catégories une fois
  useEffect(() => {
    fetch(`${API_BASE_URL}/categorie`)
      .then((res) => res.json())
      .then(setCategories)
      .catch(() => setCategories([]));
  }, []);

  // Charger les produits à chaque changement de filtre ou recherche
  useEffect(() => {
    const params = new URLSearchParams();

    if (selectedCat) params.set("categorie", parseInt(selectedCat));
    if (selectedEtat) params.set("etat", selectedEtat);
    if (query) params.set("q", query);

    const url = `${API_BASE_URL}/api/produit?${params.toString()}`;
    setLoading(true);

    fetch(url)
      .then((res) => {
        if (!res.ok) throw new Error(`Erreur ${res.status}`);
        return res.json();
      })
      .then((data) => {
        setProducts(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error(err);
        setError("Impossible de charger les produits.");
        setLoading(false);
      });
  }, [selectedCat, selectedEtat, query]);

  if (loading) return <p className="status">Chargement…</p>;
  if (error) return <p className="status error">{error}</p>;

  return (
    <main className="all-products-page">
      <div className="all-products-header">
        <h2>Tout nos produits :</h2>
        <div className="filters">
          <select value={selectedCat} onChange={(e) => setSelectedCat(e.target.value)}>
            <option value="">Toutes les catégories</option>
            {categories.map((cat) => (
              <option key={cat.id_categorie} value={cat.id_categorie}>
                {cat.nom}
              </option>
            ))}
          </select>

          <select value={selectedEtat} onChange={(e) => setSelectedEtat(e.target.value)}>
            <option value="">Tous les états</option>
            <option value="parfait état">Parfait état</option>
            <option value="très bon état">Très bon état</option>
            <option value="correct">Correct</option>
          </select>
        </div>
      </div>

      <div className="products-grid-all">
        {products.length > 0 ? (
          products.map((prod) => (
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
