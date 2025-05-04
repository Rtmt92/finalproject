import React from 'react';
import '../styles/Footer.css'; // Import the CSS file

const Footer = () => {
    return (
      <footer className="footer">
        <div className="footer-content">
          <div className="footer-section">
            <h3>Une question ?</h3>
            <p><a href="#" className="footer-link">Nous contacter</a></p>
          </div>
          <div className="divider"></div>
          <div className="footer-section">
            <h3>Autres liens utiles</h3>
            <p><a href="#" className="footer-link">nos conditions générales</a></p>
          </div>
        </div>
        <p className="copyright">copyright © Efrei 2024</p>
      </footer>
    );
  };

export default Footer;