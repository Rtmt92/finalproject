import React from "react";
import { Search, MessageCircle, User, Power, Gavel, LayoutDashboard, ClipboardCheck } from "lucide-react"; // exemple pour les icônes admin
import { useNavigate } from "react-router-dom";
import '../styles/Header.css';
import '../styles/Global.css';

const Header = () => {
  const navigate = useNavigate();
  const role = localStorage.getItem('role');

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
        {/* Logo cliquable vers page d'accueil spécifique */}
        <div className="header-left" onClick={handleHomeClick} style={{ cursor: 'pointer' }}>
          <img src="/dejaVuLogoWhite.png" alt="Logo" className="header-logo" />
        </div>

        {/* Barre de recherche */}
        <div className="header-search">
          <Search size={16} className="search-icon" />
          <input
            type="text"
            placeholder="votre recherche..."
            className="search-input"
          />
        </div>

        {/* Icônes selon rôle */}
        <div className="header-icons">
          {role === 'admin' ? (
            <>
              <Gavel size={24} className="header-icon" title="Modération" />
              <LayoutDashboard size={24} className="header-icon" title="Dashboard" />
              <ClipboardCheck size={24} className="header-icon" title="Contrats" />
            </>
          ) : (
            <>
              <MessageCircle size={24} className="header-icon" title="Messages" />
              <User size={24} className="header-icon" title="Profil" />
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
