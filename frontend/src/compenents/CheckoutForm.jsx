import React, { useState } from "react";
import { useStripe, useElements, CardElement } from "@stripe/react-stripe-js";
import { useNavigate } from "react-router-dom";

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
      const res = await fetch("http://localhost:3000/payment-intent", {
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
        return;
      }

      if (result.paymentIntent.status === "succeeded") {
        alert("💳 Paiement réussi !");

        // 3. Enregistrer la transaction
        const save = await fetch("http://localhost:3000/enregistrer-transaction", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            amount,
            id_client: clientId,
            id_panier: panierId,
          }),
        });

        if (!save.ok) {
          alert("Le paiement a réussi, mais la transaction n’a pas été enregistrée.");
          return;
        }

        const response = await save.json();
        console.log("Transaction enregistrée :", response);

        // 4. Vider le panier (produits + total)
        await fetch(`http://localhost:3000/panier/${panierId}/vider`, {
          method: "DELETE",
          credentials: "include",
        });

        // 5. Redirection vers le profil
        navigate("/profil");
      } else {
        alert("Paiement non finalisé.");
      }
    } catch (err) {
      console.error("Erreur paiement:", err);
      alert("Erreur durant le paiement.");
    } finally {
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
