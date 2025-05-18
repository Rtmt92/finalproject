import logo from './logo.svg';
import './App.css';
import Header from './compenents/Header';
import Footer from './compenents/Footer';
import ProductCard from './compenents/ProductCard';
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import Login from './pages/login';
import Register from './pages/register';


const App = () => {
  return (
    <div>
      <Header />
      <div className="main-content">
        <Router>
          <Routes>
            <Route path="/" element={<Login />} />
            <Route path="/register" element={<Register />} />
          </Routes>
        </Router>
      </div>
      <Footer />
    </div>
  );
};

export default App;
