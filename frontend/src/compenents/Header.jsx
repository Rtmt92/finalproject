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
} from "lucide-react";
import { useNavigate, useLocation } from "react-router-dom";
import '../styles/Header.css';
import '../styles/Global.css';

const Header = () => {
  const navigate  = useNavigate();
  const location  = useLocation();
  const role      = localStorage.getItem('role');
  const [search, setSearch] = useState("");

  const handleSearch = (e) => {
    if (e.key !== "Enter" || !search.trim()) return;
    const query = encodeURIComponent(search.trim());

    // On /admin/client → recherche de clients
    if (location.pathname.startsWith("/admin/client")) {
      navigate(`/admin/client?search=${query}`);
      return;
    }

    // Sur toute autre page sous /admin → recherche de produits
    if (location.pathname.startsWith("/admin")) {
      navigate(`/admin?search=${query}`);
      return;
    }

    // Par défaut (front) → recherche générale
    navigate(`/recherche?q=${query}`);
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    navigate('/login', { replace: true });
  };

  const handleHomeClick = () => {
    if (role === 'admin') {
      navigate('/admin');
    } else {
      navigate('/');
    }
  };

  return (
    <header className="header">
      <div className="header-content">
        <div
          className="header-left"
          onClick={handleHomeClick}
          style={{ cursor: 'pointer' }}
        >
          <img
            src="/dejaVuLogoWhite.png"
            alt="Logo"
            className="header-logo"
          />
        </div>

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

        <div className="header-icons">
          {role === 'admin' ? (
            <>
              <Gavel
                size={24}
                className="header-icon"
                title="Modération"
                onClick={() => navigate('/admin/client')}
              />
              <FolderKanban
                size={24}
                className="header-icon"
                title="Catégories"
                onClick={() => navigate('/admin/categorie')}
              />
              <PlusCircle
                size={24}
                className="header-icon"
                title="Créer une annonce"
                onClick={() => navigate('/admin/CreateAnnounce')}
              />
            </>
          ) : (
            <>
              <MessageCircle
                size={24}
                className="header-icon"
                title="Messages"
                onClick={() => navigate('/messages')}
              />
              <User
                size={24}
                className="header-icon"
                title="Profil"
                onClick={() => navigate('/profil')}
              />
            </>
          )}

          <Power
            size={24}
            onClick={handleLogout}
            className="header-icon logout-icon"
            title="Se déconnecter"
          />
        </div>
      </div>

      <div className="header-line" />
    </header>
  );
};

export default Header;
