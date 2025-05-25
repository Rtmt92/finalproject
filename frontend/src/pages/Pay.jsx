import React from "react";
import "../styles/pay.css";

const pay = () => {
  return (
    <div className="checkout-bg">
      <div className="checkout-container">
        <div className="checkout-total">
          <span className="checkout-total-label">Total:</span>
          <span className="checkout-total-amount">35$</span>
        </div>
        <form className="checkout-form">
          <label className="checkout-label" htmlFor="card-number">
            Votre carte
          </label>
          <input
            id="card-number"
            className="checkout-input"
            type="text"
            placeholder="Numero de carte"
          />

          <div className="checkout-row">
            <div className="checkout-col">
              <label className="checkout-label" htmlFor="exp-date">
                Date D'expiration
              </label>
              <input
                id="exp-date"
                className="checkout-input"
                type="text"
                placeholder="MM/AA"
              />
            </div>
            <div className="checkout-col">
              <label className="checkout-label" htmlFor="ccv">
                CCV
              </label>
              <input
                id="ccv"
                className="checkout-input"
                type="text"
                placeholder="123"
              />
            </div>
          </div>
          <div className="checkout-methods">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" className="checkout-icon" />
            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" className="checkout-icon" />
            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" className="checkout-icon" />
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/46/Bitcoin.svg" alt="Bitcoin" className="checkout-icon" />
          </div>
        </form>
      </div>
      <button className="checkout-btn">Valider mon panier</button>
    </div>
  );
};

export default pay;
