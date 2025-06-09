// frontend/src/App.js
import React from "react";
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";

import Header         from "./compenents/Header";
import Footer         from "./compenents/Footer";
import Login          from "./pages/login";
import Register       from "./pages/register";
import Home           from "./pages/home";
import GeneralTerm    from "./pages/GeneralTerm";
import CreateAnnounce from "./pages/CreateAnnounce";
import UpdateAnnounce from "./pages/UpdateAnnounce";
import Pay            from "./pages/Pay";
import ProductDetail  from "./pages/ProductDetail"; // ← importe la page détail

const App = () => {
  const token = localStorage.getItem("token");

  return (
    <Router>
      <Header />

      <main className="main-content">
        <Routes>
          {/* Page d’accueil protégée */}
          <Route
            path="/"
            element={token ? <Home /> : <Navigate to="/login" replace />}
          />

          {/* Auth routes */}
          <Route path="/login"    element={<Login />} />
          <Route path="/register" element={<Register />} />

          {/* Détail produit */}
          <Route
            path="/product/:id"
            element={token ? <ProductDetail /> : <Navigate to="/login" replace />}
          />

          {/* Autres pages protégées */}
          <Route
            path="/GeneralTerm"
            element={token ? <GeneralTerm /> : <Navigate to="/login" replace />}
          />
          <Route
            path="/CreateAnnounce"
            element={token ? <CreateAnnounce /> : <Navigate to="/login" replace />}
          />
          <Route
            path="/UpdateAnnounce"
            element={token ? <UpdateAnnounce /> : <Navigate to="/login" replace />}
          />
          <Route
            path="/Pay"
            element={token ? <Pay /> : <Navigate to="/login" replace />}
          />

          {/* Fallback */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </main>

      <Footer />
    </Router>
  );
};

export default App;
