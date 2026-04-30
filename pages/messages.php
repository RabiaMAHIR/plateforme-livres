<?php
// pages/messages.php — Messagerie entre étudiants
require_once '../config.php';
requireLogin();
$pageTitle = 'Messagerie';

$msg_succes = '';
$destinataire_id = intval($_GET['destinataire'] ?? 0);
$livre_id = intval($_GET['livre'] ?? 0);

// Envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dest_id = intval($_POST['destinataire_id'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $liv_id = intval($_POST['livre_id'] ?? 0);

    if ($dest_id && !empty($contenu)) {
        $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, livre_id, contenu) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $dest_id, $liv_id ?: null, $contenu]);
        $msg_succes = 'Message envoyé avec succès !';
        $destinataire_id = $dest_id;
    }
}

// Récupérer les conversations (liste des contacts)
$stmtConv = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom,
           COUNT(CASE WHEN m.lu=0 AND m.destinataire_id=? THEN 1 END) AS non_lus,
           MAX(m.date_envoi) AS dernier_msg
    FROM messages m
    JOIN utilisateurs u ON (u.id = CASE WHEN m.expediteur_id=? THEN m.destinataire_id ELSE m.expediteur_id END)
    WHERE m.expediteur_id=? OR m.destinataire_id=?
    GROUP BY u.id ORDER BY dernier_msg DESC
");
$stmtConv->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$conversations = $stmtConv->fetchAll();

// Messages avec un interlocuteur sélectionné
$messages_conv = [];
$interlocuteur = null;

if ($destinataire_id) {
    $stmtU = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmtU->execute([$destinataire_id]);
    $interlocuteur = $stmtU->fetch();

    $stmtM = $pdo->prepare("
        SELECT m.*, u.nom, u.prenom FROM messages m
        JOIN utilisateurs u ON u.id = m.expediteur_id
        WHERE (m.expediteur_id=? AND m.destinataire_id=?)
           OR (m.expediteur_id=? AND m.destinataire_id=?)
        ORDER BY m.date_envoi ASC
    ");
    $stmtM->execute([$_SESSION['user_id'], $destinataire_id, $destinataire_id, $_SESSION['user_id']]);
    $messages_conv = $stmtM->fetchAll();

    // Marquer comme lus
    $pdo->prepare("UPDATE messages SET lu=1 WHERE destinataire_id=? AND expediteur_id=?")->execute([$_SESSION['user_id'], $destinataire_id]);
}

// Liste de tous les étudiants pour nouveau message
$stmtEtud = $pdo->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE id != ? AND role='etudiant' ORDER BY nom");
$stmtEtud->execute([$_SESSION['user_id']]);
$etudiants = $stmtEtud->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-comments"></i> Messagerie</h1>
    <?php if ($msg_succes): ?>
        <div class="alert alert-success"><?= h($msg_succes) ?></div><?php endif; ?>

    <div class="messagerie-layout">

        <!-- Sidebar conversations -->
        <div class="conv-sidebar">
            <h3>Conversations</h3>
            <?php if (empty($conversations)): ?>
                <p class="empty-msg">Aucune conversation.</p>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="messages.php?destinataire=<?= $conv['id'] ?>"
                       class="conv-item <?= $destinataire_id == $conv['id'] ? 'active' : '' ?>">
                        <span class="conv-name"><?= h($conv['prenom'] . ' ' . $conv['nom']) ?></span>
                        <?php if ($conv['non_lus'] > 0): ?>
                            <span class="badge"><?= $conv['non_lus'] ?></span>
                        <?php endif; ?>
                    </a>

                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Nouveau message -->
            <div class="new-msg-section">
                <h3>Nouveau message</h3>
                <form method="POST" action="messages.php">
                    <select name="destinataire_id" required>
                        <option value="">Choisir un étudiant...</option>
                        <?php foreach ($etudiants as $et): ?>
                            <option value="<?= $et['id'] ?>" <?= $destinataire_id == $et['id'] ? 'selected' : '' ?>>
                                <?= h($et['prenom'] . ' ' . $et['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="contenu" rows="3" placeholder="Votre message..." required></textarea>
                    <input type="hidden" name="livre_id" value="<?= $livre_id ?>">
                    <button type="submit" class="btn btn-primary btn-full">Envoyer</button>
                </form>
            </div>
        </div>

        <!-- Zone de messages -->
        <div class="messages-zone">
            <?php if ($interlocuteur): ?>
                <div class="messages-header">
                    <h3>Conversation avec <?= h($interlocuteur['prenom'] . ' ' . $interlocuteur['nom']) ?></h3>
                </div>
                <div class="messages-list">
                    <?php if (empty($messages_conv)): ?>
                        <p class="empty-msg">Aucun message. Commencez la conversation !</p>
                    <?php else: ?>
                        <?php foreach ($messages_conv as $m): ?>
                            <div class="message-bubble <?= $m['expediteur_id'] == $_SESSION['user_id'] ? 'sent' : 'received' ?>">
                                <p class="msg-content"><?= h($m['contenu']) ?></p>
                                <span class="msg-time"><?= date('d/m/Y H:i', strtotime($m['date_envoi'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <form method="POST" action="messages.php?destinataire=<?= $destinataire_id ?>" class="reply-form">
                    <input type="hidden" name="destinataire_id" value="<?= $destinataire_id ?>">
                    <textarea name="contenu" rows="2" placeholder="Répondre..." required></textarea>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
            <?php else: ?>
                <div class="no-conv">
                    <p>Sélectionnez une conversation ou envoyez un nouveau message.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
