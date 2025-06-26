import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/ClientBanner.css";

const ClientBanner = ({
  id: idFromProps,
  nom,
  prenom,
  description,
  photo,
  email,
  numero_telephone,
  mode = "admin",
}) => {
  const navigate = useNavigate();
  const [id, setId] = useState(idFromProps);
  const [loading, setLoading] = useState(false);
  const token = localStorage.getItem("token");

  const [form, setForm] = useState({
    nom: nom || "",
    prenom: prenom || "",
    email: email || "",
    numero_telephone: numero_telephone || "",
    description: description || "",
    mot_de_passe: "",
    photo_profil: photo || "",
  });

  const [passwordForm, setPasswordForm] = useState({
    ancien: '',
    nouveau: '',
    confirmation: ''
  });
  const [messagePwd, setMessagePwd] = useState('');

  useEffect(() => {
    if (!idFromProps) {
      fetch("http://localhost:3000/api/me", { credentials: "include" })
        .then((res) => res.json())
        .then((data) => {
          if (data?.id_client) setId(data.id_client);
        })
        .catch((err) => console.error("Erreur /api/me", err));
    }
  }, [idFromProps]);

  const handleChange = async (e) => {
    const { name, value, files } = e.target;

    if (name === "photo_profil" && files?.length > 0) {
      const formData = new FormData();
      formData.append("photo", files[0]);

      try {
        const res = await fetch("http://localhost:3000/upload-photo", {
          method: "POST",
          body: formData,
        });
        const result = await res.json();
        if (res.ok && result.url) {
          setForm((prev) => ({ ...prev, photo_profil: result.url }));
        } else {
          alert(result.error || "Échec de l'upload");
        }
      } catch {
        alert("Erreur réseau lors de l'upload");
      }
    } else {
      setForm((prev) => ({ ...prev, [name]: value }));
    }
  };

    const handleSave = async () => {
  if (!id) return alert("ID utilisateur manquant");
  setLoading(true);

  try {
    // ⚙️ 1. Mise à jour des infos de profil
    const resInfo = await fetch(`http://localhost:3000/client/${id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify(form),
    });

    if (!resInfo.ok) {
      const err = await resInfo.json();
      alert(err.error || "Erreur lors de la mise à jour des infos");
      setLoading(false);
      return;
    }

    // 🔐 2. Mise à jour du mot de passe SI les champs sont remplis
    if (passwordForm.ancien || passwordForm.nouveau || passwordForm.confirmation) {
      if (passwordForm.nouveau !== passwordForm.confirmation) {
        alert("Les mots de passe ne correspondent pas.");
        setLoading(false);
        return;
      }

      const resPwd = await fetch(`http://localhost:3000/client/${id}/password`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(passwordForm),
      });

      const body = await resPwd.json();
      if (!resPwd.ok) {
        alert(body.error || "Erreur lors du changement de mot de passe");
        setLoading(false);
        return;
      }
    }

    alert("Modifications enregistrées !");
    setPasswordForm({ ancien: '', nouveau: '', confirmation: '' });
    navigate(0);

  } catch (err) {
    alert("Erreur réseau");
  } finally {
    setLoading(false);
  }
};



  const handleChangePassword = async (e) => {
    e.preventDefault();
    setMessagePwd('');

    if (passwordForm.nouveau !== passwordForm.confirmation) {
      return setMessagePwd("Les mots de passe ne correspondent pas.");
    }

    const res = await fetch(`http://localhost:3000/client/${id}/password`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(passwordForm)
    });

    const body = await res.json();
    if (res.ok) {
      setMessagePwd("Mot de passe mis à jour.");
      setPasswordForm({ ancien: '', nouveau: '', confirmation: '' });
    } else {
      setMessagePwd(body.error || 'Erreur lors du changement de mot de passe');
    }
  };

  const defaultAvatar = "frontend/public/default-avatar.png";

  return (
    <div className="client-banner">
      <div className="client-info">
        <img
          src={form.photo_profil || defaultAvatar}
          alt={`client-${form.nom || "photo"}`}
          className="client-avatar"
        />
        {mode === "edit" && (
          <input
            type="file"
            name="photo_profil"
            accept="image/*"
            onChange={handleChange}
          />
        )}

        <div className="client-text">
          {mode === "edit" ? (
            <>
              <label>Nom</label>
              <input type="text" name="nom" value={form.nom} onChange={handleChange} />
              <label>Prénom</label>
              <input type="text" name="prenom" value={form.prenom} onChange={handleChange} />
              <label>Email</label>
              <input type="email" name="email" value={form.email} onChange={handleChange} />
              <label>Téléphone</label>
              <input type="text" name="numero_telephone" value={form.numero_telephone} onChange={handleChange} />
              <label>Biographie</label>
              <textarea name="description" value={form.description} onChange={handleChange} />
              <div className="change-password">
                <h4>Changer le mot de passe</h4>
                <form onSubmit={handleChangePassword}>
                  <input
                    type="password"
                    placeholder="Ancien mot de passe"
                    value={passwordForm.ancien}
                    onChange={(e) => setPasswordForm({ ...passwordForm, ancien: e.target.value })}
                    required
                  />
                  <input
                    type="password"
                    placeholder="Nouveau mot de passe"
                    value={passwordForm.nouveau}
                    onChange={(e) => setPasswordForm({ ...passwordForm, nouveau: e.target.value })}
                    required
                  />
                  <input
                    type="password"
                    placeholder="Confirmer le mot de passe"
                    value={passwordForm.confirmation}
                    onChange={(e) => setPasswordForm({ ...passwordForm, confirmation: e.target.value })}
                    required
                  />
                </form>
              </div>
              <button onClick={handleSave} className="btn-valider" disabled={loading}>
                {loading ? "Enregistrement..." : "💾 Enregistrer"}
              </button>

            </>
          ) : (
            <>
              <div className="client-header">
                <h3>{prenom} {nom}</h3>
                {mode === "admin" && (
                  <button
                    className="edit-icon"
                    onClick={() => navigate(`/admin/client/edit/${id}`)}
                    title="Modifier le client"
                  >
                    ✏️
                  </button>
                )}
              </div>
              <p><strong>Email :</strong> {email}</p>
              <p><strong>Téléphone :</strong> {numero_telephone}</p>
              <p><strong>Biographie :</strong> {description}</p>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default ClientBanner;
