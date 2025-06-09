import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/log.css';

export default function Register() {
  const [form, setForm] = useState({
    nom: '',
    prenom: '',
    email: '',
    numero_telephone: '',
    mot_de_passe: '',
  });
  const navigate = useNavigate();

  const handleChange = e =>
    setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async e => {
    e.preventDefault();

    // ← ICI : on appelle votre API PHP
    const res = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form),
    });

    const body = await res.json();
    if (res.ok) {
      localStorage.setItem('token', body.token);
      navigate('/'); // redirige vers Home
    } else {
      alert(body.error);
    }
  };

  return (
    <div className="register-container">
      <h1>Inscription</h1>
      <form onSubmit={handleSubmit} className="form-register">
        <input name="nom" placeholder="Nom" onChange={handleChange} required />
        <input name="prenom" placeholder="Prénom" onChange={handleChange} required />
        <input
          type="email"
          name="email"
          placeholder="Email"
          onChange={handleChange}
          required
        />
        <input
          name="numero_telephone"
          placeholder="Téléphone"
          onChange={handleChange}
          required
        />
        <input
          type="password"
          name="mot_de_passe"
          placeholder="Mot de passe"
          onChange={handleChange}
          required
        />
        <button type="submit">S’inscrire</button>
      </form>
    </div>
  );
}
