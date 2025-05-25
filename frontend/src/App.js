import logo from './logo.svg';
import './App.css';
import Header from './compenents/Header';
import Footer from './compenents/Footer';
import ProductCard from './compenents/ProductCard';
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import Login from './pages/login';
import Register from './pages/register';
import Home from './pages/home';
import GeneralTerm from './pages/GeneralTerm';
import CreateAnnounce from './pages/CreateAnnounce';
import UpdateAnnounce from './pages/UpdateAnnounce';
import Pay from './pages/Pay';


const App = () => {
  return (
    <div>
      <Header />
      <div className="main-content">
        <Router>
          <Routes>
            <Route path="/" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/home" element={<Home />} />
            <Route path="/GeneralTerm" element={<GeneralTerm />} />
            <Route path="/CreateAnnounce" element={<CreateAnnounce />} />
            <Route path="/UpdateAnnounce" element={<UpdateAnnounce />} />
            <Route path="/Pay" element={<Pay />} />

          </Routes>
        </Router>
      </div>
      <Footer />
    </div>
  );
};

export default App;
