<?php
// index.php — Page d'accueil de la plateforme
require_once 'config.php';
$pageTitle = 'Accueil';

// Récupérer les derniers livres disponibles
$stmt = $pdo->query("
    SELECT l.*, u.nom, u.prenom
    FROM livres l
    JOIN utilisateurs u ON l.user_id = u.id
    WHERE l.statut = 'disponible'
    ORDER BY l.date_ajout DESC
    LIMIT 6
");
$derniers_livres = $stmt->fetchAll();

// Statistiques
$stats = [];
$stats['livres']      = $pdo->query("SELECT COUNT(*) FROM livres WHERE statut='disponible'")->fetchColumn();
$stats['etudiants']   = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='etudiant'")->fetchColumn();
$stats['echanges']    = $pdo->query("SELECT COUNT(*) FROM echanges WHERE statut='termine'")->fetchColumn();

require_once 'includes/header.php';
?>

<!-- Section Hero -->
<section class="hero">
    <div class="hero-content">
        <h1> Échangez vos livres universitaires</h1>
        <p>Retrouvez des livres de cours, proposez les vôtres, et organisez des échanges avec vos camarades.</p>
        <?php if (!estConnecte()): ?>
            <div class="hero-btns">
                <a href="pages/register.php" class="btn btn-primary">Rejoindre la plateforme</a>
                <a href="pages/livres.php" class="btn btn-secondary">Voir les livres</a>
            </div>
        <?php else: ?>
            <div class="hero-btns">
                <a href="pages/ajouter_livre.php" class="btn btn-primary">+ Publier un livre</a>
                <a href="pages/livres.php" class="btn btn-secondary">Parcourir les livres</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Statistiques -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?= $stats['livres'] ?></span>
                <span class="stat-label">Livres disponibles</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= $stats['etudiants'] ?></span>
                <span class="stat-label">Étudiants inscrits</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= $stats['echanges'] ?></span>
                <span class="stat-label">Échanges réalisés</span>
            </div>
        </div>
    </div>
</section>

<!-- Derniers livres -->
<section class="livres-section">
    <div class="container">
        <h2 class="title-gradient">Dernières annonces</h2>
        <?php if (empty($derniers_livres)): ?>
            <p class="empty-msg">Aucun livre disponible pour le moment.</p>
        <?php else: ?>
        <div class="livres-grid">
            <?php foreach ($derniers_livres as $livre): ?>
            <div class="livre-card">
                <div class="livre-img">
                    <img src="<?= !empty($livre['image']) ? h($livre['image']) : '/plateforme_livres_MGSI/images/default-book.jfif' ?>" alt="livre">
                </div>

                <div class="livre-info">
                    <h3><?= h($livre['titre']) ?></h3>
                    <p class="livre-auteur"><?= h($livre['auteur']) ?></p>
                    <span class="badge-matiere"><?= h($livre['matiere']) ?></span>
                    <span class="badge-etat badge-<?= h($livre['etat']) ?>"><?= ucfirst(h($livre['etat'])) ?></span>
                    <p class="livre-proprio">Par <?= h($livre['prenom']) . ' ' . h($livre['nom']) ?></p>
                    <a href="pages/livre_detail.php?id=<?= $livre['id'] ?>" class="btn livre-btn btn-sm ">Voir le livre</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="pages/livres.php" class="btn livre-btn btn-secondary">Voir tous les livres →</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
