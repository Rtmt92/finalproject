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
  ShoppingCart,
  Menu,
  X
} from "lucide-react";
import { useNavigate, useLocation } from "react-router-dom";
import '../styles/Header.css';
import '../styles/Global.css';

export default function Header() {
  const navigate = useNavigate();
  const location = useLocation();
  const token = localStorage.getItem('token');
  const role = localStorage.getItem('role');
  const [search, setSearch] = useState("");
  const [isMenuOpen, setIsMenuOpen] = useState(false);

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

  const handleLogin = () => navigate('/login');
  const handleCart = () => navigate('/panier');
  const handleHomeClick = () => {
    if (role === 'admin') navigate('/admin');
    else navigate('/');
  };

  const renderIcons = () => {
    if (role === 'admin') {
      return (
        <>
          <Gavel size={24} className="header-icon" title="Modération" onClick={() => navigate('/admin/client')} />
          <FolderKanban size={24} className="header-icon" title="Catégories" onClick={() => navigate('/admin/categorie')} />
          <PlusCircle size={24} className="header-icon" title="Créer une annonce" onClick={() => navigate('/admin/CreateAnnounce')} />
        </>
      );
    } else if (token) {
      return (
        <>
          <MessageCircle size={24} className="header-icon" title="Messages" onClick={() => navigate('/messages')} />
          <User size={24} className="header-icon" title="Profil" onClick={() => navigate('/profil')} />
        </>
      );
    } else {
      return (
        <ShoppingCart size={24} className="header-icon" title="Voir mon panier" onClick={handleCart} />
      );
    }
  };

  return (
    <header className="header">
      <div className="header-content">
        <div className="header-left" onClick={handleHomeClick} style={{ cursor: 'pointer' }}>
          <img src="/dejaVuLogoWhite.png" alt="Logo" className="header-logo" />
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
          <div className="burger-toggle" onClick={() => setIsMenuOpen(true)}>
            <Menu size={24} />
          </div>

          {/* Desktop icons */}
          <div className="desktop-icons">
            {renderIcons()}
            {token ? (
              <Power size={24} onClick={handleLogout} className="header-icon logout-icon" title="Se déconnecter" />
            ) : (
              <LogIn size={24} onClick={handleLogin} className="header-icon login-icon" title="Se connecter" />
            )}
          </div>
        </div>
      </div>

      {isMenuOpen && (
        <div className="burger-menu-overlay">
          <div className="burger-menu-header">
            <X size={28} onClick={() => setIsMenuOpen(false)} />
          </div>
            <div className="burger-menu-content">
            {role === 'admin' ? (
              <>
                <div className="burger-link" onClick={() => navigate('/admin/client')}>
                  <Gavel size={24} /> <span>Modération</span>
                </div>
                <div className="burger-link" onClick={() => navigate('/admin/categorie')}>
                  <FolderKanban size={24} /> <span>Catégories</span>
                </div>
                <div className="burger-link" onClick={() => navigate('/admin/CreateAnnounce')}>
                  <PlusCircle size={24} /> <span>Créer annonce</span>
                </div>
              </>
            ) : token ? (
              <>
                <div className="burger-link" onClick={() => navigate('/messages')}>
                  <MessageCircle size={24} /> <span>Messages</span>
                </div>
                <div className="burger-link" onClick={() => navigate('/profil')}>
                  <User size={24} /> <span>Profil</span>
                </div>
              </>
            ) : (
              <>
                <div className="burger-link" onClick={handleCart}>
                  <ShoppingCart size={24} /> <span>Panier</span>
                </div>
              </>
            )}

            {token ? (
              <div className="burger-link" onClick={handleLogout}>
                <Power size={24} className="logout-icon" /> <span>Se déconnecter</span>
              </div>
            ) : (
              <div className="burger-link" onClick={handleLogin}>
                <LogIn size={24} /> <span>Se connecter</span>
              </div>
            )}
          </div>

        </div>
      )}

      <div className="header-line" />
    </header>
  );
}
