<?php
// pages/livre_detail.php — Détail d’un livre + demande d’échange + notation
// Cette page affiche toutes les informations détaillées d’un livre sélectionné.
// Elle permet à l’utilisateur connecté de consulter les détails du livre,
// d’envoyer une demande d’échange au propriétaire, d’envoyer un message,
// et de noter le propriétaire après interaction.
// Elle gère aussi l’affichage des notations et du score moyen du propriétaire.
// L’accès aux actions (échange, notation) est protégé par la vérification de connexion (estConnecte())
// et des contrôles empêchent les actions sur son propre livre ou les doublons de demande.
require_once '../config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('livres.php');

$stmt = $pdo->prepare("SELECT l.*, u.id AS user_id, u.nom, u.prenom, u.email,u.photo, l.image FROM livres l JOIN utilisateurs u ON l.user_id = u.id WHERE l.id = ?");
$stmt->execute([$id]);
$livre = $stmt->fetch();
$photo = !empty($livre['photo'])
    ? '../' . $livre['photo']
    : '../images/default-user1.jfif';
$bookImage = !empty($livre['image'])
    ? '../' . $livre['image']
    : '../images/default-book.jfif';


if (!$livre) redirect('livres.php');

$pageTitle = h($livre['titre']);

// Traitement de la demande d'échange
$msg_succes = '';
$msg_erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && estConnecte()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'demande_echange') {
        if ($_SESSION['user_id'] == $livre['user_id']) {
            $msg_erreur = 'Vous ne pouvez pas demander l\'échange de votre propre livre.';
        } else {
            // Vérifier si une demande existe déjà
            $stmtCheck = $pdo->prepare("SELECT id FROM echanges WHERE demandeur_id = ? AND livre_id = ? AND statut = 'en_attente'");
            $stmtCheck->execute([$_SESSION['user_id'], $id]);
            if ($stmtCheck->fetch()) {
                $msg_erreur = 'Vous avez déjà une demande en cours pour ce livre.';
            } else {
                $stmtE = $pdo->prepare("
             INSERT INTO echanges 
            (demandeur_id, proprio_id, livre_id, statut, vu_demandeur, vu_proprio)
            VALUES (?, ?, ?, 'en_attente', 0, 0)
                   ");

                $stmtE->execute([$_SESSION['user_id'], $livre['user_id'], $id]);
                $msg_succes = 'Votre demande d\'échange a été envoyée !';
            }
        }
    }
    if ($action === 'noter') {

        $notateur_id = $_SESSION['user_id'];
        $note_id = intval($_POST['note_id'] ?? 0);
        $note = intval($_POST['note'] ?? 0);
        $comment = trim($_POST['commentaire'] ?? '');

        if ($note_id && $note >= 1 && $note <= 5) {

            $stmt = $pdo->prepare("
            SELECT id FROM notations 
            WHERE notateur_id=? AND note_id=?
        ");
            $stmt->execute([$notateur_id, $note_id]);

            if (!$stmt->fetch()) {

                $stmt = $pdo->prepare("
                INSERT INTO notations (notateur_id, note_id, note, commentaire)
                VALUES (?,?,?,?)
            ");
                $stmt->execute([$notateur_id, $note_id, $note, $comment]);

                $msg_succes = "Notation enregistrée !";

            } else {
                $msg_erreur = "Vous avez déjà noté cet utilisateur.";
            }
        }
    }

}

// Notations du propriétaire
$stmtNot = $pdo->prepare("SELECT n.*, u.nom, u.prenom FROM notations n JOIN utilisateurs u ON n.notateur_id = u.id WHERE n.note_id = ? ORDER BY n.date_notation DESC");
$stmtNot->execute([$livre['user_id']]);
$notations = $stmtNot->fetchAll();

$note_moy = 0;
if (count($notations)) {
    $note_moy = round(array_sum(array_column($notations, 'note')) / count($notations), 1);
}

require_once '../includes/header.php';
?>

<div class="container">
    <a href="livres.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour aux livres
    </a>

    <?php if ($msg_succes): ?>
        <div class="alert alert-success"><?= h($msg_succes) ?></div><?php endif; ?>
    <?php if ($msg_erreur): ?>
        <div class="alert alert-error"><?= h($msg_erreur) ?></div><?php endif; ?>

    <div class="detail-grid">
        <!-- Livre -->
        <div class="detail-main">
            <div class="livre-detail-img">
                <img src="<?= h($bookImage) ?>" alt="Livre">
            </div>
            <h1><?= h($livre['titre']) ?></h1>
            <p class="auteur-detail">par <?= h($livre['auteur']) ?></p>

            <div class="detail-badges">
                <span class="badge-matiere"><?= h($livre['matiere']) ?></span>
                <span class="badge-etat badge-<?= $livre['etat'] ?>"><?= ucfirst($livre['etat']) ?></span>
                <span class="badge-statut badge-<?= $livre['statut'] ?>"><?= ucfirst($livre['statut']) ?></span>
            </div>



            <div class="detail-section">
                <h3>Informations</h3>

                <div class="info-card">
                    <div class="info-item">
                        <i class="fa-solid fa-book"></i>
                        <div>
                            <span class="info-label">Matière</span>
                            <span class="info-value"><?= h($livre['matiere']) ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-layer-group"></i>
                        <div>
                            <span class="info-label">État</span>
                            <span class="info-value"><?= ucfirst(h($livre['etat'])) ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-calendar-days"></i>
                        <div>
                            <span class="info-label">Publié le</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($livre['date_ajout'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="detail-section">
                <h3>Description</h3>
                <p><?= h($livre['description']) ?></p>
            </div>

        </div>

        <!-- Sidebar propriétaire -->
        <div class="detail-sidebar">
            <div class="proprio-card">
                <h3>Proposé par</h3>
                <div class="proprio-avatar">
                    <img src="<?= h($photo) ?>" alt="Utilisateur">
                </div>
                <p class="proprio-name"><?= h($livre['prenom'] . ' ' . $livre['nom']) ?></p>
                <?php if ($note_moy > 0): ?>
                    <p class="note-moy"><i class="fa-solid fa-star"></i> <?= $note_moy ?>/5 (<?= count($notations) ?>
                        avis)</p>
                <?php endif; ?>

                <?php if (estConnecte() && $_SESSION['user_id'] != $livre['user_id']): ?>
                    <?php if ($livre['statut'] === 'disponible'): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="demande_echange">
                            <button type="submit" class="btn btn-primary btn-full">
                                <i class="fa-solid fa-right-left"></i> Demander l'échange

                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="messages.php?destinataire=<?= $livre['user_id'] ?>&livre=<?= $livre['id'] ?>"
                       class="btn btn-secondary btn-full" style="margin-top:8px">
                        <i class="fa-solid fa-paper-plane"></i> Envoyer un message
                    </a>
                <?php elseif (!estConnecte()): ?>
                    <a href="login.php" class="btn btn-primary btn-full">Connectez-vous pour échanger</a>
                <?php else: ?>
                    <p class="your-book">
                        <i class="fa-solid fa-thumbtack"></i> C'est votre livre
                    </p>
                    <a href="modifier_livre.php?id=<?= $livre['id'] ?>" class="btn btn-secondary btn-full">Modifier</a>
                <?php endif; ?>
            </div>
            <?php if (estConnecte() && $_SESSION['user_id'] != $livre['user_id']): ?>

                <form method="POST" class="rating-form">
                    <input type="hidden" name="action" value="noter">
                    <input type="hidden" name="note_id" value="<?= $livre['user_id'] ?>">
                    <input type="hidden" name="note" id="note-value">

                    <div class="stars-input">
                        <i class="fa-regular fa-star star" data-value="1"></i>
                        <i class="fa-regular fa-star star" data-value="2"></i>
                        <i class="fa-regular fa-star star" data-value="3"></i>
                        <i class="fa-regular fa-star star" data-value="4"></i>
                        <i class="fa-regular fa-star star" data-value="5"></i>
                    </div>

                    <input type="text" name="commentaire" placeholder="Commentaire">

                    <button type="submit" class="btn btn-primary">
                        Noter
                    </button>
                </form>

            <?php endif; ?>

            <!-- Notations -->
            <?php if (!empty($notations)): ?>
                <div class="notations-card">
                    <h3>Avis sur ce membre</h3>
                    <?php foreach ($notations as $not): ?>
                        <div class="notation-item">
                            <div class="notation-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-<?= $i <= $not['note'] ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="notation-comment"><?= h($not['commentaire']) ?></p>
                            <p class="notation-auteur">— <?= h($not['prenom'] . ' ' . $not['nom']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
