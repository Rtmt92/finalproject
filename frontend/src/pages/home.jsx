import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ProductCard from '../compenents/ProductCard';
import API_BASE_URL from '../config';
import '../styles/Home.css';

export default function Home() {
  const [user, setUser] = useState(null);
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchUser = async () => {
      const token = localStorage.getItem('token');
      if (!token) return setUser(null);
      try {
        const res = await fetch(`${API_BASE_URL}/api/me`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!res.ok) throw new Error('Utilisateur non authentifié');
        const data = await res.json();
        setUser(data);
      } catch {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        setUser(null);
      }
    };
    fetchUser();
  }, []);

  useEffect(() => {
    const fetchProducts = async () => {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch(`${API_BASE_URL}/api/produit`);
        if (!res.ok) throw new Error(`Erreur ${res.status}`);
        const data = await res.json();
        setProducts(data);
      } catch {
        setError("Impossible de charger les produits.");
      } finally {
        setLoading(false);
      }
    };
    fetchProducts();
  }, []);

  const preview = products.slice(0, 8);

  return (
    <main className="home">
      <div className="home-welcome">
        {user ? (
          <h1>Bienvenue, {user.prenom} {user.nom} !</h1>
        ) : (
          <h1>Bienvenue sur <strong>DéjàVU</strong> !</h1>
        )}
      </div>

      <div className="home-hero">
        <div className="home-hero-left">
          <img
            src="/RTX5090.jpg"
            alt="RTX 5090"
            className="home-hero-img"
          />
        </div>
        <div className="home-hero-right">
          <h2 className="home-hero-text">
            Une nouvelle génération de gaming<br />
            avec la nouvelle RTX 5090
          </h2>
        </div>
      </div>

      <p className="home-intro">
        Découvrez nos composants et objets électroniques d’occasion.
      </p>

      {error && <p className="error">{error}</p>}
      {loading && <p>Chargement…</p>}

      {!loading && (
        <>
          <div className="products-grid">
            {preview.length > 0 ? (
              preview.map(prod => (
                <ProductCard
                  id={prod.id}
                  titre={prod.titre}
                  image={prod.image}
                  prix={prod.prix}
                  etat={prod.etat}
                />
              ))
            ) : (
              <p>Aucun produit disponible.</p>
            )}
          </div>

          <div className="voir-plus-wrapper">
            <button
              className="voir-plus-btn"
              onClick={() => navigate('/allproducts')}
            >
              Voir plus
            </button>
          </div>
        </>
      )}
    </main>
  );
}
