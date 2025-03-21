<?php
$user_email = $_SESSION['email'] ?? 'Invité';
$user_role = $_SESSION['role'] ?? 'Invité';

function getRoleIcon($role) {
    switch ($role) {
        case 'secretaire':
            return '🗂️';
        case 'enseignant':
            return '👨‍🏫';
        case 'eleve':
        default:
            return '🎓';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/general.css">
    <title>Stageathon</title>
</head>
<body>
<header class="bg-dark text-white p-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h3">Stageathon</h1>
        <div class="user-info d-flex align-items-center">
            <span class="role-icon mr-2"><?php echo getRoleIcon($user_role); ?></span>
            <span class="user-email mr-3"><?php echo htmlspecialchars($user_email); ?></span>
            <a href="logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </div>
</header>
<nav class="sidebar bg-secondary text-white p-3">
    <h2 class="h5">Tableau de bord</h2>
    <ul id="Classes" class="list-unstyled">
        <li><a href="#" class="text-white">Classes</a>
            <ul class="list-unstyled pl-3">
                <li><a href="#" class="text-white">BTS SIO</a></li>
            </ul>
        </li>
        <li><a href="#" class="text-white">Déconnexion</a></li>
    </ul>
</nav>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>