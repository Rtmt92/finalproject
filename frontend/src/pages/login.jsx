import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import API_BASE_URL from '../config'; // 🔁 Import de l'URL dynamique
import '../styles/log.css';

export default function Login() {
  const [form, setForm] = useState({ email: '', mot_de_passe: '' });
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    try {
      const res = await fetch(`${API_BASE_URL}/api/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      const body = await res.json();
      if (!res.ok) throw new Error(body.error || 'Erreur lors de la connexion');

      localStorage.setItem('token', body.token);
      localStorage.setItem('role', body.role);

      if (body.role === 'admin') navigate('/admin');
      else navigate('/home');
    } catch (err) {
      console.error('Erreur login :', err);
      setError(err.message);
    }
  };

  return (
    <div className="login-wrapper">
      <form onSubmit={handleSubmit} className="login-form">
        <h1>Connexion</h1>

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

        <div className="login-link">
          Tu n’as pas encore de compte ? <Link to="/register">inscris-toi</Link>
        </div>

        {error && <div className="login-error">{error}</div>}

        <button type="submit">Se connecter</button>
      </form>
    </div>
  );
}
