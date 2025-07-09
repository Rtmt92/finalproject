import React from 'react';
import '../styles/Contact.css';

const Contact = () => {
  return (
    <div className="contact-container">
      <h2 className="contact-title">
        Vous voulez nous vendre un composant ?
      </h2>

      <p className="contact-intro">
        Vous souhaitez nous vendre un composant ? pas de soucis ! il suffit seulement de
        se rendre dans une de nos boutiques partenaires pour que nos experts puissent
        tester, vérifier et estimer votre bien.
      </p>

      <p className="contact-intro">
        Voici notre adresse et nos coordonnées:
      </p>

      <div className="contact-main">
        <div className="contact-info">
          <p><strong>Campus de Villejuif</strong></p>
          <p>Adresse : 30-32 avenue de la République, 94 800 Villejuif</p>
          <p>
            Vous pouvez y prendre des correspondances avec les lignes de bus 162, 172, 180, 185, 286, 380 et V7;
            les lignes Noctilien N15 et N22 et la ligne de tramway T7 (Source Bonjour RATP)
          </p>
        </div>

        <div className="contact-map">
          <iframe
            title="Carte Villejuif"
            src="https://maps.google.com/maps?q=30-32%20avenue%20de%20la%20République,%2094800%20Villejuif&t=&z=13&ie=UTF8&iwloc=&output=embed"
            width="100%"
            height="300"
            frameBorder="0"
            allowFullScreen=""
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
          ></iframe>
        </div>
      </div>
    </div>
  );
};

export default Contact;
