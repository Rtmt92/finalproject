import React, { useEffect, useState } from "react";
import { Elements } from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";
import CheckoutForm from "../compenents/CheckoutForm";
import "../styles/pay.css";

const stripePromise = loadStripe("pk_test_51RcVcGPut8fuuvIhnhfeTWcZSSHq4IOgJg37oodYS2KIX2XjCFNQ7P1ZP6izxuFzQOqGvQ0NvNyng6sRr0508etp009qmdvCYo");

const Pay = () => {
  const [total, setTotal] = useState(0);
  const [clientId, setClientId] = useState(null);
  const [panierId, setPanierId] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) return;

    fetch("http://localhost:3000/api/me", {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
      .then(res => res.json())
      .then(data => {
        setClientId(data.id_client);
      })
      .catch(() => setClientId(null));

    // 2. Récupérer son panier
    fetch("http://localhost:3000/panier", {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
      .then(res => res.json())
      .then(data => {
        const prix = parseFloat(data?.prix_total);
        setTotal(isNaN(prix) ? 0 : prix);
        setPanierId(data?.id_panier);
      })
      .catch(() => {
        setTotal(0);
        setPanierId(null);
      });
  }, []);

  return (
    <div className="checkout-bg">
      <div className="checkout-container">
        <div className="checkout-total">
          <span className="checkout-total-label">Total:</span>
          <span className="checkout-total-amount">{total.toFixed(2)} €</span>
        </div>

        <Elements stripe={stripePromise}>
          {clientId && panierId ? (
            <CheckoutForm amount={total} clientId={clientId} panierId={panierId} />
          ) : (
            <p style={{ color: "#fcb040" }}>Client ou panier introuvable.</p>
          )}
        </Elements>

        <div className="checkout-methods">
          <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" className="checkout-icon" />
          <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" className="checkout-icon" />
          <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" className="checkout-icon" />
          <img src="https://upload.wikimedia.org/wikipedia/commons/4/46/Bitcoin.svg" alt="Bitcoin" className="checkout-icon" />
        </div>
      </div>
    </div>
  );
};

export default Pay;
