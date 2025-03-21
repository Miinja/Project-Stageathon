<?php
$user_email = $_SESSION['user_email'] ?? 'Invité';
$user_role = $_SESSION['user_role'] ?? 'eleve';

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
<link rel="stylesheet" href="assets/css/general.css">
<header>
    <h1>Stageathon</h1>
    <div class="user-info">
        <span class="role-icon"><?php echo getRoleIcon($user_role); ?></span>
        <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
        <a href="logout.php" class="logout-button">Déconnexion</a>
    </div>
</header>
<nav class="sidebar">
    <h2>Tableau de bord</h2>
    <ul id="Classes">
        <li><a href="#">Classes</a>
            <ul>
                <li><a href="#">BTS SIO</a></li>
            </ul>
        <li><a href="#">Déconnexion</a></li>
    </ul>
</nav>

