-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 06 juil. 2025 à 17:10
-- Version du serveur : 11.5.2-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dejavu`
--

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `nom`) VALUES
(8, 'Cartes graphiques'),
(9, 'cartes mere'),
(10, 'processeur');

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `id_client` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `numero_telephone` varchar(20) DEFAULT NULL,
  `photo_profil` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id_client`, `nom`, `prenom`, `email`, `numero_telephone`, `photo_profil`, `description`, `mot_de_passe`, `role`) VALUES
(51, 'teste', 'Rayan', 'test@gmail.com', '1234567890', NULL, NULL, '$2y$10$Wbfa0vKI99Yvvqe6V0dCmuKtVkIh.sRClKqiEGESIDSf5lpdUiPmO', 'client'),
(52, 'test', 'docker', 'testdocker@gmail.com', '0695106490', NULL, NULL, '$2y$10$mmPyMVAHk.0C/ae2S74E1egysrSnwQ8qpvfQjvEMnhiyAIoAMzhqK', 'client'),
(54, 'admin', 'Rayan', 'testtoken@gmail.com', '0695106490', 'http://localhost:8000/uploads/profils/profil_6863b941c21871.75190550.jpg', '', '$2y$10$qJrFqb3/FVtyGEsVJgSslumD9Jo9nQMTbW1RoxI4wyu7pS0ntN2r.', 'client'),
(55, 'test', 'injection', 'testinjection@gmail.com', '0695106490', NULL, NULL, '$2y$10$gCvZq11Iu88/4Lx990MmretdrLaIibr0bSzBvCYPuLaPkehpPqQ6C', 'client'),
(59, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$SLRICVoMAlQ.YmxHis9TV.nxTCNX4lJsBwwFr/soFjz/y/joO.tfy', 'client'),
(60, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$A2xR95C0K2/6kFGB1BCUEubA9d1jU08R1LJzB3lbNubYtLqGlDxuW', 'client'),
(61, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$SMDGDPNDPGlIDHXcNySNxuiDWOJ1Te4npw3JlDdkh9EOc/ga9d9Tq', 'client'),
(62, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$WoMafB82BErpGqoqCjkrCuZFmsMOeN.non413xyJrjTOdUuGctrVC', 'client'),
(63, 'CascadeTest', 'DeleteTest', 'cascade@example.com', '0000000000', NULL, NULL, '$2y$10$gQ5E77lyhkMcEvWNVHFm7eEAigAZN1J0y2ydmhYtdf82hvpULp6Te', 'client'),
(66, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$vXlLi5vUl8g9wCq.21RrTezMROJwPScGtBUOEmgw.cCN0NbsT/2KS', 'client'),
(68, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$qEyIC.mF4Taw2/plvG7DauKXnSfbtu0LMm7YiLiXAexePvsCBDRNO', 'client'),
(70, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$H5o13ti2TKbwgo5jtZqp4.MWy63mE0BfkJc75x.8nMkhPDvRc7U5G', 'client'),
(73, 'admin', 'Rayan', 'jhondoe@gmail.com', '0695106490', NULL, NULL, '$2y$10$IpVAdOTCxtKV8D181UijROOHzTYMh.nqLMQdvlhqi/eHRcP8VA/.2', 'client'),
(74, 'Toumert', 'Rayan', 'testpass@gmail.com', '1234567890', NULL, NULL, '$2y$10$ZiazezlKE5/ike9OLqh11uzsiDcQyq8HV1JNOc502jP53GFTBQVRC', 'client'),
(75, 'Toumert', 'Rayan', 'Rayan1234567@gmail.com', '0695106490', NULL, NULL, '$2y$10$1b3pXaQsOgHq56RZJJ8QIeshyA5f3Le/HQHNQ0EWk7eWg5tDTYtZS', 'client'),
(76, 'admin', 'Sarah', 'admin12345678@gmail.com', '0695106490', NULL, NULL, '$2y$10$zPD3v0FEoNhJUNEBRaHu5eDcO6WYFBU2enVzwwEtz7iJW8Di0ffE2', 'client'),
(79, '\'; DROP TABLE client; --', 'Injection', 'inject@test.com', '0123456789', NULL, NULL, '$2y$10$cngqj6xAaHRp.QhmOp8diOHtnWE9z3EZJ0iIOLJqEDGh7GjEe.sTm', 'client'),
(81, 'Toumert', 'Rayan', 'rayantoumert.rt@gmail.com', '0695106490', 'http://localhost:8000/uploads/profils/profil_68643e5e51b103.15855203.PNG', '', '$2y$10$R1lJa/P80VLWexh0fM/xi.88WDP/fW8x0wxbB6C6BY63iUS64r/mO', 'admin'),
(82, 'Toumert', 'Rayan', 'rayantoumert@com', '0695106490', NULL, NULL, '$2y$10$4WYZJZ5iB17mnHl81iUriergvgG1TmWLfr0Pc2FCZg94Vikf6TSYq', 'client'),
(83, 'Martin', 'docker', 'manu@gmail.com', '0695106490', NULL, NULL, '$2y$10$X5OHe9moKteDATZPQu1pkecqekFSo2j0MiVfeYUTluTyaXpPNboTS', 'admin'),
(84, 'Toumert', 'Rayan', 'rayantoumert@gmail.com', '0695106490', NULL, NULL, '$2y$10$GgqmM3HMfLFcsaCP2Dx.W.fFGKZ3WqZjS6KCJ1NyGcaaLTPfYNCNS', 'admin'),
(88, 'Toumert', 'Rayan', 'testlogin@gmail.com', '0695106490', 'http://localhost:8000/uploads/profils/profil_686a440e771218.56425993.jpg', '', '$2y$10$.YX8gAicNYQcb2A1aRdv..Y3taE72riESbgETMh10FFVvDDmaPf0e', 'client');

-- --------------------------------------------------------

--
-- Structure de la table `image`
--

CREATE TABLE `image` (
  `id_image` int(11) NOT NULL,
  `lien` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `image`
--

INSERT INTO `image` (`id_image`, `lien`) VALUES
(3, 'https://images.rtl.fr/~c/2000v2000/rtl/www/1449859-shrek-dans-shrek-4-il-etait-une-fin.jpg'),
(4, 'https://images.rtl.fr/~c/2000v2000/rtl/www/1449859-shrek-dans-shrek-4-il-etait-une-fin.jpg'),
(120, 'uploads/produits/685bb8ad4510e_Capture d\'écran 2023-12-20 163154.png'),
(121, 'uploads/produits/685bb9e8a7055_imagetest.jpg'),
(122, 'uploads/produits/685bb9f00d545_imagetest.jpg'),
(123, 'uploads/produits/685bb9f4ecae0_imagetest.jpg'),
(124, 'uploads/produits/685bbb2a9aeab_imagetest.jpg'),
(125, 'uploads/produits/685bbd585f83a_imagetest2.jpg'),
(126, 'uploads/produits/685bbd8837642_Capture d\'écran 2023-12-20 163154.png'),
(127, 'uploads/produits/685bbd9c0b907_Capture d\'écran 2023-12-20 163154.png'),
(128, 'uploads/produits/685bbd9c0cc6c_Capture d\'écran 2023-12-13 160040.png'),
(129, 'uploads/produits/685bbdc56e772_imagetest.jpg'),
(130, 'uploads/produits/685bbea9822d0_imagetest2.jpg'),
(131, 'uploads/produits/685bbea983349_imagetest.jpg'),
(134, 'uploads/produits/685bdb45e7ba0_91SoCp5MkVL._AC_SL1500_.jpg'),
(135, 'uploads/produits/685bdb45e86d1_41lyiFVDsOL._AC_SR100,100_QL65_.jpg'),
(136, 'uploads/produits/685bdba25adf0_51hVvPf7T0L._AC_SL1000_.jpg'),
(137, 'uploads/produits/685bdba25bca3_61IIbwz-+ML._AC_SL1500_.jpg'),
(138, 'uploads/produits/685bdc3051756_41b56CkrCmL._AC_SL1280_.jpg'),
(139, 'uploads/produits/685bdc9615120_81ZhEI9zTUL._AC_SL1500_.jpg'),
(140, 'uploads/produits/685bdc961609e_81Iu+IK30WL._AC_SL1500_.jpg'),
(141, 'uploads/produits/685bdc9616cb1_81JPI-8kaQL._AC_SL1500_.jpg'),
(142, 'uploads/produits/685bdc96176f0_81wWdxyfxdL._AC_SL1500_.jpg'),
(143, 'uploads/produits/685bdc9618358_81C3WHGqLvL._AC_SL1500_.jpg'),
(144, 'uploads/produits/685bdcee1d31d_61hPyxPlRjL._AC_SL1200_ (1).jpg'),
(146, 'uploads/produits/685c6924f31e8_61hPyxPlRjL._AC_SL1200_.jpg'),
(147, 'uploads/produits/685c6924f405b_81JPI-8kaQL._AC_SL1500_.jpg'),
(164, 'http://localhost:8000/uploads/686797316e66c.png'),
(165, 'uploads/produits/6867977371bc8_default-avatar.png'),
(166, 'uploads/produits/6867e8341c670_usecase.drawio.png');

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `contenu` text DEFAULT NULL,
  `date_envoi` datetime DEFAULT NULL,
  `id_client` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

CREATE TABLE `panier` (
  `id_panier` int(11) NOT NULL,
  `prix_total` decimal(10,2) DEFAULT NULL,
  `id_client` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `panier`
--

INSERT INTO `panier` (`id_panier`, `prix_total`, `id_client`) VALUES
(28, 56.00, 51),
(29, 770.00, 52),
(31, 1526.00, 54),
(38, 450.00, 81),
(39, 1220.00, 84),
(43, 1143.99, 88);

-- --------------------------------------------------------

--
-- Structure de la table `panier_produit`
--

CREATE TABLE `panier_produit` (
  `id_panier` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `panier_produit`
--

INSERT INTO `panier_produit` (`id_panier`, `id_produit`) VALUES
(28, 25),
(29, 26),
(39, 26),
(43, 26),
(29, 27),
(31, 27),
(39, 27),
(31, 29),
(43, 34);

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `id_produit` int(11) NOT NULL,
  `nom_produit` varchar(255) NOT NULL DEFAULT '',
  `prix` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `quantite` int(11) DEFAULT 1,
  `etat` enum('très bon état','correct','parfait état') DEFAULT 'très bon état'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`id_produit`, `nom_produit`, `prix`, `description`, `id_categorie`, `quantite`, `etat`) VALUES
(25, 'GIGABYTE Carte mère A520M K V23e', 56.00, 'Prend en charge les processeurs AMD Ryzen série 5000 AM4, jusqu\'à 5100MHz DDR4 (OC), PCIe Gen3 x4 M.2, LAN GbE, USB 3.2 Gen 1', NULL, 1, 'correct'),
(26, 'AMD Ryzen 7 5800X Processeur', 144.00, ' (8 Cœurs/16 Threads, 105W TDP, Socket AM4, 36 Mo Cache, jusqu\'à 4,7 Ghz Fréquence Boost, sans Ventilateur)', 10, 8, 'parfait état'),
(27, 'Intel® Core™ i9-14900', 626.00, ' processeur pour PC de Bureau, 24 cœurs (8 P-Cores + 16 E-Cores) jusqu\'à 5,8 GHz', 10, 1, 'parfait état'),
(29, 'MSI Carte mère de jeu MPG B550', 900.00, 'MSI Carte mère de jeu MPG B550', 9, 1, 'très bon état'),
(34, 'PRINZ', 999.99, 'rf', 9, 1, 'très bon état');

-- --------------------------------------------------------

--
-- Structure de la table `produit_image`
--

CREATE TABLE `produit_image` (
  `id_image` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `produit_image`
--

INSERT INTO `produit_image` (`id_image`, `id_produit`) VALUES
(134, 25),
(135, 25),
(136, 26),
(137, 26),
(138, 27),
(144, 29),
(166, 34);

-- --------------------------------------------------------

--
-- Structure de la table `signalement`
--

CREATE TABLE `signalement` (
  `id_client` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `date_send` datetime DEFAULT NULL,
  `status` enum('tenté','en cours','à traiter') DEFAULT 'tenté'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transaction`
--

CREATE TABLE `transaction` (
  `id_transaction` int(11) NOT NULL,
  `montant_total` decimal(10,2) DEFAULT NULL,
  `date_transaction` datetime DEFAULT NULL,
  `id_client` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `transaction`
--

INSERT INTO `transaction` (`id_transaction`, `montant_total`, `date_transaction`, `id_client`) VALUES
(38, 956.00, '2025-07-01 10:35:03', 54);

-- --------------------------------------------------------

--
-- Structure de la table `transaction_panier`
--

CREATE TABLE `transaction_panier` (
  `id_panier` int(11) NOT NULL,
  `id_transaction` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `transaction_panier`
--

INSERT INTO `transaction_panier` (`id_panier`, `id_transaction`) VALUES
(31, 38);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_client`);

--
-- Index pour la table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id_image`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `message_ibfk_1` (`id_client`);

--
-- Index pour la table `panier`
--
ALTER TABLE `panier`
  ADD PRIMARY KEY (`id_panier`),
  ADD KEY `panier_ibfk_1` (`id_client`);

--
-- Index pour la table `panier_produit`
--
ALTER TABLE `panier_produit`
  ADD PRIMARY KEY (`id_panier`,`id_produit`),
  ADD KEY `panier_produit_ibfk_2` (`id_produit`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `produit_ibfk_1` (`id_categorie`);

--
-- Index pour la table `produit_image`
--
ALTER TABLE `produit_image`
  ADD PRIMARY KEY (`id_image`,`id_produit`),
  ADD KEY `produit_image_ibfk_2` (`id_produit`);

--
-- Index pour la table `signalement`
--
ALTER TABLE `signalement`
  ADD PRIMARY KEY (`id_client`,`id_produit`),
  ADD KEY `signalement_ibfk_2` (`id_produit`);

--
-- Index pour la table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id_transaction`),
  ADD KEY `transaction_ibfk_1` (`id_client`);

--
-- Index pour la table `transaction_panier`
--
ALTER TABLE `transaction_panier`
  ADD PRIMARY KEY (`id_panier`,`id_transaction`),
  ADD KEY `transaction_panier_ibfk_2` (`id_transaction`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
  MODIFY `id_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT pour la table `image`
--
ALTER TABLE `image`
  MODIFY `id_image` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `panier`
--
ALTER TABLE `panier`
  MODIFY `id_panier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `id_transaction` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE;

--
-- Contraintes pour la table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE;

--
-- Contraintes pour la table `panier_produit`
--
ALTER TABLE `panier_produit`
  ADD CONSTRAINT `panier_produit_ibfk_1` FOREIGN KEY (`id_panier`) REFERENCES `panier` (`id_panier`) ON DELETE CASCADE,
  ADD CONSTRAINT `panier_produit_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_produit`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produit_image`
--
ALTER TABLE `produit_image`
  ADD CONSTRAINT `produit_image_ibfk_1` FOREIGN KEY (`id_image`) REFERENCES `image` (`id_image`) ON DELETE CASCADE,
  ADD CONSTRAINT `produit_image_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_produit`) ON DELETE CASCADE;

--
-- Contraintes pour la table `signalement`
--
ALTER TABLE `signalement`
  ADD CONSTRAINT `signalement_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE,
  ADD CONSTRAINT `signalement_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_produit`) ON DELETE CASCADE;

--
-- Contraintes pour la table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE;

--
-- Contraintes pour la table `transaction_panier`
--
ALTER TABLE `transaction_panier`
  ADD CONSTRAINT `fk_panier_transaction_cascade` FOREIGN KEY (`id_panier`) REFERENCES `panier` (`id_panier`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_panier_ibfk_2` FOREIGN KEY (`id_transaction`) REFERENCES `transaction` (`id_transaction`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
