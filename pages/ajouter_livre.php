<?php
// pages/ajouter_livre.php — Ajouter une annonce de livre
// Cette page permet à un utilisateur connecté de publier un nouveau livre sur la plateforme.
// Elle gère la récupération des données du formulaire (titre, auteur, matière, description, état),
// ainsi que l’upload optionnel de l’image du livre.
// Les données sont ensuite insérées dans la base de données avec une requête préparée (PDO)
// afin de garantir la sécurité contre les injections SQL.
// L’accès à cette page est protégé et nécessite une authentification (requireLogin()).
require_once '../config.php';
requireLogin();
$pageTitle = 'Publier un livre';

$erreur = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');
    $matiere = trim($_POST['matiere'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $etat = $_POST['etat'] ?? 'bon';
    $image_name = null;

    if (!empty($_FILES['image']['name'])) {

        $uploadDir = '../uploads/livres/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tmp = $_FILES['image']['tmp_name'];
        $name = time() . '_' . basename($_FILES['image']['name']);
        $path = $uploadDir . $name;

        if (move_uploaded_file($tmp, $path)) {
            $image_name = 'uploads/livres/' . $name;
        }
    }

    if (empty($titre) || empty($auteur) || empty($matiere)) {
        $erreur = 'Titre, auteur et matière sont obligatoires.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO livres (titre, auteur, matiere, description, etat,image, user_id) VALUES (?, ?, ?, ?, ?,?, ?)");
        $stmt->execute([$titre, $auteur, $matiere, $description, $etat, $image_name, $_SESSION['user_id']]);
        redirect('pages/livres.php');
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <h1 class="title-gradient">Publier un livre</h1>
        <p class="form-subtitle">Proposez votre livre à la communauté</p>

        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= h($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="ajouter_livre.php">
            <div class="form-group">
                <label for="titre">Titre du livre *</label>
                <input type="text" id="titre" name="titre"
                       value="<?= h($_POST['titre'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="auteur">Auteur *</label>
                    <input type="text" id="auteur" name="auteur"
                           value="<?= h($_POST['auteur'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="matiere">Matière *</label>
                    <input type="text" id="matiere" name="matiere"
                           value="<?= h($_POST['matiere'] ?? '') ?>"
                           placeholder="ex: Informatique, Mathématiques..." required>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"
                          placeholder="Décrivez l'état du livre, son contenu..."><?= h($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Image du livre</label>
                <input type="file" id="image" name="image" accept="image/*">

                <div class="image-preview">
                    <img id="preview" src="../images/default-book.jfif" alt="Preview">
                </div>
            </div>


            <div class="form-group">
                <label for="etat">État du livre *</label>
                <select id="etat" name="etat">
                    <option value="neuf" <?= ($_POST['etat'] ?? '') === 'neuf' ? 'selected' : '' ?>>Neuf</option>
                    <option value="bon" <?= ($_POST['etat'] ?? 'bon') === 'bon' ? 'selected' : '' ?>>Bon état</option>
                    <option value="acceptable" <?= ($_POST['etat'] ?? '') === 'acceptable' ? 'selected' : '' ?>>
                        Acceptable
                    </option>
                    <option value="use" <?= ($_POST['etat'] ?? '') === 'use' ? 'selected' : '' ?>>Usé</option>
                </select>
            </div>
            <div class="form-btns">
                <button type="submit" class="btn btn-primary">Publier le livre</button>
                <a href="livres.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
