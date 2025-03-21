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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stageathon</title>
    <link rel="stylesheet" href="assets/css/general.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>
</body>
</html>