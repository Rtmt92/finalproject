import "../styles/log.css";
import { Link } from "react-router-dom";

const Login = () => {
  return (
    <div className="container">
      <h1>Connexion</h1>
      <input type="text" placeholder="Nom" />
      <input type="password" placeholder="Mot de passe" />
      <Link to="/register">
            <a>Tu n'as pas encore de compte ? inscrits toi</a>
        </Link>
      <button>Se connecter</button>
    </div>
  );
};

export default Login;


