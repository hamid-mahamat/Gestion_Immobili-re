-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 04 oct. 2026 à 12:41
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_immo`
--

-- --------------------------------------------------------

--
-- Structure de la table `bien`
--

CREATE TABLE `bien` (
  `id_bien` int(50) NOT NULL,
  `id_pro` int(50) NOT NULL,
  `type` varchar(100) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `surface` varchar(100) NOT NULL,
  `nb_chambre` int(100) NOT NULL,
  `prix_location` int(100) NOT NULL,
  `statut` varchar(100) NOT NULL,
  `date_operation` date NOT NULL,
  `image` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `bien`
--

INSERT INTO `bien` (`id_bien`, `id_pro`, `type`, `adresse`, `surface`, `nb_chambre`, `prix_location`, `statut`, `date_operation`, `image`) VALUES
(25, 6, 'Terrain', 'Diguel', '15', 0, 30000000, 'En vente', '2025-10-22', ''),
(26, 6, 'Maison', 'Amriguébé, N\'Djamena, Chari Bâche ', '1', 5, 400000, 'Loué', '2025-10-10', '');

-- --------------------------------------------------------

--
-- Structure de la table `demandeur`
--

CREATE TABLE `demandeur` (
  `id_demandeur` int(50) NOT NULL,
  `bien_demande` varchar(100) NOT NULL,
  `quartier` varchar(100) NOT NULL,
  `montant` int(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `etat` varchar(50) NOT NULL,
  `date_operation` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `locataire`
--

CREATE TABLE `locataire` (
  `id_loca` int(50) NOT NULL,
  `nom_loca` varchar(100) NOT NULL,
  `prenom_loca` varchar(100) NOT NULL,
  `telephone_loca` int(100) NOT NULL,
  `contact_urgent` int(50) NOT NULL,
  `date_creation` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `location`
--

CREATE TABLE `location` (
  `id_location` int(11) NOT NULL,
  `id_bien` int(11) NOT NULL,
  `id_loca` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `loyer_mensuel` int(11) NOT NULL,
  `caution` int(11) NOT NULL,
  `date_operation` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `maintenance`
--

CREATE TABLE `maintenance` (
  `id_main` int(50) NOT NULL,
  `id_bien` int(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `date_demande` date NOT NULL,
  `cout` int(11) NOT NULL,
  `date_operation` date NOT NULL,
  `statut` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `payement`
--

CREATE TABLE `payement` (
  `id_payement` int(11) NOT NULL,
  `id_location` int(11) NOT NULL,
  `id_vente` int(50) NOT NULL,
  `montant` int(11) NOT NULL,
  `date_operation` date NOT NULL,
  `mode_payement` varchar(50) NOT NULL,
  `id_user` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `propritaire`
--

CREATE TABLE `propritaire` (
  `id_pro` int(50) NOT NULL,
  `nom_pro` varchar(100) NOT NULL,
  `prenom_pro` varchar(100) NOT NULL,
  `adresse_pro` varchar(100) NOT NULL,
  `contact_pro` varchar(50) NOT NULL,
  `date_creation` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `propritaire`
--

INSERT INTO `propritaire` (`id_pro`, `nom_pro`, `prenom_pro`, `adresse_pro`, `contact_pro`, `date_creation`) VALUES
(6, 'Adoum', 'Oumar', '6253,Amriguébué,Ndjamena', '66233345', '2025-05-16'),
(9, 'Ali', 'Oumar', 'Carré 1/Amriguebé', '23233333444', '2025-06-26'),
(10, 'Moussa', 'Issa', 'Bololo', '66 12 33 55', '2025-07-07'),
(11, 'Mht', 'Youssouf', 'Rue de 30', '90 09 04 05', '2025-07-07');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(50) NOT NULL,
  `nom_user` varchar(100) NOT NULL,
  `prenom_user` varchar(50) NOT NULL,
  `mot_passe` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `nom_user`, `prenom_user`, `mot_passe`, `role`) VALUES
(1, 'Hamid ', 'Mahamat', '12345', 'Admin');

-- --------------------------------------------------------

--
-- Structure de la table `ventes`
--

CREATE TABLE `ventes` (
  `id_vente` int(50) NOT NULL,
  `id_bien` int(50) NOT NULL,
  `nom_acheteur` varchar(100) NOT NULL,
  `prenom_acheteur` varchar(200) NOT NULL,
  `prix_vente` int(200) NOT NULL,
  `date_operation` date NOT NULL,
  `contact_acheteur` int(20) NOT NULL,
  `adresse_acheteur` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bien`
--
ALTER TABLE `bien`
  ADD PRIMARY KEY (`id_bien`);

--
-- Index pour la table `demandeur`
--
ALTER TABLE `demandeur`
  ADD PRIMARY KEY (`id_demandeur`);

--
-- Index pour la table `locataire`
--
ALTER TABLE `locataire`
  ADD PRIMARY KEY (`id_loca`);

--
-- Index pour la table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id_location`);

--
-- Index pour la table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id_main`);

--
-- Index pour la table `payement`
--
ALTER TABLE `payement`
  ADD PRIMARY KEY (`id_payement`);

--
-- Index pour la table `propritaire`
--
ALTER TABLE `propritaire`
  ADD PRIMARY KEY (`id_pro`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD PRIMARY KEY (`id_vente`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `bien`
--
ALTER TABLE `bien`
  MODIFY `id_bien` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `demandeur`
--
ALTER TABLE `demandeur`
  MODIFY `id_demandeur` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `locataire`
--
ALTER TABLE `locataire`
  MODIFY `id_loca` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `location`
--
ALTER TABLE `location`
  MODIFY `id_location` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id_main` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `payement`
--
ALTER TABLE `payement`
  MODIFY `id_payement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `propritaire`
--
ALTER TABLE `propritaire`
  MODIFY `id_pro` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `ventes`
--
ALTER TABLE `ventes`
  MODIFY `id_vente` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
