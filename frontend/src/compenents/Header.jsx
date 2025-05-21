import React from "react";
import { Search, MessageCircle, User } from "lucide-react";
import '../styles/Header.css';
import '../styles/Global.css';

const Header = () => {
  return (
        <header className="header">
        <div className="header-content">
            <div className="header-left">
            <img src="/logo.png" alt="Logo" className="header-logo" />
            <span className="header-logo-text">Justice</span>
            </div>

            <div className="header-search">
            <Search size={16} className="search-icon" />
            <input
                type="text"
                placeholder="votre recherche..."
                className="search-input"
            />
            </div>

            <div className="header-icons">
            <MessageCircle size={24} className="header-icon" />
            <User size={24} className="header-icon" />
            </div>
        </div>

        <div className="header-line" />
        </header>
    )

};

export default Header;
