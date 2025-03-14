<?php
session_start();
require_once 'includes/DatabaseLinker.php';
$db = DataBaseLinker::getConnexion();
if (isset($_SESSION['username'])) {
    header("Location: app/home.php");
    exit();
} else {
    if (isset($_POST['submit'])) {
        if (empty($_POST['username'])) {
            $msg = "Le champ Pseudo est vide.";
        } elseif (empty($_POST['psd'])) {
            $msg = "Le champ Mot de passe est vide.";
        } else {
            $username = htmlentities($_POST['username'], ENT_QUOTES, "UTF-8");
            $motDePasse = htmlentities($_POST['psd'], ENT_QUOTES, "UTF-8");
            try {
                $stmt = $db->prepare("SELECT * FROM account WHERE user = :user AND psd = :psd");
                $stmt->bindParam(':user', $username, PDO::PARAM_STR);
                $hashedPassword = hash('sha256', $motDePasse);
                $stmt->bindParam(':psd', $hashedPassword, PDO::PARAM_STR);

                $stmt->execute();

                if ($stmt->rowCount() == 0) {
                $msg = "L'identifiant ou le mot de passe est incorrect.";
                }
                else {
                    $_SESSION['username'] = $username;
                    header("Refresh:0");
                }
            } catch (PDOException $e) {
                $msg = "Erreur lors de la requête : " . $e->getMessage() . "";
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Stageathon | Ensemble la Salle 63</title>
        <link rel="icon" href="favicon.png" type="image/x-icon">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <div class="login">
    <div class="logo">
        <img src="favicon.png" alt="Stageathon" width="20px"> Stageathon
    </div>
    <div class="photo">
    </div>
    <span>Connectez-vous</span>
    <form action="" id="login-form">
        <div id="u" class="form-group">
            <input id="username" spellcheck=false class="form-control" name="username" type="text" size="18" alt="login" required="">
            <span class="form-highlight"></span>
            <span class="form-bar"></span>
            <label for="username" class="float-label">Nom d'Utilisateur</label>
        </div>
        <div id="p" class="form-group">
            <input id="password" class="form-control" spellcheck=false name="password" type="password" size="18" alt="login" required="">
            <span class="form-highlight"></span>
            <span class="form-bar"></span>
            <label for="password" class="float-label">Mots de Passe</label>
        </div>
        <div class="form-group">
            <input type="checkbox" id="rem">
            <label for="rem">Stay Signed in</label>
            <button id="submit" type="submit" ripple>Sign in</button>
        </div>
    </form>
    <footer><a href="#0">Create an account</a></footer>
    </div>

    <p class="copyright">
        <span>&copy; 2025 <a href="https://lasalle63.fr">La Salle 63</a>. Developed by <a href="https://sio.jbdelasalle.com" target="_blank">BTS SIO</a> (Promo. 2024-2025). All rights reserved.</span>
    </p>
    </body>
</html>
                                    
                                    
<?php
}
?>