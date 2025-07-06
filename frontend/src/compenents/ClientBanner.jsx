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
  onDelete,
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
    photo_profil: photo || "",
  });

  const [passwordForm, setPasswordForm] = useState({
    ancien: '',
    nouveau: '',
    confirmation: ''
  });

  const [messagePwd, setMessagePwd] = useState('');
  const initials = `${prenom?.charAt(0) || ""}${nom?.charAt(0) || ""}`.toUpperCase();

  useEffect(() => {
    if (!idFromProps && token) {
      fetch("http://localhost:8000/api/me", {
        headers: { Authorization: `Bearer ${token}` }
      })
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
        const res = await fetch("http://localhost:8000/upload-photo", {
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
      // Ne pas inclure le champ mot_de_passe vide par erreur
      const { mot_de_passe, ...infosSansMotDePasse } = form;

      const resInfo = await fetch(`http://localhost:8000/client/${id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(infosSansMotDePasse),
      });

      if (!resInfo.ok) {
        const err = await resInfo.json();
        alert(err.error || "Erreur lors de la mise à jour des infos");
        setLoading(false);
        return;
      }

      // Changement de mot de passe uniquement si rempli
      if (passwordForm.ancien || passwordForm.nouveau || passwordForm.confirmation) {
        if (passwordForm.nouveau !== passwordForm.confirmation) {
          alert("Les mots de passe ne correspondent pas.");
          setLoading(false);
          return;
        }

        const resPwd = await fetch(`http://localhost:8000/client/${id}/password`, {
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

  if (mode === "edit") {
    return (
      <div className="client-banner">
        <div className="avatar-wrapper">
          <div className="client-avatar">
            <img
              src={form.photo_profil || "/default-avatar.png"}
              alt={`client-${form.nom || "photo"}`}
            />
          </div>
          <label className="upload-btn">
            Modifier la photo de profil
            <input type="file" name="photo_profil" accept="image/*" onChange={handleChange} />
          </label>
        </div>

        <div className="client-info">
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
            <input
              type="password"
              placeholder="Ancien mot de passe"
              value={passwordForm.ancien}
              onChange={(e) => setPasswordForm({ ...passwordForm, ancien: e.target.value })}
            />
            <input
              type="password"
              placeholder="Nouveau mot de passe"
              value={passwordForm.nouveau}
              onChange={(e) => setPasswordForm({ ...passwordForm, nouveau: e.target.value })}
            />
            <input
              type="password"
              placeholder="Confirmer le mot de passe"
              value={passwordForm.confirmation}
              onChange={(e) => setPasswordForm({ ...passwordForm, confirmation: e.target.value })}
            />
            {messagePwd && <p className="message">{messagePwd}</p>}
          </div>

          <button onClick={handleSave} className="btn-valider" disabled={loading}>
            Enregistrer
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="client-banner">
      <div className="client-avatar">
        {photo ? <img src={photo} alt="profil" /> : <span>{initials}</span>}
      </div>
      <div className="client-info">
        <div className="client-header">
          <h3>profil {prenom?.toLowerCase()}</h3>
          <button
            className="edit-button"
            onClick={() => navigate(`/admin/client/edit/${id}`)}
            title="Modifier"
          >
            🖉
          </button>
        </div>
        <p className="client-subtitle">Biographie</p>
        <p className="client-description">
          {description?.trim() || "Aucune biographie fournie."}
        </p>
      </div>
    </div>
  );
};

export default ClientBanner;
