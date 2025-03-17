<?php
session_start();
require_once 'includes/DatabaseLinker.php';
$db = DataBaseLinker::getConnexion();

$max_attempts = 5;
$lockout_time = 15; // in minutes

// Clean up old login attempts
$stmt = $db->prepare("DELETE FROM login_attempts WHERE attempt_time < (NOW() - INTERVAL :lockout_time MINUTE)");
$stmt->bindParam(':lockout_time', $lockout_time, PDO::PARAM_INT);
$stmt->execute();

function getIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$ip_address = getIp();

if (isset($_SESSION['email'])) {
    header("Location: app/home.php");
    exit();
} else {
    if (isset($_POST['submit'])) {
        $email = htmlentities($_POST['email'], ENT_QUOTES, "UTF-8");
        $motDePasse = htmlentities($_POST['password'], ENT_QUOTES, "UTF-8");

        // Check login attempts
        $stmt = $db->prepare("SELECT COUNT(*) AS attempts FROM login_attempts WHERE ip_address = :ip AND attempt_time > (NOW() - INTERVAL :lockout_time MINUTE)");
        $stmt->bindParam(':ip', $ip_address, PDO::PARAM_STR);
        $stmt->bindParam(':lockout_time', $lockout_time, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['attempts'] >= $max_attempts) {
            $msg = "Trop de tentatives de connexion. Veuillez réessayer après $lockout_time minutes.";
        } else {
            if (empty($email)) {
                $msg = "Le champ Email est vide.";
            } elseif (empty($motDePasse)) {
                $msg = "Le champ Mot de passe est vide.";
            } else {
                try {
                    $stmt = $db->prepare("SELECT * FROM account WHERE email = :email AND password = :password");
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $hashedPassword = hash('sha256', $motDePasse);
                    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

                    $stmt->execute();

                    if ($stmt->rowCount() == 0) {
                        $msg = "L'identifiant ou le mot de passe est incorrect.";
                        // Log the failed attempt
                        $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (:ip, :email)");
                        $stmt->bindParam(':ip', $ip_address, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->execute();
                    } else {
                        $_SESSION['email'] = $email;
                        header("Refresh:0");
                    }
                } catch (PDOException $e) {
                    $msg = "Erreur lors de la requête : " . $e->getMessage();
                }
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Stage'athon | Bansac</title>
        <link rel="icon" href="favicon.png" type="image/x-icon">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <span><h3>Portail de Connexion</h3></span>
        <br>
        <form method="POST" action="index.php" id="login-form">
            <div id="u" class="form-group">
                <input id="email" spellcheck=false class="form-control" name="email" type="email" size="18" alt="login" value="<?php echo isset($email) ? $email : ''; ?>" required>
                <span class="form-highlight"></span>
                <span class="form-bar"></span>
                <label for="email" class="float-label">Email</label>
            </div>
            <div id="p" class="form-group">
                <input id="password" class="form-control" spellcheck=false name="password" type="password" size="18" alt="login" required>
                <span class="form-highlight"></span>
                <span class="form-bar"></span>
                <label for="password" class="float-label">Mot de passe</label>
                <span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password" title="Révéler le mot de passe"></span>
            </div>
            <div class="form-group">
                <button id="submit" type="submit" name="submit">Connexion</button>
            </div><br>
        </form>
        <?php
            if (isset($msg)) {
                echo '<div class="alert">' . $msg . '</div>';
                echo '<br>';
                echo '<br>';
            }
            ?>
        <br>
        <span><a href="recover.php">Mot de passe oublié ?</a></span>
        <br>
    </div>

    <p class="copyright">
        <span>&copy; 2025 <a href="https://lasalle63.fr">La Salle 63</a>. Developed by <a href="https://sio.jbdelasalle.com" target="_blank">BTS SIO</a> (Promo. 2024-2025). All rights reserved.</span>
    </p>
    </body>
    </html>
    <script>
    document.querySelector('.toggle-password').addEventListener('click', function (e) {
        const passwordField = document.querySelector('#password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
    </script>                                    
<?php
}
?>