// frontend/src/pages/Home.jsx
import React, { useEffect, useState } from 'react';
import { useNavigate }                from 'react-router-dom';
import ProductCard                    from '../compenents/ProductCard';
import '../styles/Home.css';

export default function Home() {
  const [user, setUser]         = useState(null);
  const [products, setProducts] = useState([]);
  const [loading, setLoading]   = useState(true);
  const [error, setError]       = useState(null);
  const navigate                = useNavigate();

  // Charger l'utilisateur si connecté
  useEffect(() => {
    const token = localStorage.getItem('token');
    if (!token) return setUser(null);
    fetch('http://localhost:8000/api/me', {
      headers: { Authorization: `Bearer ${token}` }
    })
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(setUser)
      .catch(() => {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        setUser(null);
      });
  }, []);

  // Charger les produits (tous, puis ne garder que 8 pour l'aperçu)
  useEffect(() => {
    setLoading(true);
    setError(null);
    fetch('http://localhost:8000/api/produit')
      .then(r => {
        if (!r.ok) throw new Error(`Erreur ${r.status}`);
        return r.json();
      })
      .then(data => {
        setProducts(data);
      })
      .catch(err => {
        console.error(err);
        setError('Impossible de charger les produits.');
      })
      .finally(() => setLoading(false));
  }, []);

  // Aperçu : les 8 premiers
  const preview = products.slice(0, 8);

  return (
    <main className="home">
      <div className="home-welcome">
        {user
          ? <h1>Bienvenue, {user.prenom} {user.nom} !</h1>
          : <h1>Bienvenue sur <strong>DéjàVU</strong> !</h1>
        }
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
            Une nouvelle génération de gaming<br/>
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
                  key={prod.id}
                  id={prod.id}
                  titre={prod.titre}
                  description={prod.description}
                  prix={prod.prix}
                  image={prod.image}
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
