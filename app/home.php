<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil | Stage'athon</title>
    <link rel="icon" href="../favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="logo" style="text-align: center; margin-top: 20px;">
    <img src="../logo.svg" alt="Logo JB de la Salle" width="125px"> 
    <h1 style="color: black; font-family: 'Arial', sans-serif; font-weight: bold; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);">
        Stage'<span>athon</span>
    </h1>
</div>
<div class="content">
    <h2>Bienvenue sur la plateforme Stage'athon</h2>
    <p>Vous êtes connecté en tant que <?php echo $_SESSION['email']; ?>.</p>
    <p>Rôle : <?php echo $_SESSION['role']; ?></p>
    <a href="logout.php">Déconnexion</a>
</div>

<p class="copyright">
    <span>&copy; 2025 <a href="https://lasalle63.fr">La Salle 63</a>. Developed by <a href="https://sio.jbdelasalle.com" target="_blank">BTS SIO</a> (Promo. 2024-2025). All rights reserved.</span>
</p>
</body>
</html>