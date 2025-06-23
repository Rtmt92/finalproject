// frontend/src/App.js
import React from "react";
import { BrowserRouter as Router, Routes, Route, Navigate, useLocation } from "react-router-dom";

import Header from "./compenents/Header";
import Footer from "./compenents/Footer";
import Login from "./pages/login";
import Register from "./pages/register";
import Home from "./pages/home";
import GeneralTerm from "./pages/GeneralTerm";
import CreateAnnounce from "./pages/CreateAnnounce";
import Pay from "./pages/Pay";
import ProductDetail from "./pages/ProductDetail";
import AdminHome from "./pages/AdminHome";
import RequireAdmin from "./compenents/RequireAdmin";
import EditProduct from "./pages/EditProduct";
import AdminClient from "./pages/AdminClient";
import EditClient from "./pages/EditClient";
import AdminCategorie from "./pages/AdminCategorie";
import Profil from "./pages/Profil";
import SearchResults from "./pages/SearchResults";

const AppRoutes = () => {
  const token = localStorage.getItem("token");
  const role = localStorage.getItem("role");
  const location = useLocation();

  if (token && role === "admin" && !location.pathname.startsWith("/admin")) {
    return <Navigate to="/admin" replace />;
  }

  return (
    <Routes>
      <Route path="/" element={token ? <Home /> : <Navigate to="/login" replace />} />
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />
      <Route path="/product/:id" element={token ? <ProductDetail /> : <Navigate to="/login" replace />} />
      <Route path="/GeneralTerm" element={token ? <GeneralTerm /> : <Navigate to="/login" replace />} />
      <Route path="/profil" element={token ? <Profil /> : <Navigate to="/login" replace />} />
      <Route path="/Pay" element={token ? <Pay /> : <Navigate to="/login" replace />} />
      <Route path="/recherche" element={<SearchResults />} />

      {/* Routes Admin */}
      <Route path="/admin" element={<RequireAdmin><AdminHome /></RequireAdmin>} />
      <Route path="/admin/CreateAnnounce" element={<RequireAdmin><CreateAnnounce /></RequireAdmin>} />
      <Route path="/admin/edit-product/:id" element={<RequireAdmin><EditProduct /></RequireAdmin>} />
      <Route path="/admin/client" element={<RequireAdmin><AdminClient /></RequireAdmin>} />
      <Route path="/admin/client/edit/:id" element={<RequireAdmin><EditClient /></RequireAdmin>} />
      <Route path="/admin/categorie" element={<RequireAdmin><AdminCategorie /></RequireAdmin>} />

      {/* Fallback */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
};

const AppWrapper = () => {
  const location = useLocation();
  const hideHeader = ["/login", "/register"].includes(location.pathname);

  return (
    <>
      {!hideHeader && <Header />}
      <main className="main-content">
        <AppRoutes />
      </main>
      <Footer />
    </>
  );
};

const App = () => (
  <Router>
    <AppWrapper />
  </Router>
);

export default App;
