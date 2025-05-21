import React from 'react';
import { Link } from 'react-router-dom';
import ProductCard from "../compenents/ProductCard";
import '../styles/Home.css';

const Home = () => {
  return (
    <div>
      <div className="home-header">
        <img 
          src={"../../public/RTX5090.jpg"} 
          alt="RTX 5090"
        />
        <div>
          <h2 className="home-header-text">
            Une nouvelle génération de gaming<br />avec la nouvelle RTX 5090
          </h2>
        </div>
      </div>

      <p className="home-intro">
        Bienvenu sur <strong>******</strong> un boutique entièrement dédiée à la vente de composants et d'objet électronique d'occasion
      </p>

      <div className="products-grid">
        {Array.from({ length: 14 }).map((_, index) => (
          <ProductCard key={index} />
        ))}
      </div>
    </div>
  );
};

export default Home;
