import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import '../styles/ProductDetail.css';

export default function ProductDetail() {
  const { id } = useParams();
  const [prod, setProd] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [selectedIndex, setSelectedIndex] = useState(0);

  // Charger les infos du produit à l'affichage
  useEffect(() => {
    fetch(`http://localhost:3000/api/produit/${id}`)
      .then(res => {
        if (!res.ok) throw new Error(`Erreur HTTP: ${res.status}`);
        return res.json();
      })
      .then(data => {
        setProd(data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Erreur fetch produit :', err);
        setError("Impossible de charger le produit.");
        setLoading(false);
      });
  }, [id]);

  // Ajouter au panier
  const ajouterAuPanier = async (idProduit) => {
    try {
      const response = await fetch('http://localhost:3000/panier_produit', {
        method: 'POST',
        credentials: 'include', // Inclut les cookies/session
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_produit: idProduit })
      });

      const data = await response.json();

      if (response.ok) {
        alert(data.message || 'Produit ajouté au panier !');
      } else {
        alert(data.error || 'Erreur lors de l’ajout au panier.');
      }
    } catch (err) {
      console.error('Erreur réseau :', err);
      alert("Erreur de communication avec le serveur.");
    }
  };

  if (loading) return <div className="detail-container">Chargement…</div>;
  if (error) return <div className="detail-container">{error}</div>;
  if (!prod) return null;

  const images = prod.images || [];
  const mainImg = images[selectedIndex]?.lien || '';

  return (
    <div className="detail-container">
      <div className="detail-main">
        <div className="thumbnail-list">
          {images.map((img, idx) => (
            <img
              key={idx}
              src={img.lien}
              alt={`${prod.nom_produit} ${idx}`}
              className={idx === selectedIndex ? 'selected' : ''}
              onClick={() => setSelectedIndex(idx)}
            />
          ))}
        </div>

        <div className="main-image">
          {mainImg ? (
            <img src={mainImg} alt={prod.nom_produit} />
          ) : (
            <div className="image-placeholder" />
          )}
        </div>
      </div>

      <div className="info-panel">
        <p className="name">{prod.nom_produit}</p>
        <p className="description">{prod.description}</p>
        <p className="price">{prod.prix}€</p>
      </div>

      <div className="add-cart-wrapper">
        <button
          className="add-cart-btn"
          onClick={() => ajouterAuPanier(prod.id_produit)}
        >
          Ajouter au panier
        </button>
      </div>
    </div>
  );
}
