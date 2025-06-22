import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/ClientBanner.css";

const ClientBanner = ({
  id: idFromProps,
  nom,
  prenom,
  description,
  photo, // <- reçu en tant que photo_profil depuis le parent
  email,
  telephone,
  mode = "admin",
}) => {
  const navigate = useNavigate();
  const [id, setId] = useState(idFromProps);
  const [loading, setLoading] = useState(false);

  const [form, setForm] = useState({
    nom: nom || "",
    prenom: prenom || "",
    email: email || "",
    telephone: telephone || "",
    description: description || "",
    mot_de_passe: "",
    photo_profil: photo || "", // bien nommé
  });

  // 🔁 Récupérer ID si absent
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
      const res = await fetch(`http://localhost:3000/client/${id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(form),
      });

      if (res.ok) {
        alert("Modifications enregistrées !");
        navigate(0); // rafraîchit la page
      } else {
        const err = await res.json();
        alert(err.error || "Erreur lors de la mise à jour");
      }
    } catch (err) {
      alert("Erreur réseau");
    } finally {
      setLoading(false);
    }
  };

  const defaultAvatar = "/default-avatar.png";

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
              <input
                type="text"
                name="nom"
                value={form.nom}
                onChange={handleChange}
              />
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
                name="telephone"
                value={form.telephone}
                onChange={handleChange}
              />
              <label>Biographie</label>
              <textarea
                name="description"
                value={form.description}
                onChange={handleChange}
              />
              <label>Nouveau mot de passe</label>
              <input
                type="password"
                name="mot_de_passe"
                value={form.mot_de_passe}
                onChange={handleChange}
              />

              <button
                onClick={handleSave}
                className="btn-valider"
                disabled={loading}
              >
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
              <p><strong>Téléphone :</strong> {telephone}</p>
              <p><strong>Biographie :</strong> {description}</p>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default ClientBanner;
