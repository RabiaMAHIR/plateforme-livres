=====================================================
  LibreChange — Plateforme d'échange de livres
  MGSI Groupe 45 | MAHIR Rabia & ABLAD Mostapha
=====================================================

PRÉREQUIS
---------
- XAMPP (Apache + MySQL) installé et démarré
- PHP 7.4 ou supérieur
- Navigateur web moderne

=====================================================
  ÉTAPES D'INSTALLATION
=====================================================

ÉTAPE 1 — Copier le projet
--------------------------
Décompresser et copier le dossier "plateforme_livres_MGSI"
dans le répertoire htdocs de XAMPP :

  C:\xampp\htdocs\plateforme_livres_MGSI\

ÉTAPE 2 — Importer la base de données
--------------------------------------
1. Ouvrir phpMyAdmin : http://localhost/phpmyadmin
2. Cliquer sur "Importer"
3. Choisir le fichier : projet.sql
4. Cliquer sur "Exécuter"

La base "plateforme_livres" sera créée automatiquement
avec toutes les tables et données de test.

ÉTAPE 3 — Configurer la connexion (si nécessaire)
--------------------------------------------------
Ouvrir le fichier config.php et vérifier :

  define('DB_HOST',  'localhost');
  define('DB_NAME',  'plateforme_livres');
  define('DB_USER',  'root');
  define('DB_PASS',  '');           <- laisser vide pour XAMPP par défaut

ÉTAPE 4 — Lancer l'application
--------------------------------
Ouvrir dans le navigateur :

  http://localhost/plateforme_livres_MGSI/

=====================================================
  COMPTES DISPONIBLES
=====================================================

COMPTE ADMINISTRATEUR (accès complet)
  Login    : ENSIASD
  Password : ENSIASD2026
  Rôle     : Administrateur

COMPTES ÉTUDIANTS DE TEST
  Login : mahir      | Password : password | Rabia MAHIR
  Login : ablad      | Password : password | Mostapha ABLAD
  Login : benali     | Password : password | Sara Benali
  Login : karim      | Password : password | Karim Idrissi

NOTE IMPORTANTE : Le mot de passe "ENSIASD2026" est stocké
hashé en base de données (bcrypt). Si vous rencontrez un
problème de connexion avec le compte admin, exécutez ce
script SQL dans phpMyAdmin :

  UPDATE utilisateurs
  SET mot_de_passe = '$2y$10$YourHashHere'
  WHERE login = 'ENSIASD';

Ou utilisez le fichier fix_admin_password.php fourni :
  http://localhost/plateforme_livres_MGSI/fix_admin_password.php
(Supprimer ce fichier après utilisation !)

=====================================================
  STRUCTURE DES DOSSIERS
=====================================================

plateforme_livres_MGSI/
  index.php              Page d'accueil
  config.php             Connexion BDD + constantes
  projet.sql             Export base de données
  fix_admin_password.php Script de réinitialisation admin
  README.txt             Ce fichier
  css/
    style.css            Feuille de styles globale
  js/
    script.js            Scripts JavaScript
  images/                Images du projet
  pages/
    login.php            Page de connexion
    register.php         Inscription
    livres.php           Liste des livres + recherche
    livre_detail.php     Détail d'un livre + échange
    ajouter_livre.php    Publier un livre
    modifier_livre.php   Modifier un livre
    messages.php         Messagerie
    profil.php           Profil + bibliothèque personnelle
    admin.php            Tableau de bord admin
    logout.php           Déconnexion
  includes/
    header.php           En-tête global
    footer.php           Pied de page global
  doc/
    captures/            Captures d'écran
    diagrammes/          MCD + MLD
    Fiche.pdf            Fiche de validation

=====================================================
  FONCTIONNALITÉS IMPLÉMENTÉES
=====================================================

1. Publication d'annonces de livres à échanger
   -> pages/ajouter_livre.php + modifier_livre.php

2. Recherche de livres par titre, auteur, matière
   -> pages/livres.php (filtres dynamiques)

3. Messagerie entre étudiants
   -> pages/messages.php (conversations en temps réel)

4. Profil utilisateur avec bibliothèque personnelle
   -> pages/profil.php (mes livres, demandes, notations)

5. Système de notation post-échange
   -> pages/livre_detail.php + profil.php

6. Administration complète
   -> pages/admin.php (gestion users + livres)

=====================================================
  SÉCURITÉ
=====================================================

- Mots de passe hashés avec password_hash() (bcrypt)
- Requêtes SQL préparées (PDO) — protection injection SQL
- htmlspecialchars() sur toutes les sorties (anti-XSS)
- Vérification de session sur toutes les pages protégées
- Vérification du rôle pour les pages admin

=====================================================
  CONTACT
=====================================================

Groupe 45 — Filière MGSI
MAHIR Rabia     : rabia.mahir@ensiasd.ma
ABLAD Mostapha  : ablad.m@ensiasd.ma
=====================================================
