// frontend/src/pages/ProductDetail.jsx
import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import API_BASE_URL from '../config'; // ✅ URL API centralisée
import '../styles/ProductDetail.css';

export default function ProductDetail() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [prod, setProd] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [thumbIndex, setThumbIndex] = useState(0);

  // Charger les données du produit
  useEffect(() => {
    fetch(`${API_BASE_URL}/api/produit/${id}`)
      .then(res => {
        if (!res.ok) throw new Error(`Statut ${res.status}`);
        return res.json();
      })
      .then(data => {
        setProd(data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Erreur fetch produit :', err);
        setError('Impossible de charger le produit');
        setLoading(false);
      });
  }, [id]);

  // Ajouter au panier
  const ajouterAuPanier = async () => {
    const token = localStorage.getItem('token');

    if (token) {
      try {
        const res = await fetch(`${API_BASE_URL}/panier_produit`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
          body: JSON.stringify({ id_produit: Number(id) }),
        });

        if (res.status === 204) {
          alert('Produit ajouté au panier');
          navigate('/profil');
          return;
        }

        const payload = await res.json().catch(() => ({}));

        if (res.ok) {
          alert(payload.message || 'Produit ajouté au panier');
          navigate('/profil');
        } else {
          alert(payload.error || 'Erreur lors de l’ajout au panier');
        }

      } catch (e) {
        console.error('Erreur requête panier :', e);
        alert('Erreur de communication avec le serveur');
      }

    } else {
      // Visiteur non connecté
      const guestCart = JSON.parse(localStorage.getItem('guestCart') || '[]').map(Number);

      if (guestCart.includes(Number(id))) {
        alert('Ce produit est déjà dans votre panier temporaire.');
      } else {
        guestCart.push(Number(id));
        localStorage.setItem('guestCart', JSON.stringify(guestCart));
        alert('Produit ajouté à votre panier temporaire.');
      }

      navigate('/panier');
    }
  };

  if (loading) return <div className="detail-container">Chargement…</div>;
  if (error) return <div className="detail-container error">{error}</div>;
  if (!prod) return null;

  const images = prod.images || [];
  const mainUrl = images[thumbIndex]?.lien
    ? `${API_BASE_URL}/${images[thumbIndex].lien}`
    : null;

  return (
    <div className="detail-container">
      <div className="detail-main">
        <div className="thumbnail-list">
          {images.map((img, idx) => (
            <img
              key={idx}
              src={`${API_BASE_URL}/${img.lien}`}
              alt={`${prod.nom_produit} ${idx}`}
              className={idx === thumbIndex ? 'selected' : ''}
              onClick={() => setThumbIndex(idx)}
            />
          ))}
        </div>

        <div className="main-image">
          {mainUrl
            ? <img src={mainUrl} alt={prod.nom_produit} />
            : <div className="image-placeholder" />
          }
        </div>
      </div>

      <div className="info-panel">
        <h2 className="name">{prod.nom_produit}</h2>
        <p className="description">{prod.description}</p>
        <p className="etat"><strong>État :</strong> {prod.etat || 'N/A'}</p>
        <p className="quantite"><strong>Quantité :</strong> {prod.quantite ?? 'N/A'}</p>
        <p className="price"><strong>Prix :</strong> {prod.prix} €</p>
      </div>

      <div className="add-cart-wrapper">
        <button className="add-cart-btn" onClick={ajouterAuPanier}>
          Ajouter au panier
        </button>
      </div>
    </div>
  );
}
