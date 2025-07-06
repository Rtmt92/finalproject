import React, { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import ProductCard from "../compenents/ProductCard";
import "../styles/AllProducts.css"; // utilise les mêmes styles responsives

const API_BASE = "http://localhost:8000";

export default function SearchResults() {
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
  }, []);

  useEffect(() => {
    fetchAnnonces();
  }, [query, selectedCat, selectedEtat]);

  const fetchAnnonces = async () => {
    const params = new URLSearchParams();
    if (selectedCat) params.set("categorie", parseInt(selectedCat));
    if (selectedEtat) params.set("etat", selectedEtat);
    if (query) params.set("q", query);

    const url = `${API_BASE}/api/produit?${params.toString()}`;

    try {
      const res = await fetch(url);
      if (!res.ok) throw new Error("Erreur réseau");
      const data = await res.json();
      setAnnonces(data);
    } catch (err) {
      console.error("Erreur chargement produits :", err);
      setAnnonces([]);
    }
  };

  const fetchCategories = async () => {
    try {
      const res = await fetch(`${API_BASE}/categorie`);
      if (!res.ok) throw new Error("Erreur catégories");
      const data = await res.json();
      setCategories(data);
    } catch (err) {
      console.error("Erreur chargement catégories :", err);
      setCategories([]);
    }
  };

  return (
    <main className="all-products-page">
      <div className="all-products-header">
        <h2>Résultats pour « {query} »</h2>
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
        {annonces.length > 0 ? (
          annonces.map((prod, i) => (
            <ProductCard
              key={i}
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
