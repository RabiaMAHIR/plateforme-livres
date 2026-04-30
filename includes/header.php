<?php
// includes/header.php — En-tête global de l'application
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>index.php" class="nav-brand">
            <?= SITE_NAME ?>
        </a>

        <div class="menu-toggle" id="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </div>

        <ul class="nav-links" id="nav-links">
            <?php if (!estConnecte()): ?>
                <li><a href="<?= BASE_URL ?>index.php">Accueil</a></li>
            <?php endif; ?>
            <li><a href="<?= BASE_URL ?>pages/livres.php">Livres</a></li>


            <?php if (estConnecte()): ?>
                <?php
                $stmtNotifDemandeur = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM echanges 
                    WHERE demandeur_id = ? 
                    AND vu_demandeur = 0
                    AND statut IN ('accepte','refuse')
                ");
                $stmtNotifDemandeur->execute([$_SESSION['user_id']]);
                $notifDemandeur = $stmtNotifDemandeur->fetchColumn();

                $stmtNotifProprio = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM echanges 
                    WHERE proprio_id = ? 
                    AND vu_proprio = 0
                    AND statut = 'en_attente'
                ");


                $stmtNotifProprio->execute([$_SESSION['user_id']]);
                $notifProprio = $stmtNotifProprio->fetchColumn();
                ?>


                <li><a href="<?= BASE_URL ?>pages/messages.php">
                        Messagerie
                        <?php
                        // Compter messages non lus
                        $stmtMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = 0");
                        $stmtMsg->execute([$_SESSION['user_id']]);
                        $nbMsg = $stmtMsg->fetchColumn();
                        if ($nbMsg > 0) echo '<span class="badge">' . $nbMsg . '</span>';
                        ?>
                    </a></li>
                <li>
                    <a href="<?= BASE_URL ?>pages/mes_demandes.php">
                        Mes demandes
                        <?php if ($notifDemandeur > 0): ?>
                            <span class="badge"><?= $notifDemandeur ?></span>
                        <?php endif; ?>

                    </a>
                </li>


                <li><a href="<?= BASE_URL ?>pages/profil.php">Mon profil
                        <?php if ($notifProprio > 0): ?>
                            <span class="badge"><?= $notifProprio ?></span>
                        <?php endif; ?>
                    </a></li>

                <?php if (estAdmin()): ?>
                    <li><a href="<?= BASE_URL ?>pages/admin.php" class="btn-admin">Admin</a></li>
                <?php endif; ?>
                <li><a href="<?= BASE_URL ?>pages/logout.php" class="btn-logout">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>pages/login.php" class="btn-login">Connexion</a></li>
                <li><a href="<?= BASE_URL ?>pages/register.php" class="btn-register">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<main class="main-content">
