<?php
// fix_admin_password.php
// Ce fichier règle le mot de passe admin ENSIASD2026
// SUPPRIMER CE FICHIER APRÈS UTILISATION !

require_once 'config.php';

$login   = 'ENSIASD';
$new_mdp = 'ENSIASD2026';
$hash    = password_hash($new_mdp, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE login = ?");
$stmt->execute([$hash, $login]);

if ($stmt->rowCount() > 0) {
    echo '<div style="font-family:Arial;padding:30px;max-width:500px;margin:40px auto;background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;">';
    echo '<h2 style="color:#155724;"> Mot de passe mis à jour !</h2>';
    echo '<p>Le mot de passe du compte <strong>ENSIASD</strong> a été réinitialisé.</p>';
    echo '<p>Login : <strong>ENSIASD</strong></p>';
    echo '<p>Mot de passe : <strong>ENSIASD2026</strong></p>';
    echo '<p style="margin-top:16px;color:#721c24;"><strong>⚠ Supprimez ce fichier maintenant !</strong></p>';
    echo '<a href="index.php" style="display:inline-block;margin-top:12px;padding:8px 16px;background:#155724;color:white;border-radius:5px;text-decoration:none;">→ Aller à l\'accueil</a>';
    echo '</div>';
} else {
    echo '<div style="font-family:Arial;padding:30px;max-width:500px;margin:40px auto;background:#f8d7da;border:1px solid #f5c6cb;border-radius:8px;">';
    echo '<h2 style="color:#721c24;"> Erreur</h2>';
    echo '<p>Compte ENSIASD introuvable. Vérifiez que la base de données est bien importée.</p>';
    echo '</div>';
}
?>
