import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/log.css';

export default function Login() {
  const [form, setForm] = useState({ email: '', mot_de_passe: '' });
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleChange = e => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setError('');
    console.log('Tentative de connexion avec :', form);

    try {
      const res = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      console.log('Statut réponse login :', res.status);
      const body = await res.json();
      console.log('Corps réponse login :', body);

      if (!res.ok) {
        throw new Error(body.error || 'Erreur lors de la connexion');
      }

      // Stockage du token et du rôle
      localStorage.setItem('token', body.token);
      localStorage.setItem('role', body.role);

      // Redirection conditionnelle
      if (body.role === 'admin') {
        navigate('/admin');
      } else {
        navigate('/home');
      }

    } catch (err) {
      console.error('Erreur login :', err);
      setError(err.message);
    }
  };

  return (
    <div className="login-container">
      <h1>Connexion</h1>

      {error && <div className="login-error">{error}</div>}

      <form onSubmit={handleSubmit} className="form-login">
        <input
          type="email"
          name="email"
          placeholder="Email"
          value={form.email}
          onChange={handleChange}
          required
        />
        <input
          type="password"
          name="mot_de_passe"
          placeholder="Mot de passe"
          value={form.mot_de_passe}
          onChange={handleChange}
          required
        />
        <button type="submit">Se connecter</button>
      </form>
    </div>
  );
}
