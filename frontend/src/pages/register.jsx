import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/log.css';
import { isValidPassword } from '../utils/passwordValidator';
import API_BASE_URL from '../config'; // ✅ base URL configurable

export default function Register() {
  const [form, setForm] = useState({
    nom: '',
    prenom: '',
    email: '',
    numero_telephone: '',
    mot_de_passe: '',
    confirmation: '',
    accept: false,
  });

  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleChange = e => {
    const { name, value, type, checked } = e.target;
    setForm({ ...form, [name]: type === 'checkbox' ? checked : value });
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setError('');

    if (form.mot_de_passe !== form.confirmation) {
      setError("Les mots de passe ne sont pas identiques.");
      return;
    }

    if (!isValidPassword(form.mot_de_passe)) {
      setError("Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.");
      return;
    }

    if (!form.accept) {
      setError("Vous devez accepter les conditions générales.");
      return;
    }

    try {
      const res = await fetch(`${API_BASE_URL}/api/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      const body = await res.json();

      if (res.ok) {
        localStorage.setItem('token', body.token);
        localStorage.setItem('role', body.role || 'client');
        navigate('/');
      } else {
        setError(body.error || "Une erreur est survenue.");
      }
    } catch (err) {
      console.error("Erreur réseau :", err);
      setError("Erreur réseau, impossible de joindre le serveur.");
    }
  };

  return (
    <div className="login-wrapper">
      <form className="login-form" onSubmit={handleSubmit}>
        <h1>Inscription</h1>

        <input name="nom" placeholder="Nom" onChange={handleChange} required />
        <input name="prenom" placeholder="Prénom" onChange={handleChange} required />
        <input type="email" name="email" placeholder="Email" onChange={handleChange} required />
        <input name="numero_telephone" placeholder="Téléphone" onChange={handleChange} required />
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" onChange={handleChange} required />
        <input type="password" name="confirmation" placeholder="Confirmer le mot de passe" onChange={handleChange} required />

        <div className="register-extra">
          <label>
            <input type="checkbox" name="accept" checked={form.accept} onChange={handleChange} />
            J’accepte les <a href="/generalterm">conditions générales</a>
          </label>
        </div>

        <div className="login-link">
          Tu es déjà un habitué ? <a href="/login">Connecte-toi ici</a>
        </div>

        {error && <p className="error">{error}</p>}
        <button type="submit">S’inscrire</button>
      </form>
    </div>
  );
}
