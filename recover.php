<?php
session_start();
require_once 'includes/DatabaseLinker.php';
require 'includes/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = DataBaseLinker::getConnexion();

if (isset($_POST['submit'])) {
    $email = htmlentities($_POST['email'], ENT_QUOTES, "UTF-8");

    if (empty($email)) {
        $msg = "Le champ Email est vide.";
    } else {
        // Vérifiez si l'email existe dans la base de données
        $stmt = $db->prepare("SELECT * FROM account WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $msg = "Aucun compte trouvé avec cet email.";
        } else {
            // Générer un token de récupération
            $token = bin2hex(random_bytes(50));

            // Insérer le token dans la base de données
            $stmt = $db->prepare("INSERT INTO password_resets (email, token) VALUES (:email, :token)");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();

            // Charger la configuration SMTP
            $smtp_config = require 'includes/smtp_config.php';

            // Envoyer l'email de récupération
            $mail = new PHPMailer(true);
            try {
                // Configuration du serveur SMTP
                $mail->isSMTP();
                $mail->Host = $smtp_config['host'];
                $mail->SMTPAuth = $smtp_config['smtp_auth'];
                $mail->Username = $smtp_config['username'];
                $mail->Password = $smtp_config['password'];
                $mail->SMTPSecure = $smtp_config['smtp_secure'];
                $mail->Port = $smtp_config['port'];

                // Destinataires
                $mail->setFrom($smtp_config['username'], 'Stageathon');
                $mail->addAddress($email);

                // Contenu de l'email
                $mail->isHTML(true);
                $mail->Subject = 'Récupération de mot de passe';
                $mail->Body    = "Cliquez sur le lien suivant pour réinitialiser votre mot de passe : <a href='http://yourdomain.com/reset_password.php?token=$token'>Réinitialiser le mot de passe</a>";

                $mail->send();
                $msg = "Un email de récupération de mot de passe a été envoyé.";
            } catch (Exception $e) {
                $msg = "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récupération de compte | Stage'athon</title>
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
    <span><h3>Récupération de compte</h3></span>
    <?php
    if (isset($msg)) {
        echo '<br>';
        echo '<br>';
        echo '<br>';
        echo '<div class="alert">' . $msg . '</div>';
    }
    ?>
    <form method="POST" id="recover-form">
        <div id="u" class="form-group">
            <input id="email" spellcheck=false class="form-control" name="email" type="email" size="18" alt="login" required>
            <span class="form-highlight"></span>
            <span class="form-bar"></span>
            <label for="email" class="float-label">Email</label>
        </div>
        <div class="form-group">
            <button id="submit" type="submit" name="submit">Envoyer</button>
        </div>
    </form>
</div>

<p class="copyright">
    <span>&copy; 2025 <a href="https://lasalle63.fr">La Salle 63</a>. Developed by <a href="https://sio.jbdelasalle.com" target="_blank">BTS SIO</a> (Promo. 2024-2025). All rights reserved.</span>
</p>
</body>
</html>