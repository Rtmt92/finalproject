// frontend/src/pages/Pay.jsx
import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Elements } from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";
import CheckoutForm from "../compenents/CheckoutForm";
import '../styles/pay.css';

const stripePromise = loadStripe(
  "pk_test_51RcVcGPut8fuuvIhnhfeTWcZSSHq4IOgJg37oodYS2KIX2XjCFNQ7P1ZP6izxuFzQOqGvQ0NvNyng6sRr0508etp009qmdvCYo"
);

export default function Pay() {
  const navigate = useNavigate();
  const [total, setTotal]       = useState(0);
  const [clientId, setClientId] = useState(null);
  const [panierId, setPanierId] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) {
      alert("Vous devez être connecté pour accéder au paiement.");
      navigate("/login", { replace: true });
      return;
    }

    // Récupérer les infos utilisateur
    fetch("http://localhost:8000/api/me", {
      headers: { Authorization: `Bearer ${token}` }
    })
      .then(res => {
        if (!res.ok) throw new Error();
        return res.json();
      })
      .then(data => {
        setClientId(data.id_client);
      })
      .catch(() => {
        setClientId(null);
      });

    // Récupérer le panier
    fetch("http://localhost:8000/panier", {
      headers: { Authorization: `Bearer ${token}` }
    })
      .then(res => {
        if (!res.ok) throw new Error();
        return res.json();
      })
      .then(data => {
        const prix = parseFloat(data.prix_total);
        setTotal(isNaN(prix) ? 0 : prix);
        setPanierId(data.id_panier);
      })
      .catch(() => {
        setTotal(0);
        setPanierId(null);
      });
  }, [navigate]);

  // Si clientId ou panierId manquant, on empêche le rendu du formulaire
  const readyToPay = clientId && panierId && total > 0;

  return (
    <div className="checkout-bg">
      <div className="checkout-container">
        <div className="checkout-total">
          <span className="checkout-total-label">Total :</span>
          <span className="checkout-total-amount">
            {total.toFixed(2)} €
          </span>
        </div>

        {readyToPay ? (
          <Elements stripe={stripePromise}>
            <CheckoutForm
              amount={total}
              clientId={clientId}
              panierId={panierId}
            />
          </Elements>
        ) : (
          <p style={{ color: "#fcb040", textAlign: "center" }}>
            Impossible de lancer le paiement.<br/>
            Vérifiez que vous êtes connecté et que votre panier n’est pas vide.
          </p>
        )}

        <div className="checkout-methods">
          <img
            src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png"
            alt="Visa"
            className="checkout-icon"
          />
          <img
            src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg"
            alt="PayPal"
            className="checkout-icon"
          />
          <img
            src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg"
            alt="Mastercard"
            className="checkout-icon"
          />
          <img
            src="https://upload.wikimedia.org/wikipedia/commons/4/46/Bitcoin.svg"
            alt="Bitcoin"
            className="checkout-icon"
          />
        </div>
      </div>
    </div>
  );
}
