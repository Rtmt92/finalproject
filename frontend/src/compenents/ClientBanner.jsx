import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import API_BASE_URL from "../config";
import "../styles/ClientBanner.css";

export default function ClientBanner({
  id: initialId,
  nom,
  prenom,
  description,
  photo,
  email,
  numero_telephone,
  mode = "admin",
}) {
  const navigate = useNavigate();
  const token = localStorage.getItem("token");

  const [id, setId] = useState(initialId);
  const [form, setForm] = useState({
    nom: nom || "",
    prenom: prenom || "",
    email: email || "",
    numero_telephone: numero_telephone || "",
    description: description || "",
    photo_profil: photo || "",
  });
  const [passwordForm, setPasswordForm] = useState({
    ancien: "",
    nouveau: "",
    confirmation: "",
  });
  const [loading, setLoading] = useState(false);

  // Récupérer ID utilisateur si pas passé en props
  useEffect(() => {
    if (!initialId && token) {
      fetch(`${API_BASE_URL}/api/me`, {
        headers: { Authorization: `Bearer ${token}` },
      })
        .then((res) => res.json())
        .then((data) => data?.id_client && setId(data.id_client))
        .catch(console.error);
    }
  }, [initialId, token]);

  const handleChange = (e) => {
    const { name, value, files } = e.target;
    if (name === "photo_profil" && files?.length) {
      const formData = new FormData();
      formData.append("photo", files[0]);
      fetch(`${API_BASE_URL}/upload-photo`, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((result) => {
          if (result.url) setForm((f) => ({ ...f, photo_profil: result.url }));
          else alert(result.error || "Erreur upload photo");
        })
        .catch(() => alert("Erreur réseau upload photo"));
    } else {
      setForm((f) => ({ ...f, [name]: value }));
    }
  };

  const handleSave = async () => {
    if (!id) return alert("ID utilisateur manquant");
    setLoading(true);

    try {
      const { mot_de_passe, ...userInfos } = form;

      const resInfo = await fetch(`${API_BASE_URL}/client/${id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(userInfos),
      });

      if (!resInfo.ok) {
        const err = await resInfo.json();
        if (err.error && err.error.toLowerCase().includes("email")) {
          alert("Cette adresse email est déjà utilisée.");
        } else {
          alert(err.error || "Erreur mise à jour infos");
        }
        setLoading(false);
        return;
      }

      if (
        passwordForm.ancien ||
        passwordForm.nouveau ||
        passwordForm.confirmation
      ) {
        if (passwordForm.nouveau !== passwordForm.confirmation) {
          alert("Les mots de passe ne correspondent pas.");
          setLoading(false);
          return;
        }

        const resPwd = await fetch(`${API_BASE_URL}/client/${id}/password`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify(passwordForm),
        });

        const body = await resPwd.json();
        if (!resPwd.ok) {
          alert(body.error || "Erreur changement mot de passe");
          setLoading(false);
          return;
        }
      }

      alert("Modifications enregistrées !");
      setPasswordForm({ ancien: "", nouveau: "", confirmation: "" });
      navigate(0);
    } catch {
      alert("Erreur réseau");
    } finally {
      setLoading(false);
    }
  };

  const initials = `${prenom?.charAt(0) || ""}${nom?.charAt(0) || ""}`.toUpperCase();

  if (mode === "edit") {
    return (
      <div className="client-banner">
        <div className="avatar-wrapper">
          <div className="client-avatar">
            <img
              src={form.photo_profil || "/default-avatar.png"}
              alt={`Profil de ${form.nom}`}
            />
          </div>
          <label className="upload-btn">
            Modifier la photo de profil
            <input
              type="file"
              name="photo_profil"
              accept="image/*"
              onChange={handleChange}
            />
          </label>
        </div>

        <div className="client-info">
          <label>Nom</label>
          <input type="text" name="nom" value={form.nom} onChange={handleChange} />
          <label>Prénom</label>
          <input
            type="text"
            name="prenom"
            value={form.prenom}
            onChange={handleChange}
          />
          <label>Email</label>
          <input
            type="email"
            name="email"
            value={form.email}
            onChange={handleChange}
          />
          <label>Téléphone</label>
          <input
            type="text"
            name="numero_telephone"
            value={form.numero_telephone}
            onChange={handleChange}
          />
          <label>Biographie</label>
          <textarea
            name="description"
            value={form.description}
            onChange={handleChange}
          />

          <div className="change-password">
            <h4>Changer le mot de passe</h4>
            <input
              type="password"
              placeholder="Ancien mot de passe"
              value={passwordForm.ancien}
              onChange={(e) =>
                setPasswordForm((p) => ({ ...p, ancien: e.target.value }))
              }
            />
            <input
              type="password"
              placeholder="Nouveau mot de passe"
              value={passwordForm.nouveau}
              onChange={(e) =>
                setPasswordForm((p) => ({ ...p, nouveau: e.target.value }))
              }
            />
            <input
              type="password"
              placeholder="Confirmer le mot de passe"
              value={passwordForm.confirmation}
              onChange={(e) =>
                setPasswordForm((p) => ({ ...p, confirmation: e.target.value }))
              }
            />
          </div>

          <button onClick={handleSave} disabled={loading} className="btn-valider">
            Enregistrer
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="client-banner-wrapper">
      <div className="client-banner-box">
        <div className="client-avatar">
          {form.photo_profil ? (
            <img src={form.photo_profil} alt="profil" />
          ) : (
            <span>{initials}</span>
          )}
        </div>

        <div className="client-info client-info--centered">
          <h3 className="client-name">
            Profil {prenom?.toLowerCase()} {nom?.toLowerCase()}
          </h3>

          <button
            className="edit-button"
            onClick={() => navigate(`/admin/client/edit/${id}`)}
            title="Modifier"
          >
            Modifier
          </button>

          <div className="bio-block">
            <p className="client-subtitle">Biographie</p>
            <p className="client-description">
              {description?.trim() || "Aucune biographie fournie."}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
