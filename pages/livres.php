<?php
// pages/livres.php — Liste des livres avec système de recherche et filtrage
// Cette page affiche tous les livres disponibles sur la plateforme.
// Elle permet à l’utilisateur de rechercher un livre par titre ou auteur,
// et de filtrer les résultats par matière et état du livre.
// La requête SQL est construite dynamiquement selon les filtres choisis,
// puis exécutée de manière sécurisée avec PDO (requêtes préparées).
// La page récupère également les matières distinctes pour alimenter le filtre.
// Chaque livre est affiché sous forme de carte avec ses informations principales
// et un lien vers la page de détail du livre.
require_once '../config.php';
$pageTitle = 'Livres disponibles';

// Paramètres de recherche
$search = trim($_GET['search'] ?? '');
$matiere = trim($_GET['matiere'] ?? '');
$etat = trim($_GET['etat'] ?? '');

// Construction de la requête avec filtres
$sql = "SELECT l.*, u.nom, u.prenom FROM livres l JOIN utilisateurs u ON l.user_id = u.id WHERE l.statut = 'disponible'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (l.titre LIKE ? OR l.auteur LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($matiere)) {
    $sql .= " AND l.matiere = ?";
    $params[] = $matiere;
}
if (!empty($etat)) {
    $sql .= " AND l.etat = ?";
    $params[] = $etat;
}
$sql .= " ORDER BY l.date_ajout DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll();

// Récupérer les matières distinctes pour le filtre
$matieres = $pdo->query("SELECT DISTINCT matiere FROM livres ORDER BY matiere")->fetchAll(PDO::FETCH_COLUMN);

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1> Livres disponibles</h1>
        <?php if (estConnecte()): ?>
            <a href="ajouter_livre.php" class="btn btn-primary">+ Publier un livre</a>
        <?php endif; ?>
    </div>

    <!-- Formulaire de recherche -->
    <form method="GET" action="livres.php" class="search-form">
        <div class="search-row">
            <input type="text" name="search" placeholder="Rechercher par titre ou auteur..."
                   value="<?= h($search) ?>" class="search-input">
            <select name="matiere">
                <option value="">Toutes les matières</option>
                <?php foreach ($matieres as $m): ?>
                    <option value="<?= h($m) ?>" <?= $matiere === $m ? 'selected' : '' ?>><?= h($m) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="etat">
                <option value="">Tous les états</option>
                <option value="neuf" <?= $etat === 'neuf' ? 'selected' : '' ?>>Neuf</option>
                <option value="bon" <?= $etat === 'bon' ? 'selected' : '' ?>>Bon</option>
                <option value="acceptable" <?= $etat === 'acceptable' ? 'selected' : '' ?>>Acceptable</option>
                <option value="use" <?= $etat === 'use' ? 'selected' : '' ?>>Usé</option>
            </select>
            <button type="submit" class="btn btn-primary">Rechercher</button>
            <?php if ($search || $matiere || $etat): ?>
                <a href="livres.php" class="btn btn-secondary">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </form>

    <p class="results-count"><?= count($livres) ?> livre(s) trouvé(s)</p>

    <?php if (empty($livres)): ?>
        <div class="empty-state">
            <p>Aucun livre ne correspond à votre recherche.</p>
        </div>
    <?php else: ?>
        <div class="livres-grid">
            <?php foreach ($livres as $livre): ?>
                <div class="livre-card">
                    <div class="livre-img">
                        <img src="<?= !empty($livre['image']) ? '../' . h($livre['image']) : '../images/default-book.jfif' ?>"
                             alt="livre">
                    </div>
                    <div class="livre-info">
                        <h3><?= h($livre['titre']) ?></h3>
                        <p class="livre-auteur">
                            <i class="fa-solid fa-pen-nib"></i> <?= h($livre['auteur']) ?>
                        </p>
                        <div class="livre-badges">
                            <span class="badge-matiere"><?= h($livre['matiere']) ?></span>
                            <span class="badge-etat badge-<?= $livre['etat'] ?>"><?= ucfirst($livre['etat']) ?></span>
                        </div>
                        <p class="livre-desc"><?= h(substr($livre['description'], 0, 80)) ?>...</p>
                        <p class="livre-proprio">
                            <i class="fa-solid fa-user"></i> <?= h($livre['prenom'] . ' ' . $livre['nom']) ?>
                        </p>
                        <a href="livre_detail.php?id=<?= $livre['id'] ?>" class="btn livre-btn btn-sm">Voir le
                            détail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
