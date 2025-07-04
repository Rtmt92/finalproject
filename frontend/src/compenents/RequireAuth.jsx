// frontend/src/components/RequireAuth.jsx
import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';

export default function RequireAuth({ children }) {
  const token = localStorage.getItem('token');
  const loc   = useLocation();

  if (!token) {
    // Sauvegarde la page visée pour y revenir ensuite si tu veux
    return <Navigate to="/login" state={{ from: loc }} replace />;
  }
  return children;
}
