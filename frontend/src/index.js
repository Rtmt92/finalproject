// src/index.js
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './App';

// Récupère l’élément racine
const container = document.getElementById('root');

// Crée le root et rend l’application dans le BrowserRouter
createRoot(container).render(
  <BrowserRouter>
    <App />
  </BrowserRouter>
);
