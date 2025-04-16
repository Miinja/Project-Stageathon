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
    <div class="container d-flex justify-content-center align-items-center">
        <h1 class="h3 text-center">Stageathon</h1>
    </div>
    <div class="container d-flex justify-content-end align-items-center">
        <div class="user-info d-flex align-items-center">
            <span class="role-icon mr-2"><?php echo getRoleIcon($user_role); ?></span>
            <span class="user-email mr-3"><?php echo htmlspecialchars($user_email); ?></span>
            <a href="logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </div>
</header>
<div class="d-flex flex-column flex-md-row">
<nav class="navbar bg-secondary text-white d-flex flex-column p-3" style="width: 200px; min-height: 100vh;">
    <h2 class="h5 text-white text-center">Tableau de bord</h2>
    <ul id="Classes" class="navbar-nav flex-column">
        <li class="nav-item"><a href="classes.php" class="nav-link text-white">Classes</a></li>
        <?php if ($user_role === 'eleve'): ?>
            <li class="nav-item"><a href="preconv.php" class="nav-link text-white">Préconvention</a></li>
        <?php endif; ?>
        <li class="nav-item"><a href="logout.php" class="nav-link text-white">Déconnexion</a></li>
    </ul>
</nav>
    <div class="content flex-grow-1 p-3">
        <!-- Contenu principal -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>