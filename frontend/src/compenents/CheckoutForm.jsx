import React, { useState } from "react";
import { useStripe, useElements, CardElement } from "@stripe/react-stripe-js";
import { useNavigate } from "react-router-dom";
import API_BASE_URL from "../config";

const CheckoutForm = ({ amount, clientId, panierId }) => {
  const stripe = useStripe();
  const elements = useElements();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);

const handleSubmit = async (e) => {
  e.preventDefault();

  if (!stripe || !elements) {
    alert("Stripe n’est pas prêt.");
    return;
  }

  setLoading(true);

  try {
    // 1. Demander à backend de créer le paiement
    const res = await fetch(`${API_BASE_URL}/payment-intent`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ amount }),
    });

    const data = await res.json();
    if (!data.clientSecret) throw new Error("Client secret introuvable");

    // 2. Confirmer le paiement Stripe
    const result = await stripe.confirmCardPayment(data.clientSecret, {
      payment_method: { card: elements.getElement(CardElement) },
    });

    if (result.error) {
      alert(result.error.message);
      setLoading(false);
      return;
    }

    if (result.paymentIntent.status === "succeeded") {
      alert("Paiement réussi !");

      // Récupération du token
      const token = localStorage.getItem("token");

      // 3. Enregistrer la transaction
      const save = await fetch(`${API_BASE_URL}/enregistrer-transaction`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,  // token obligatoire
        },
        body: JSON.stringify({
          amount,
          id_client: clientId,
          id_panier: panierId,
        }),
      });

      if (!save.ok) {
        alert("Le paiement a réussi, mais la transaction n’a pas été enregistrée.");
        setLoading(false);
        return;
      }

      const response = await save.json();
      console.log("Transaction enregistrée :", response);

      // 4. Vider le panier (produits + total) avec token Authorization
      const viderRes = await fetch(`${API_BASE_URL}/panier/${panierId}/vider`, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,   // très important
        },
        credentials: "include",
      });


      // 5. Redirection vers le profil
      navigate("/profil");
    } else {
      alert("Paiement non finalisé.");
    }
  }catch (err) {
  if (err instanceof Response) {
    err.json().then(errorBody => {
      alert("Erreur durant le paiement :\n" + JSON.stringify(errorBody, null, 2));
    }).catch(() => {
      err.text().then(text => {
        alert("Erreur durant le paiement :\n" + text);
      });
    });
  } else {
    alert("Erreur durant le paiement :\n" + (typeof err === "object" ? JSON.stringify(err, null, 2) : err));
  }
  console.error("Erreur paiement:", err);
}finally {
    setLoading(false);
  }
};


  return (
    <form onSubmit={handleSubmit} className="checkout-form">
      <label className="checkout-label">Votre carte</label>
      <CardElement className="checkout-input" />
      <button type="submit" className="checkout-btn" disabled={loading || !stripe}>
        {loading ? "Paiement en cours..." : `Payer ${amount.toFixed(2)} €`}
      </button>
    </form>
  );
};

export default CheckoutForm;
