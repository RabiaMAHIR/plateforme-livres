<?php
// pages/modifier_livre.php — Modifier une annonce de livre
require_once '../config.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('pages/livres.php');

$stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$id]);
$livre = $stmt->fetch();

if (!$livre || $livre['user_id'] !== $_SESSION['user_id']) redirect('pages/livres.php');

$pageTitle = 'Modifier : ' . $livre['titre'];
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');
    $matiere = trim($_POST['matiere'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $etat = $_POST['etat'] ?? 'bon';
    $statut = $_POST['statut'] ?? 'disponible';
    $image_name = $livre['image'];
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
        $stmt = $pdo->prepare("UPDATE livres SET titre=?, auteur=?, matiere=?, description=?, etat=?, statut=?,image=?  WHERE id=? AND user_id=?");
        $stmt->execute([$titre, $auteur, $matiere, $description, $etat, $statut, $image_name, $id, $_SESSION['user_id']]);
        redirect('pages/livre_detail.php?id=' . $id);
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <h1 class="title-gradient">️ Modifier le livre</h1>

        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= h($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="modifier_livre.php?id=<?= $id ?>">
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre"
                       value="<?= h($_POST['titre'] ?? $livre['titre']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="auteur">Auteur *</label>
                    <input type="text" id="auteur" name="auteur"
                           value="<?= h($_POST['auteur'] ?? $livre['auteur']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="matiere">Matière *</label>
                    <input type="text" id="matiere" name="matiere"
                           value="<?= h($_POST['matiere'] ?? $livre['matiere']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          rows="4"><?= h($_POST['description'] ?? $livre['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="etat">État</label>
                    <select id="etat" name="etat">
                        <?php foreach (['neuf' => 'Neuf', 'bon' => 'Bon état', 'acceptable' => 'Acceptable', 'use' => 'Usé'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($livre['etat'] == $v) ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <?php foreach (['disponible' => 'Disponible', 'echange' => 'En échange', 'archive' => 'Archivé'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($livre['statut'] == $v) ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="image">Modifier l'image</label>
                <input type="file" id="image" name="image" accept="image/*">

                <div class="image-preview">
                    <img id="preview"
                         src="<?= !empty($livre['image']) ? '../' . $livre['image'] : '../images/default-book.jfif' ?>"
                         alt="Preview">
                </div>
            </div>

            <div class="form-btns">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="livre_detail.php?id=<?= $id ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
