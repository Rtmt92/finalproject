import { Link } from "react-router-dom";
import React from 'react';

const Register = () => {
  return (
    <div className="container">
      <h1>Inscription</h1>
      <input type="text" placeholder="Nom" />
      <input type="text" placeholder="Prénom" />
      <input type="email" placeholder="Email" />
      <input type="password" placeholder="Mot de passe" />
      <input type="password" placeholder="Mot de passe" />
        <Link to="/">
            <a>Tu es déjà un habitué ? Connecte toi ici</a>
        </Link>
      <button>Valider</button>
    </div>
  );
};

export default Register;
