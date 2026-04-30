-- ===================================================
-- projet.sql — Base de données Plateforme Livres
-- Plateforme d'échange de livres — MGSI Groupe 45
-- Étudiants : MAHIR Rabia, ABLAD Mostapha
-- ===================================================


-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 29 avr. 2026 à 19:38
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `plateforme_livres`
--

CREATE
DATABASE IF NOT EXISTS `plateforme_livres`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `plateforme_livres`;

-- --------------------------------------------------------


-- ===================================================
-- TABLE : utilisateurs
-- ===================================================

CREATE TABLE `utilisateurs`
(
    `id`               int(11) NOT NULL,
    `login`            varchar(50)  NOT NULL,
    `mot_de_passe`     varchar(255) NOT NULL,
    `nom`              varchar(100) NOT NULL,
    `prenom`           varchar(100) NOT NULL,
    `email`            varchar(150) NOT NULL,
    `role`             enum('admin','etudiant') DEFAULT 'etudiant',
    `photo`            varchar(255) DEFAULT 'images/default-user1.jfif',
    `date_inscription` datetime     DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===================================================
-- TABLE : echanges
-- ===================================================

CREATE TABLE `echanges`
(
    `id`           int(11) NOT NULL,
    `demandeur_id` int(11) NOT NULL,
    `proprio_id`   int(11) NOT NULL,
    `livre_id`     int(11) NOT NULL,
    `statut`       enum('en_attente','accepte','refuse','termine') DEFAULT 'en_attente',
    `date_demande` datetime DEFAULT current_timestamp(),
    `vu`           tinyint(1) DEFAULT 0,
    `vu_demandeur` tinyint(1) DEFAULT 0,
    `vu_proprio`   tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `echanges`
--

INSERT INTO `echanges` (`id`, `demandeur_id`, `proprio_id`, `livre_id`, `statut`, `date_demande`, `vu`, `vu_demandeur`,
                        `vu_proprio`)
VALUES (1, 2, 3, 3, 'accepte', '2026-04-29 17:11:28', 1, 1, 0);

-- --------------------------------------------------------


-- ===================================================
-- TABLE : livres
-- ===================================================


CREATE TABLE `livres`
(
    `id`          int(11) NOT NULL,
    `titre`       varchar(200) NOT NULL,
    `auteur`      varchar(150) NOT NULL,
    `matiere`     varchar(100) NOT NULL,
    `description` text         DEFAULT NULL,
    `etat`        enum('neuf','bon','acceptable','use') DEFAULT 'bon',
    `statut`      enum('disponible','echange','archive') DEFAULT 'disponible',
    `image`       varchar(255) DEFAULT 'images/default-book.jfif',
    `user_id`     int(11) NOT NULL,
    `date_ajout`  datetime     DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `livres`
--

INSERT INTO `livres` (`id`, `titre`, `auteur`, `matiere`, `description`, `etat`, `statut`, `image`, `user_id`,
                      `date_ajout`)
VALUES (1, 'Algorithmique et programmation', 'Thomas Cormen', 'Informatique',
        'Le livre de référence pour les algorithmes et structures de données.', 'bon', 'disponible',
        'images/default-book.jfif', 2, '2026-04-29 16:55:28'),
       (2, 'Bases de données relationnelles', 'Ramez Elmasri', 'Bases de données',
        'Concepts fondamentaux des bases de données relationnelles.', 'acceptable', 'disponible',
        'images/default-book.jfif', 2, '2026-04-29 16:55:28'),
       (3,
        'Systèmes d\'exploitation modernes', 'Andrew Tanenbaum', 'Systèmes', 'Concepts avancés des systèmes d\'exploitation.',
        'bon', 'echange', 'images/default-book.jfif', 3, '2026-04-29 16:55:28'),
       (4, 'Réseaux informatiques', 'James Kurose', 'Réseaux', 'Du haut vers le bas : approche des réseaux.', 'neuf',
        'disponible', 'images/default-book.jfif', 3, '2026-04-29 16:55:28'),
       (5,
        'Mathématiques pour l\'informatique', 'Eric Lehman', 'Mathématiques', 'Mathématiques discrètes appliquées à l\'informatique.',
        'use', 'disponible', 'images/default-book.jfif', 4, '2026-04-29 16:55:28'),
       (6, 'Intelligence Artificielle', 'Stuart Russell', 'IA',
        'Une approche moderne de l\'intelligence artificielle.', 'bon', 'echange', 'images/default-book.jfif', 4, '2026-04-29 16:55:28'),
(7, 'Génie Logiciel', 'Ian Sommerville', 'Génie logiciel', 'Principes et pratiques du développement logiciel.', 'bon', 'disponible', 'images/default-book.jfif', 5, '2026-04-29 16:55:28'),
(8, 'Développement Web avec PHP', 'Kevin Tatroe', 'Web', 'PHP moderne et développement web avancé.', 'acceptable', 'disponible', 'images/default-book.jfif', 5, '2026-04-29 16:55:28');

-- --------------------------------------------------------


-- ===================================================
-- TABLE : messages
-- ===================================================

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `livre_id` int(11) DEFAULT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `date_envoi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `expediteur_id`, `destinataire_id`, `livre_id`, `contenu`, `lu`, `date_envoi`) VALUES
(1, 3, 2, 1, 'Bonjour, je suis intéressé par votre livre d\'algorithmique. Est-il toujours disponible ?', 1,
        '2026-04-29 16:55:45'),
       (2, 2, 3, 1, 'Oui, il est toujours disponible ! On peut organiser un échange.', 1, '2026-04-29 16:55:45'),
       (3, 4, 3, 3, 'Bonjour, je recherche ce livre sur les OS. Avez-vous quelque chose à proposer en échange ?', 1,
        '2026-04-29 16:55:45'),
       (4, 5, 4, 5, 'Je peux vous proposer mon livre sur PHP en échange des maths.', 0, '2026-04-29 16:55:45');

-- --------------------------------------------------------


-- ===================================================
-- TABLE : notations
-- ===================================================

CREATE TABLE `notations`
(
    `id`            int(11) NOT NULL,
    `notateur_id`   int(11) NOT NULL,
    `note_id`       int(11) NOT NULL,
    `note`          tinyint(4) NOT NULL CHECK (`note` between 1 and 5),
    `commentaire`   text     DEFAULT NULL,
    `date_notation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notations`
--

INSERT INTO `notations` (`id`, `notateur_id`, `note_id`, `note`, `commentaire`, `date_notation`)
VALUES (1, 3, 2, 5, 'Échange rapide et livre en excellent état. Je recommande !', '2026-04-29 16:56:08'),
       (2, 4, 3, 4, 'Très bon échangeur, livre conforme à la description.', '2026-04-29 16:56:08'),
       (3, 2, 4, 3, 'Échange correct, le livre avait quelques annotations non mentionnées.', '2026-04-29 16:56:08');

-- --------------------------------------------------------


--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `login`, `mot_de_passe`, `nom`, `prenom`, `email`, `role`, `photo`,
                            `date_inscription`)
VALUES (1, 'ENSIASD', '$2y$12$ovUtztQZUrwSXPvsEHLNJu7JXHNgup2ZSMMzlERucErKKKAUROTc6', 'Admin', 'ENSIASD',
        'admin@ensiasd.ma', 'admin', 'default.png', '2026-04-29 16:54:25'),
       (2, 'mahir', '$2y$12$VwLIAPHUtcAJD6k3xc1Pi.f427U8LdFN9tX9W.Br.FfUbkv67KYDK', 'MAHIR', 'Rabia',
        'rabia.mahir@ensiasd.ma', 'etudiant', 'uploads/users/1777479071_Capture d’écran 2026-04-13 190250.png',
        '2026-04-29 16:54:25'),
       (3, 'ablad', '$2y$12$VwLIAPHUtcAJD6k3xc1Pi.f427U8LdFN9tX9W.Br.FfUbkv67KYDK', 'ABLAD', 'Mostapha',
        'ablad.m@ensiasd.ma', 'etudiant', 'default.png', '2026-04-29 16:54:25'),
       (4, 'benali', '$2y$12$VwLIAPHUtcAJD6k3xc1Pi.f427U8LdFN9tX9W.Br.FfUbkv67KYDK', 'Benali', 'Sara',
        'sara.benali@ensiasd.ma', 'etudiant', 'default.png', '2026-04-29 16:54:25'),
       (5, 'karim', '$2y$12$VwLIAPHUtcAJD6k3xc1Pi.f427U8LdFN9tX9W.Br.FfUbkv67KYDK', 'Idrissi', 'Karim',
        'karim.idrissi@ensiasd.ma', 'etudiant', 'default.png', '2026-04-29 16:54:25');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `echanges`
--
ALTER TABLE `echanges`
    ADD PRIMARY KEY (`id`),
  ADD KEY `demandeur_id` (`demandeur_id`),
  ADD KEY `proprio_id` (`proprio_id`),
  ADD KEY `livre_id` (`livre_id`);

--
-- Index pour la table `livres`
--
ALTER TABLE `livres`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
    ADD PRIMARY KEY (`id`),
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `destinataire_id` (`destinataire_id`),
  ADD KEY `livre_id` (`livre_id`);

--
-- Index pour la table `notations`
--
ALTER TABLE `notations`
    ADD PRIMARY KEY (`id`),
  ADD KEY `notateur_id` (`notateur_id`),
  ADD KEY `note_id` (`note_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `echanges`
--
ALTER TABLE `echanges`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `livres`
--
ALTER TABLE `livres`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `notations`
--
ALTER TABLE `notations`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `echanges`
--
ALTER TABLE `echanges`
    ADD CONSTRAINT `echanges_ibfk_1` FOREIGN KEY (`demandeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `echanges_ibfk_2` FOREIGN KEY (`proprio_id`) REFERENCES `utilisateurs` (`id`) ON
DELETE
CASCADE,
  ADD CONSTRAINT `echanges_ibfk_3` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`) ON DELETE
CASCADE;

--
-- Contraintes pour la table `livres`
--
ALTER TABLE `livres`
    ADD CONSTRAINT `livres_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
    ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON
DELETE
CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`) ON DELETE
SET NULL;

--
-- Contraintes pour la table `notations`
--
ALTER TABLE `notations`
    ADD CONSTRAINT `notations_ibfk_1` FOREIGN KEY (`notateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notations_ibfk_2` FOREIGN KEY (`note_id`) REFERENCES `utilisateurs` (`id`) ON
DELETE
CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
