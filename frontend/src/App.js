// frontend/src/App.js
import React from "react";
import { Routes, Route, Navigate, useLocation } from "react-router-dom";

import Header        from "./compenents/Header";
import Footer        from "./compenents/Footer";
import RequireAdmin  from "./compenents/RequireAdmin";
import RequireAuth   from "./compenents/RequireAuth";  // à créer si tu ne l'as pas

import Login         from "./pages/login";
import Register      from "./pages/register";
import Home          from "./pages/home";
import GeneralTerm   from "./pages/GeneralTerm";
import ProductDetail from "./pages/ProductDetail";
import SearchResults from "./pages/SearchResults";
import Profil        from "./pages/Profil";
import Pay           from "./pages/Pay";

import AdminHome      from "./pages/AdminHome";
import CreateAnnounce from "./pages/CreateAnnounce";
import EditProduct    from "./pages/EditProduct";
import AdminClient    from "./pages/AdminClient";
import EditClient     from "./pages/EditClient";
import AdminCategorie from "./pages/AdminCategorie";
import Panier from "./pages/Panier";
import AllProducts from "./pages/AllProducts";

function AppRoutes() {
  const token = localStorage.getItem("token");
  const role  = localStorage.getItem("role");

  return (
    <Routes>
      {/* ─── Routes publiques ─── */}
      <Route path="/"            element={<Home />} />
      <Route path="/login"       element={<Login />} />
      <Route path="/register"    element={<Register />} />
      <Route path="/product/:id" element={<ProductDetail />} />
      <Route path="/recherche"   element={<SearchResults />} />
      <Route path="/cgu"         element={<GeneralTerm />} />
      <Route path="/panier"         element={<Panier />} />
      <Route path="/allproducts"     element={<AllProducts />} />


      {/* ─── Routes client (nécessite d'être loggé) ─── */}
      <Route
        path="/profil"
        element={
          <RequireAuth>
            <Profil />
          </RequireAuth>
        }
      />
      <Route
        path="/pay"
        element={
          <RequireAuth>
            <Pay />
          </RequireAuth>
        }
      />

      {/* ─── Routes admin ─── */}
      <Route
        path="/admin"
        element={
          <RequireAdmin>
            <AdminHome />
          </RequireAdmin>
        }
      />
      <Route
        path="/admin/CreateAnnounce"
        element={
          <RequireAdmin>
            <CreateAnnounce />
          </RequireAdmin>
        }
      />
      <Route
        path="/admin/edit-product/:id"
        element={
          <RequireAdmin>
            <EditProduct />
          </RequireAdmin>
        }
      />
      <Route
        path="/admin/client"
        element={
          <RequireAdmin>
            <AdminClient />
          </RequireAdmin>
        }
      />
      <Route
        path="/admin/client/edit/:id"
        element={
          <RequireAdmin>
            <EditClient />
          </RequireAdmin>
        }
      />
      <Route
        path="/admin/categorie"
        element={
          <RequireAdmin>
            <AdminCategorie />
          </RequireAdmin>
        }
      />

      {/* ─── Fallback ─── */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}

export default function App() {

  return (
    <>
      <Header />
      <main className="main-content">
        <AppRoutes />
      </main>
      <Footer />
    </>
  );
}
