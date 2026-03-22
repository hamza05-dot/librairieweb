<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibraireWeb <?= isset($pageTitle) ? '— ' . $pageTitle : '' ?></title>
    <link rel="stylesheet" href="/LibraireWeb/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="/LibraireWeb/public/index.php" class="nav-logo">
            📚 LibraireWeb
        </a>

        <!-- Links -->
        <ul class="nav-links">
            <li><a href="/LibraireWeb/public/index.php">Accueil</a></li>
            <li><a href="/LibraireWeb/public/catalog.php">Catalogue</a></li>
        </ul>

        <!-- Right side -->
        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- Logged in -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/LibraireWeb/admin/dashboard.php" class="btn btn-outline">Admin</a>
                <?php endif; ?>

                <a href="/LibraireWeb/public/cart.php" class="btn btn-outline">
                    🛒 Panier
                    <span class="cart-count" id="cartCount">0</span>
                </a>
                <a href="/LibraireWeb/public/account.php" class="btn btn-outline">
                    👤 <?= htmlspecialchars($_SESSION['prenom']) ?>
                </a>
                <a href="/LibraireWeb/public/logout.php" class="btn btn-danger">Déconnexion</a>

            <?php else: ?>

                <!-- Not logged in -->
                <a href="/LibraireWeb/public/login.php" class="btn btn-outline">Connexion</a>
                <a href="/LibraireWeb/public/register.php" class="btn btn-primary">S'inscrire</a>

            <?php endif; ?>
        </div>

    </div>
</nav>

<main class="main-content">