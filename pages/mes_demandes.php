<?php
require_once '../config.php';
requireLogin();


$pageTitle = "Mes demandes d'échange";
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT e.*, l.titre
    FROM echanges e
    JOIN livres l ON e.livre_id = l.id
    WHERE e.demandeur_id = ?
    ORDER BY e.date_demande DESC
");
$stmt->execute([$user_id]);
$mes_demandes = $stmt->fetchAll();
$pdo->prepare("
    UPDATE echanges 
    SET vu_demandeur = 1 
    WHERE demandeur_id = ?
    AND statut IN ('accepte','refuse')
")->execute([$user_id]);

require_once '../includes/header.php';
?>

<div class="container">

    <h1>
        <i class="fa-solid fa-paper-plane"></i>
        Mes demandes d'échange
    </h1>

    <?php if (empty($mes_demandes)): ?>
        <p>Aucune demande d'échange pour le moment.</p>
    <?php else: ?>

        <?php foreach ($mes_demandes as $d): ?>
            <div class="echange-row">

                <p>
                    <i class="fa-solid fa-book"></i>
                    Livre: <strong><?= h($d['titre']) ?></strong>
                </p>

                <?php if ($d['statut'] == 'en_attente'): ?>
                    <p style="color:orange;">
                        <i class="fa-solid fa-clock"></i>
                        En attente de réponse
                    </p>

                <?php elseif ($d['statut'] == 'accepte'): ?>
                    <p style="color:green;">
                        <i class="fa-solid fa-circle-check"></i>
                        Demande acceptée
                    </p>

                <?php elseif ($d['statut'] == 'refuse'): ?>
                    <p style="color:red;">
                        <i class="fa-solid fa-circle-xmark"></i>
                        Demande refusée
                    </p>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
