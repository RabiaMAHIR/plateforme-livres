<?php
// ===================================================
// config.php — Connexion PDO + constantes globales
// Inclure ce fichier dans toutes les pages
// ===================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'plateforme_livres');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');
define('SITE_NAME',   'LibreChange');
define('BASE_URL',    'http://localhost/plateforme_livres_MGSI/');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('<div style="color:red;padding:20px;font-family:Arial;">
         Erreur de connexion à la base de données : ' . $e->getMessage() . '
         </div>');
}

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonctions utilitaires globales

/**
 * Nettoyer l'affichage contre les attaques XSS
 */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Rediriger vers une URL
 */
function redirect($url) {
    header('Location: ' . BASE_URL . $url);
    exit;
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function estConnecte() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est admin
 */
function estAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Protéger une page : redirige vers login si non connecté
 */
function requireLogin() {
    if (!estConnecte()) {
        redirect('pages/login.php');
    }
}

/**
 * Protéger une page admin
 */
function requireAdmin() {
    requireLogin();
    if (!estAdmin()) {
        redirect('index.php');
    }
}
?>
