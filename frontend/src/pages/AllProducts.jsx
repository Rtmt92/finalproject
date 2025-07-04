// frontend/src/pages/AllProducts.jsx
import React, { useEffect, useState } from 'react';
import ProductCard from '../compenents/ProductCard';
import '../styles/AllProducts.css';

export default function AllProducts() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading]   = useState(true);
  const [error, setError]       = useState(null);

  useEffect(() => {
    fetch('http://localhost:8000/api/produit')
      .then(res => {
        if (!res.ok) throw new Error(`Erreur ${res.status}`);
        return res.json();
      })
      .then(data => {
        setProducts(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setError('Impossible de charger les produits.');
        setLoading(false);
      });
  }, []);

  if (loading) return <p className="status">Chargement…</p>;
  if (error)   return <p className="status error">{error}</p>;

  return (
    <main className="all-products">
      <h1>Tous les produits</h1>
      <div className="products-grid">
        {products.map(prod => (
          <ProductCard
            key={prod.id}
            id={prod.id}
            titre={prod.titre}
            image={prod.image}
            prix={prod.prix}
            etat={prod.etat}
            quantite={prod.quantite}
          />
        ))}
      </div>
    </main>
  );
}
