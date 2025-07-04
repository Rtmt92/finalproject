// frontend/src/components/Header.jsx
import React, { useState } from 'react';
import {
  Search,
  MessageCircle,
  User,
  Power,
  Gavel,
  PlusCircle,
  FolderKanban,
  LogIn,
  ShoppingCart
} from "lucide-react";
import { useNavigate, useLocation } from "react-router-dom";
import '../styles/Header.css';
import '../styles/Global.css';

export default function Header() {
  const navigate  = useNavigate();
  const location  = useLocation();
  const token     = localStorage.getItem('token');
  const role      = localStorage.getItem('role');
  const [search, setSearch] = useState("");

  const handleSearch = e => {
    if (e.key !== "Enter" || !search.trim()) return;
    const q = encodeURIComponent(search.trim());
    if (location.pathname.startsWith("/admin/client")) {
      navigate(`/admin/client?search=${q}`);
    } else if (location.pathname.startsWith("/admin")) {
      navigate(`/admin?search=${q}`);
    } else {
      navigate(`/recherche?q=${q}`);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    navigate('/login');
  };

  const handleLogin = () => {
    navigate('/login');
  };

  const handleCart = () => {
    navigate('/panier');
  };

  const handleHomeClick = () => {
    if (role === 'admin')      navigate('/admin');
    else                        navigate('/');
  };

  return (
    <header className="header">
      <div className="header-content">
        {/* Logo / Accueil */}
        <div
          className="header-left"
          onClick={handleHomeClick}
          style={{ cursor: 'pointer' }}
        >
          <img src="/dejaVuLogoWhite.png" alt="Logo" className="header-logo" />
        </div>

        {/* Barre de recherche */}
        <div className="header-search">
          <Search size={16} className="search-icon" />
          <input
            type="text"
            placeholder="votre recherche..."
            className="search-input"
            value={search}
            onChange={e => setSearch(e.target.value)}
            onKeyDown={handleSearch}
          />
        </div>

        {/* Icônes de droite */}
        <div className="header-icons">
          {role === 'admin' ? (
            <>
              <Gavel size={24} className="header-icon" title="Modération" onClick={() => navigate('/admin/client')} />
              <FolderKanban size={24} className="header-icon" title="Catégories" onClick={() => navigate('/admin/categorie')} />
              <PlusCircle size={24} className="header-icon" title="Créer une annonce" onClick={() => navigate('/admin/CreateAnnounce')} />
            </>
          ) : token ? (
            <>
              <MessageCircle size={24} className="header-icon" title="Messages" onClick={() => navigate('/messages')} />
              <User size={24} className="header-icon" title="Profil" onClick={() => navigate('/profil')} />
            </>
          ) : (
            <>
              {/* Visiteur : accès au panier et à la connexion */}
              <ShoppingCart
                size={24}
                className="header-icon"
                title="Voir mon panier"
                onClick={handleCart}
              />
            </>
          )}

          {/* Bouton connexion / déconnexion */}
          {token ? (
            <Power
              size={24}
              onClick={handleLogout}
              className="header-icon logout-icon"
              title="Se déconnecter"
            />
          ) : (
            <LogIn
              size={24}
              onClick={handleLogin}
              className="header-icon login-icon"
              title="Se connecter"
            />
          )}
        </div>
      </div>
      <div className="header-line" />
    </header>
  );
}
