<?php
// pages/logout.php — Déconnexion
require_once '../config.php';
session_destroy();
header('Location: ' . BASE_URL . 'index.php');
exit;
?>
