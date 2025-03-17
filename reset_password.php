<?php
session_start();
require_once 'includes/DatabaseLinker.php';

$db = DataBaseLinker::getConnexion();

$token_valid = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifiez si le token est valide et n'a pas expiré
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token AND created_at > (NOW() - INTERVAL 5 MINUTE)");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $msg = "Le lien de récupération est invalide ou a expiré.";
    } else {
        $token_valid = true;
        if (isset($_POST['submit'])) {
            $new_password = htmlentities($_POST['new_password'], ENT_QUOTES, "UTF-8");
            $confirm_password = htmlentities($_POST['confirm_password'], ENT_QUOTES, "UTF-8");

            if (empty($new_password) || empty($confirm_password)) {
                $msg = "Tous les champs sont obligatoires.";
            } elseif ($new_password !== $confirm_password) {
                $msg = "Les mots de passe ne correspondent pas.";
            } else {
                // Mettre à jour le mot de passe de l'utilisateur
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $row['email'];
                $hashedPassword = hash('sha256', $new_password);

                $stmt = $db->prepare("UPDATE account SET password = :password WHERE email = :email");
                $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                // Supprimer le token de récupération
                $stmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                $msg = "Votre mot de passe a été réinitialisé avec succès.";
            }
        }
    }
} else {
    $msg = "Aucun token de récupération fourni.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de mot de passe | Stage'athon</title>
    <link rel="icon" href="favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="logo" style="text-align: center; margin-top: 20px;">
    <img src="logo.svg" alt="Logo JB de la Salle" width="125px"> 
    <h1 style="color: black; font-family: 'Arial', sans-serif; font-weight: bold; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);">
        Stage'<span>athon</span>
    </h1>
</div>
<div class="login">
    <div class="photo"></div>
    <span><h3>Réinitialisation de mot de passe</h3></span>
    <br>
    <?php
    if (isset($msg)) {
        echo '<br>';
        echo '<br>';
        echo '<br>';
        echo '<div class="alert alert-danger">' . $msg . '</div>';
        echo '<br>';
        echo '<br>';
        echo '<br>';
    }
    ?>
    <?php if ($token_valid): ?>
    <form method="POST" action="reset_password.php?token=<?php echo $token; ?>" id="reset-form">
        <div id="p" class="form-group">
            <input id="new_password" spellcheck=false class="form-control" name="new_password" type="password" size="18" alt="login" required>
            <span class="form-highlight"></span>
            <span class="form-bar"></span>
            <label for="new_password" class="float-label">Nouveau mot de passe</label>
        </div>
        <div id="p" class="form-group">
            <input id="confirm_password" spellcheck=false class="form-control" name="confirm_password" type="password" size="18" alt="login" required>
            <span class="form-highlight"></span>
            <span class="form-bar"></span>
            <label for="confirm_password" class="float-label">Confirmer le mot de passe</label>
        </div>
        <div class="form-group">
            <button id="submit" type="submit" name="submit">Réinitialiser</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<p class="copyright">
    <span>&copy; 2025 <a href="https://lasalle63.fr">La Salle 63</a>. Developed by <a href="https://sio.jbdelasalle.com" target="_blank">BTS SIO</a> (Promo. 2024-2025). All rights reserved.</span>
</p>
</body>
</html>
