<?php
// pages/register.php — Page d'inscription permettant la création d'un nouveau compte utilisateur avec vérification des champs, validation des données, contrôle d'unicité du login et de l'email, et stockage sécurisé du mot de passe (hash).
require_once '../config.php';
$pageTitle = 'Inscription';

if (estConnecte()) redirect('index.php');

$erreur  = '';
$succes  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login  = trim($_POST['login']  ?? '');
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $mdp    = $_POST['mot_de_passe'] ?? '';
    $mdp2   = $_POST['mot_de_passe2'] ?? '';

    if (empty($login) || empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif ($mdp !== $mdp2) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($mdp) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Vérifier si login ou email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? OR email = ?");
        $stmt->execute([$login, $email]);
        if ($stmt->fetch()) {
            $erreur = 'Ce login ou cet email est déjà utilisé.';
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, mot_de_passe, nom, prenom, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$login, $hash, $nom, $prenom, $email]);
            $succes = 'Compte créé avec succès ! Vous pouvez vous connecter.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <h1 class="title-gradient">Créer un compte</h1>

        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= h($erreur) ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="alert alert-success"><?= h($succes) ?>
                <br><a href="login.php">→ Se connecter</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= h($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= h($_POST['prenom'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="login">Login (identifiant unique)</label>
                <input type="text" id="login" name="login" value="<?= h($_POST['login'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe (min. 6 caractères)</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe2">Confirmer le mot de passe</label>
                <input type="password" id="mot_de_passe2" name="mot_de_passe2" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Créer mon compte</button>
        </form>

        <p class="form-footer">
            Déjà inscrit ? <a href="login.php">Se connecter</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
