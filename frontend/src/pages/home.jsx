import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import ProductCard from "../compenents/ProductCard";
import '../styles/Home.css';

export default function Home() {
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (!token) { navigate('/login'); return; }
    fetch('/api/me', { headers: { Authorization: `Bearer ${token}` } })
      .then(r => { if (!r.ok) throw 0; return r.json() })
      .then(setUser)
      .catch(() => {
        localStorage.removeItem('token');
        navigate('/login');
      });
  }, [navigate]);

  return (
    <main className="home">
      {user && (
        <h1 className="home-welcome">
          Bienvenue, {user.prenom} {user.nom} !
        </h1>
      )}

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
        Bienvenu sur <strong>DejaVU</strong> une boutique entièrement dédiée à la vente de composants et d'objets électroniques d'occasion
      </p>

      <div className="products-grid">
        {Array.from({ length: 14 }).map((_, i) => (
          <ProductCard key={i} />
        ))}
      </div>
    </main>
  );
}
