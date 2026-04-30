<?php
// pages/admin.php — Tableau de bord administrateur
// Cette page représente l’espace d’administration de la plateforme LibreChange.
// Elle permet à l’administrateur de gérer les utilisateurs (ajout, suppression, changement de rôle),
// de gérer les livres (suppression), et de consulter les statistiques globales de la plateforme.
// L’accès à cette page est strictement réservé aux utilisateurs ayant le rôle "admin" via la fonction requireAdmin().
// Toutes les actions sont sécurisées et exécutées via des requêtes préparées (PDO) afin de protéger contre les injections SQL.


require_once '../config.php';
requireAdmin();
$pageTitle = 'Administration';

$msg = '';

// Actions admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'supprimer_user') {
        $uid = intval($_POST['user_id'] ?? 0);
        if ($uid && $uid != $_SESSION['user_id']) {
            $pdo->prepare("DELETE FROM utilisateurs WHERE id=?")->execute([$uid]);
            $msg = 'Utilisateur supprimé.';
        }
    }
    if ($action === 'supprimer_livre') {
        $lid = intval($_POST['livre_id'] ?? 0);
        if ($lid) {
            $pdo->prepare("DELETE FROM livres WHERE id=?")->execute([$lid]);
            $msg = 'Livre supprimé.';
        }
    }
    if ($action === 'changer_role') {
        $uid = intval($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';
        if ($uid && in_array($role, ['admin', 'etudiant'])) {
            $pdo->prepare("UPDATE utilisateurs SET role=? WHERE id=?")->execute([$role, $uid]);
            $msg = 'Rôle mis à jour.';
        }
    }
}

// Statistiques globales
$stats = [
    'utilisateurs' => $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
    'livres' => $pdo->query("SELECT COUNT(*) FROM livres")->fetchColumn(),
    'messages' => $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
    'echanges' => $pdo->query("SELECT COUNT(*) FROM echanges")->fetchColumn(),
];

// Liste utilisateurs
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC")->fetchAll();

// Liste livres
$livres = $pdo->query("SELECT l.*, u.nom, u.prenom FROM livres l JOIN utilisateurs u ON l.user_id=u.id ORDER BY l.date_ajout DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="title-gradient"><i class="fa-solid fa-gauge"></i> Tableau de bord Admin</h1>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card"><span class="stat-number"><?= $stats['utilisateurs'] ?></span><span class="stat-label">Utilisateurs</span>
        </div>
        <div class="stat-card"><span class="stat-number"><?= $stats['livres'] ?></span><span
                    class="stat-label">Livres</span></div>
        <div class="stat-card"><span class="stat-number"><?= $stats['messages'] ?></span><span class="stat-label">Messages</span>
        </div>
        <div class="stat-card"><span class="stat-number"><?= $stats['echanges'] ?></span><span class="stat-label">Échanges</span>
        </div>
    </div>

    <!-- Gestion utilisateurs -->
    <div class="admin-section">
        <h2> Gestion des utilisateurs</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Login</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Inscription</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($utilisateurs as $u): ?>
                <tr>
                    <td><?= h($u['login']) ?></td>
                    <td><?= h($u['prenom'] . ' ' . $u['nom']) ?></td>
                    <td><?= h($u['email']) ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="changer_role">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role" onchange="this.form.submit()">
                                <option value="etudiant" <?= $u['role'] == 'etudiant' ? 'selected' : '' ?>>Étudiant
                                </option>
                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </form>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                <input type="hidden" name="action" value="supprimer_user">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        <?php else: ?>
                            <span class="badge">Vous</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gestion livres -->
    <div class="admin-section">
        <h2> Gestion des livres</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Matière</th>
                <th>État</th>
                <th>Statut</th>
                <th>Propriétaire</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($livres as $l): ?>
                <tr>
                    <td><?= h($l['titre']) ?></td>
                    <td><?= h($l['auteur']) ?></td>
                    <td><?= h($l['matiere']) ?></td>
                    <td><span class="badge-etat badge-<?= $l['etat'] ?>"><?= ucfirst($l['etat']) ?></span></td>
                    <td><?= ucfirst(h($l['statut'])) ?></td>
                    <td><?= h($l['prenom'] . ' ' . $l['nom']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Supprimer ce livre ?')">
                            <input type="hidden" name="action" value="supprimer_livre">
                            <input type="hidden" name="livre_id" value="<?= $l['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
