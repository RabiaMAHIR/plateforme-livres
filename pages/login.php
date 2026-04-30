<?php
// pages/login.php — Page de connexion permettant l’authentification des utilisateurs via login et mot de passe, avec vérification sécurisée des informations (password_verify), création de session utilisateur et redirection en cas de connexion réussie.
require_once '../config.php';
$pageTitle = 'Connexion';

// Déjà connecté → redirection
if (estConnecte()) redirect('index.php');

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    if (empty($login) || empty($mdp)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        // Recherche de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $user['login'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['role'] = $user['role'];
            redirect('index.php');
        } else {
            $erreur = 'Login ou mot de passe incorrect.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <h1 class="title-gradient">Connexion</h1>

        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= h($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login"
                       value="<?= h($_POST['login'] ?? '') ?>"
                       placeholder="Votre identifiant" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       placeholder="Votre mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
        </form>

        <p class="form-footer">
            Pas encore inscrit ? <a href="register.php">Créer un compte</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
