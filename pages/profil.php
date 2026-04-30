<?php
// pages/profil.php — Page de profil utilisateur permettant de gérer les informations personnelles, la bibliothèque des livres publiés, les demandes d'échange reçues et envoyées, ainsi que les notations reçues et données entre utilisateurs dans la plateforme.
require_once '../config.php';
requireLogin();
$pageTitle = 'Mon profil';

$msg = '';
$user_id = $_SESSION['user_id'];


// Mise à jour du profil
$stmtNotif = $pdo->prepare("SELECT COUNT(*) FROM echanges WHERE proprio_id = ?     AND statut = 'en_attente' AND vu_proprio = 0");
$stmtNotif->execute([$user_id]);
$notifCount = $stmtNotif->fetchColumn();

// Mark notifications as read (owner + demandeur)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profil') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nom && $prenom && $email) {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $email, $user_id]);

            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;

            $msg = "Profil mis à jour avec succès.";
        }
    }

    // supprimer livre
    if ($action === 'supprimer_livre') {
        $liv_id = intval($_POST['livre_id']);
        $stmt = $pdo->prepare("DELETE FROM livres WHERE id=? AND user_id=?");
        $stmt->execute([$liv_id, $user_id]);

        $msg = "Livre supprimé.";
    }

    // répondre على échange
    if ($action === 'repondre_echange') {

        $echange_id = intval($_POST['echange_id'] ?? 0);
        $reponse = $_POST['reponse'] ?? '';
        $stmt = $pdo->prepare("
            UPDATE echanges 
            SET statut = ?, vu = 0 
            WHERE id = ? AND proprio_id = ?
        ");


        $stmt->execute([$reponse, $echange_id, $user_id]);
        $pdo->prepare("
            UPDATE echanges 
            SET vu_proprio = 0 
            WHERE id = ?
        ")->execute([$echange_id]);


        if ($echange_id && in_array($reponse, ['accepte', 'refuse'])) {

            $stmt = $pdo->prepare("SELECT livre_id FROM echanges WHERE id=? AND proprio_id=?");
            $stmt->execute([$echange_id, $user_id]);
            $livre_id = $stmt->fetchColumn();

            if ($livre_id) {

                $stmt = $pdo->prepare("UPDATE echanges SET statut=? WHERE id=?");
                $stmt->execute([$reponse, $echange_id]);

                // 3. mise a joure de livre->accepter
                if ($reponse === 'accepte') {

                    $stmt = $pdo->prepare("UPDATE livres SET statut='echange' WHERE id=?");
                    $stmt->execute([$livre_id]);

                }

                // 4. redirect
                header("Location: profil.php?msg=ok");
                exit;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profil') {
        $photo_name = null;
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($nom && $prenom && $email) {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $email, $user_id]);
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            $msg = 'Profil mis à jour avec succès.';
        }



        $uploadDir = '../uploads/users/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tmp = $_FILES['photo']['tmp_name'];
        $name = time() . '_' . basename($_FILES['photo']['name']);

        $path = $uploadDir . $name;

        if (move_uploaded_file($tmp, $path)) {
            $photo_name = 'uploads/users/' . $name;

            $stmt = $pdo->prepare("UPDATE utilisateurs SET photo=? WHERE id=?");
            $stmt->execute([$photo_name, $user_id]);
        }


    }

    if ($action === 'noter') {
        $note_id = intval($_POST['note_id'] ?? 0);
        $note = intval($_POST['note'] ?? 0);
        $comment = trim($_POST['commentaire'] ?? '');
        if ($note_id && $note >= 1 && $note <= 5) {
            // Vérifier qu'il n'a pas déjà noté
            $stmtC = $pdo->prepare("SELECT id FROM notations WHERE notateur_id=? AND note_id=?");
            $stmtC->execute([$user_id, $note_id]);
            if (!$stmtC->fetch()) {
                $stmtN = $pdo->prepare("INSERT INTO notations (notateur_id, note_id, note, commentaire) VALUES (?,?,?,?)");
                $stmtN->execute([$user_id, $note_id, $note, $comment]);
                $msg = 'Notation enregistrée !';
            } else {
                $msg = 'Vous avez déjà noté cet utilisateur.';
            }
        }
    }

    if ($action === 'supprimer_livre') {
        $liv_id = intval($_POST['livre_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM livres WHERE id=? AND user_id=?");
        $stmt->execute([$liv_id, $user_id]);
        $msg = 'Livre supprimé.';
    }
}

// Données utilisateur
$stmtU = $pdo->prepare("SELECT * FROM utilisateurs WHERE id=?");
$stmtU->execute([$user_id]);
$user = $stmtU->fetch();

// Mes livres
$stmtL = $pdo->prepare("SELECT * FROM livres WHERE user_id=? ORDER BY date_ajout DESC");
$stmtL->execute([$user_id]);
$mes_livres = $stmtL->fetchAll();

// Mes demandes d'échange
$stmtE = $pdo->prepare("SELECT e.*, l.titre, u.nom, u.prenom FROM echanges e JOIN livres l ON e.livre_id=l.id JOIN utilisateurs u ON e.proprio_id=u.id WHERE e.demandeur_id=? ORDER BY e.date_demande DESC");
$stmtE->execute([$user_id]);
$mes_demandes = $stmtE->fetchAll();

//transformer les demandes en  "vue"
$pdo->prepare("
    UPDATE echanges 
    SET vu = 1 
    WHERE proprio_id = ?
")->execute([$user_id]);

// Échanges reçus
$stmtR = $pdo->prepare("SELECT e.*, l.titre, u.nom, u.prenom FROM echanges e JOIN livres l ON e.livre_id=l.id JOIN utilisateurs u ON e.demandeur_id=u.id WHERE e.proprio_id=? ORDER BY e.date_demande DESC");
$stmtR->execute([$user_id]);
$demandes_recues = $stmtR->fetchAll();

// Mes notations reçues
$stmtNot = $pdo->prepare("SELECT n.*, u.nom, u.prenom FROM notations n JOIN utilisateurs u ON n.notateur_id=u.id WHERE n.note_id=?");
$stmtNot->execute([$user_id]);
$mes_notations = $stmtNot->fetchAll();



require_once '../includes/header.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-user"></i> Mon profil</h1>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>

    <div class="profil-layout">

        <!-- Infos profil -->
        <div class="profil-sidebar">
            <div class="profil-card">
                <div class="profil-avatar">
                    <img src="<?= !empty($user['photo']) && file_exists('../' . $user['photo'])
                        ? '../' . $user['photo']
                        : '../images/default-user1.jfif' ?>" alt="profil">

                </div>

                <h2><?= h($user['prenom'] . ' ' . $user['nom']) ?></h2>
                <p class="profil-login">@<?= h($user['login']) ?></p>
                <p class="profil-email"><?= h($user['email']) ?></p>
                <p>Membre depuis <?= date('d/m/Y', strtotime($user['date_inscription'])) ?></p>
            </div>

            <!-- Modifier profil -->
            <div class="profil-card">
                <h3>Modifier mes informations</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profil">
                    <div class="form-group">
                        <label>Photo de profil</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?= h($user['nom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="prenom" value="<?= h($user['prenom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= h($user['email']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">Mettre à jour</button>
                </form>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="profil-main">

            <!-- Mes livres -->
            <div class="profil-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-book"></i> Ma bibliothèque (<?= count($mes_livres) ?> livre(s))</h2>
                    <a href="ajouter_livre.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Ajouter
                    </a>
                </div>
                <?php if (empty($mes_livres)): ?>
                    <p class="empty-msg">Vous n'avez publié aucun livre.</p>
                <?php else: ?>
                    <div class="profil-livres">
                        <?php foreach ($mes_livres as $l): ?>
                            <div class="profil-livre-row">
                                <div>
                                    <strong><?= h($l['titre']) ?></strong>
                                    <span class="badge-etat badge-<?= $l['etat'] ?>"><?= ucfirst($l['etat']) ?></span>
                                    <span class="badge-statut badge-<?= $l['statut'] ?>"><?= ucfirst($l['statut']) ?></span>
                                </div>
                                <div class="livre-actions">
                                    <a href="modifier_livre.php?id=<?= $l['id'] ?>" class="btn btn-sm">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>


                                    <form method="POST" style="display:inline"
                                          onsubmit="return confirm('Supprimer ce livre ?')">
                                        <input type="hidden" name="action" value="supprimer_livre">
                                        <input type="hidden" name="livre_id" value="<?= $l['id'] ?>">
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-trash"></i> Supprimer
                                        </button>

                                    </form>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="alert alert-info">
                <i class="fa-solid fa-bell"></i>
                Vous avez <?= $notifCount ?> nouvelle(s) demande(s)
            </div>


            <!-- Demandes reçues -->
            <?php if (!empty($demandes_recues)): ?>
                <div class="profil-section">
                    <h2><i class="fa-solid fa-bell"></i> Demandes d'échange reçues</h2>

                    <?php foreach ($demandes_recues as $d): ?>
                        <div class="echange-row">
                            <p><strong><?= h($d['prenom'] . ' ' . $d['nom']) ?></strong> souhaite votre livre :
                                <em><?= h($d['titre']) ?></em></p>
                            <p>
                                Statut :
                                <span class="badge badge-<?= $d['statut'] ?>">
                             <?= $d['statut'] ?>
                             </span>
                            </p>
                            <form method="POST" style="display:flex;gap:8px">
                                <input type="hidden" name="action" value="repondre_echange">
                                <input type="hidden" name="echange_id" value="<?= $d['id'] ?>">
                                <button type="submit" name="reponse" value="accepte" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-check"></i> Accepter
                                </button>

                                <button type="submit" name="reponse" value="refuse" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-xmark"></i> Refuser
                                </button>


                            </form>
                        </div>
                    <?php endforeach; ?>


                </div>
            <?php endif; ?>

            <!-- Mes notations reçues -->
            <div class="profil-section">
                <h2><i class="fa-solid fa-star"></i> Mes notations reçues</h2>
                <?php if (empty($mes_notations)): ?>
                    <p class="empty-msg">Aucune notation reçue pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($mes_notations as $n): ?>
                        <div class="notation-item">
                            <span><?= str_repeat('⭐', $n['note']) . str_repeat('☆', 5 - $n['note']) ?></span>
                            <p><?= h($n['commentaire']) ?></p>
                            <small>— <?= h($n['prenom'] . ' ' . $n['nom']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>


</div>

<?php require_once '../includes/footer.php'; ?>





