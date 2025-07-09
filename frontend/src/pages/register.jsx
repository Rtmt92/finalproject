import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { Eye, EyeOff } from 'lucide-react';
import API_BASE_URL from '../config';
import '../styles/log.css';
import { isValidPassword } from '../utils/passwordValidator';

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
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmation, setShowConfirmation] = useState(false);
  const navigate = useNavigate();

const handleChange = e => {
  const { name, value, type, checked } = e.target;

  if (name === 'numero_telephone') {
    const digitsOnly = value.replace(/\D/g, ''); 
    setForm(prev => ({ ...prev, [name]: digitsOnly }));
  } else {
    setForm(prev => ({ ...prev, [name]: type === 'checkbox' ? checked : value }));
  }
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

      if (!res.ok) {
        setError(body.error || "Une erreur est survenue.");
        return;
      }

      localStorage.setItem('token', body.token);
      localStorage.setItem('role', body.role || 'client');
      navigate('/');
    } catch {
      setError("Erreur réseau, impossible de joindre le serveur.");
    }
  };

  return (
    <div className="login-wrapper">
      <form onSubmit={handleSubmit} className="login-form">
        <h1>Inscription</h1>

        <input name="nom" placeholder="Nom" onChange={handleChange} required />
        <input name="prenom" placeholder="Prénom" onChange={handleChange} required />
        <input type="email" name="email" placeholder="Email" onChange={handleChange} required />
        <input name="numero_telephone" placeholder="Téléphone" onChange={handleChange} required />

        <div className="password-input-wrapper">
          <input
            type={showPassword ? 'text' : 'password'}
            name="mot_de_passe"
            placeholder="Mot de passe"
            value={form.mot_de_passe}
            onChange={handleChange}
            required
          />
          <button
            type="button"
            className="password-toggle-button"
            onClick={() => setShowPassword(prev => !prev)}
            aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
          >
            {showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
          </button>
        </div>

        <div className="password-input-wrapper">
          <input
            type={showConfirmation ? 'text' : 'password'}
            name="confirmation"
            placeholder="Confirmer le mot de passe"
            value={form.confirmation}
            onChange={handleChange}
            required
          />
          <button
            type="button"
            className="password-toggle-button"
            onClick={() => setShowConfirmation(prev => !prev)}
            aria-label={showConfirmation ? 'Masquer la confirmation' : 'Afficher la confirmation'}
          >
            {showConfirmation ? <EyeOff size={20} /> : <Eye size={20} />}
          </button>
        </div>

        <div className="register-extra">
          <label>
            <input type="checkbox" name="accept" checked={form.accept} onChange={handleChange} />
            J’accepte les <Link to="/generalterm">conditions générales</Link>
          </label>
        </div>

        <div className="login-link">
          Tu es déjà un habitué ? <Link to="/login">Connecte-toi ici</Link>
        </div>

        {error && <p className="error">{error}</p>}

        <button type="submit" className="btn-main">S’inscrire</button>
      </form>
    </div>
  );
}
