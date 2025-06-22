import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import '../styles/ProductDetail.css';

export default function ProductDetail() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [prod, setProd] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [selectedIndex, setSelectedIndex] = useState(0);

  useEffect(() => {
    fetch(`/api/produit/${id}`)
      .then(res => {
        if (!res.ok) throw new Error(`Status ${res.status}`);
        return res.json();
      })
      .then(data => {
        setProd(data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Erreur fetch produit:', err);
        setError('Impossible de charger le produit');
        setLoading(false);
      });
  }, [id]);

    const ajouterAuPanier = async (idProduit) => {
    const token = localStorage.getItem("token");
    if (!token) {
        alert("Veuillez vous connecter pour ajouter un produit au panier.");
        navigate("/login");
        return;
    }

    try {
        const response = await fetch('http://localhost:3000/panier_produit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}` // ✅ Ajout du token ici
        },
        body: JSON.stringify({ id_produit: idProduit }),
        });

        const data = await response.json();
        if (response.ok) {
        if (data.message === 'Produit ajouté au panier') {
            navigate('/profil');
        } else if (data.message === 'Produit déjà dans le panier') {
            alert(data.message);
        }
        } else {
        alert(data.error || 'Une erreur est survenue.');
        }
    } catch (error) {
        console.error('Erreur lors de la requête :', error);
        alert('Erreur de communication avec le serveur.');
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
        <p className="etat">État : {prod.etat || 'N/A'}</p>
        <p className="quantite">Quantité : {prod.quantite ?? 'N/A'}</p>
        <p className="price">Prix : {prod.prix}€</p>
      </div>

      <div className="add-cart-wrapper">
        <button className="add-cart-btn" onClick={() => ajouterAuPanier(prod.id_produit)}>
          Ajouter au panier
        </button>
      </div>
    </div>
  );
}
