<?php
session_start();
require_once '../includes/DatabaseLinker.php';
$db = DataBaseLinker::getConnexion();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

$user_email = $_SESSION['user_email'];
$user_role = '';

// Récupérer le rôle de l'utilisateur
$stmt = $db->prepare("SELECT role FROM account WHERE email = :email");
$stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
$stmt->execute();
$user_role = $stmt->fetchColumn();

// Vérifiez si l'ID de l'élève est fourni
if (!isset($_GET['eleve_id']) && !isset($_GET['token'])) {
    header("Location: classes.php");
    exit();
}

$is_token_access = false;
$eleve_id = 0;
$preconv_id = 0;

// Accès par token (pour les entreprises)
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $db->prepare("SELECT eleve_id, preconv_id FROM preconv_access_tokens WHERE token = :token AND expiry > NOW()");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $eleve_id = $result['eleve_id'];
        $preconv_id = $result['preconv_id'];
        $is_token_access = true;
    } else {
        die("Le lien d'accès est invalide ou a expiré.");
    }
} else {
    $eleve_id = $_GET['eleve_id'];
}

// Récupérer les informations de l'élève
$query_eleve = "SELECT s.id, s.nom, s.prenom, c.name as classe_name, c.annee 
                FROM student s 
                JOIN classes c ON s.classe_id = c.id
                WHERE s.id = :eleve_id";
$stmt_eleve = $db->prepare($query_eleve);
$stmt_eleve->bindParam(':eleve_id', $eleve_id, PDO::PARAM_INT);
$stmt_eleve->execute();

if ($stmt_eleve->rowCount() == 0) {
    header("Location: classes.php");
    exit();
}

$eleve = $stmt_eleve->fetch(PDO::FETCH_ASSOC);

// Vérifier si une préconvention existe déjà
$preconvention = null;
if ($preconv_id > 0) {
    $query_preconv = "SELECT * FROM preconventions WHERE id = :id";
    $stmt_preconv = $db->prepare($query_preconv);
    $stmt_preconv->bindParam(':id', $preconv_id, PDO::PARAM_INT);
} else {
    $query_preconv = "SELECT * FROM preconventions WHERE eleve_id = :eleve_id ORDER BY date_soumission DESC LIMIT 1";
    $stmt_preconv = $db->prepare($query_preconv);
    $stmt_preconv->bindParam(':eleve_id', $eleve_id, PDO::PARAM_INT);
}
$stmt_preconv->execute();

if ($stmt_preconv->rowCount() > 0) {
    $preconvention = $stmt_preconv->fetch(PDO::FETCH_ASSOC);
}

// Récupérer l'enseignant référent de la classe
$query_enseignant = "SELECT a.email FROM account a 
                    JOIN classes c ON a.id = c.teacher_id 
                    JOIN student s ON s.classe_id = c.id 
                    WHERE s.id = :eleve_id AND a.role = 'enseignant'";
$stmt_enseignant = $db->prepare($query_enseignant);
$stmt_enseignant->bindParam(':eleve_id', $eleve_id, PDO::PARAM_INT);
$stmt_enseignant->execute();
$enseignant_email = $stmt_enseignant->fetchColumn() ?: '';

// Déterminer les parties du formulaire éditables
$can_edit_student_part = ($user_role === 'secretaire' || $user_role === 'enseignant' || $user_role === 'eleve');
$can_edit_company_part = ($user_role === 'secretaire' || $user_role === 'enseignant' || $is_token_access);

// Traitement de la sauvegarde du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eleve_id = isset($_POST['eleve_id']) ? $_POST['eleve_id'] : $eleve_id;
    
    // Filtrer les données selon les permissions
    $data = [
        'eleve_id' => $eleve_id,
        'statut' => isset($_POST['submit_final']) ? 'final' : 'brouillon'
    ];
    
    // Partie étudiant
    if ($can_edit_student_part) {
        $data['civilite_etudiant'] = $_POST['civilite_etudiant'] ?? null;
        $data['date_debut'] = $_POST['date_debut'] ?? null;
        $data['date_fin'] = $_POST['date_fin'] ?? null;
    }
    
    // Partie entreprise
    if ($can_edit_company_part) {
        $data['raison_sociale'] = $_POST['raison_sociale'] ?? null;
        $data['adresse_entreprise'] = $_POST['adresse_entreprise'] ?? null;
        $data['lieu_stage'] = $_POST['lieu_stage'] ?? null;
        $data['civilite_tuteur'] = $_POST['civilite_tuteur'] ?? null;
        $data['nom_prenom_tuteur'] = $_POST['nom_prenom_tuteur'] ?? null;
        $data['fonction_tuteur'] = $_POST['fonction_tuteur'] ?? null;
        $data['telephone_tuteur'] = $_POST['telephone_tuteur'] ?? null;
        $data['email_tuteur'] = $_POST['email_tuteur'] ?? null;
        $data['civilite_directeur'] = $_POST['civilite_directeur'] ?? null;
        $data['nom_prenom_directeur'] = $_POST['nom_prenom_directeur'] ?? null;
        $data['email_directeur'] = $_POST['email_directeur'] ?? null;
        $data['description_activites'] = $_POST['description_activites'] ?? null;
        $data['horaire_lundi'] = $_POST['horaire_lundi'] ?? null;
        $data['horaire_mardi'] = $_POST['horaire_mardi'] ?? null;
        $data['horaire_mercredi'] = $_POST['horaire_mercredi'] ?? null;
        $data['horaire_jeudi'] = $_POST['horaire_jeudi'] ?? null;
        $data['horaire_vendredi'] = $_POST['horaire_vendredi'] ?? null;
        $data['horaire_samedi'] = $_POST['horaire_samedi'] ?? null;
        $data['horaire_dimanche'] = $_POST['horaire_dimanche'] ?? null;
        $data['horaires_variables'] = isset($_POST['horaires_variables']) ? 1 : 0;
    }
    
    $data['remarques'] = $_POST['remarques'] ?? null;
    
    // Insérer ou mettre à jour la préconvention
    if ($preconvention) {
        $sql_fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'eleve_id') { // ID élève ne peut pas être modifié
                $sql_fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        $sql = "UPDATE preconventions SET " . implode(", ", $sql_fields) . ", date_modification = NOW() WHERE id = :id";
        $params[':id'] = $preconvention['id'];
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $preconv_id = $preconvention['id'];
        $success_message = "La préconvention a été mise à jour avec succès.";
    } else {
        $columns = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        
        $sql = "INSERT INTO preconventions ($columns) VALUES ($values)";
        $stmt = $db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        $preconv_id = $db->lastInsertId();
        $success_message = "La préconvention a été créée avec succès.";
    }
    
    // Si on demande à envoyer un lien à l'entreprise
    if (isset($_POST['send_company_link']) && $_POST['email_to_share']) {
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        // Enregistrer le token
        $stmt = $db->prepare("INSERT INTO preconv_access_tokens (token, preconv_id, eleve_id, expiry) VALUES (:token, :preconv_id, :eleve_id, :expiry)");
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->bindParam(':preconv_id', $preconv_id, PDO::PARAM_INT);
        $stmt->bindParam(':eleve_id', $eleve_id, PDO::PARAM_INT);
        $stmt->bindParam(':expiry', $expiry, PDO::PARAM_STR);
        $stmt->execute();
        
        // Envoyer l'email avec le lien (utilisation de mail PHP simple pour l'exemple)
        $to = $_POST['email_to_share'];
        $subject = "Invitation à compléter une préconvention de stage";
        $link = "http://" . $_SERVER['HTTP_HOST'] . "/app/preconv.php?token=" . $token;
        
        $message = "Bonjour,\n\n";
        $message .= "L'étudiant(e) {$eleve['prenom']} {$eleve['nom']} vous invite à compléter sa préconvention de stage.\n";
        $message .= "Veuillez cliquer sur le lien suivant pour accéder au formulaire :\n";
        $message .= $link . "\n\n";
        $message .= "Ce lien est valable pendant 7 jours.\n";
        $message .= "Cordialement,\n";
        $message .= "L'équipe Stage'athon";
        
        $headers = "From: noreply@stageathon.com";
        
        mail($to, $subject, $message, $headers);
        
        $success_message .= " Un lien d'accès a été envoyé à {$_POST['email_to_share']}.";
    }
    
    // Rafraîchir les données de la préconvention
    $query_preconv = "SELECT * FROM preconventions WHERE id = :id";
    $stmt_preconv = $db->prepare($query_preconv);
    $stmt_preconv->bindParam(':id', $preconv_id, PDO::PARAM_INT);
    $stmt_preconv->execute();
    $preconvention = $stmt_preconv->fetch(PDO::FETCH_ASSOC);
}

// Générer un lien de partage pour l'entreprise (à afficher dans l'interface)
$company_access_link = '';
if (!$is_token_access && $preconvention) {
    $check_token = $db->prepare("SELECT token FROM preconv_access_tokens WHERE preconv_id = :preconv_id AND expiry > NOW() ORDER BY expiry DESC LIMIT 1");
    $check_token->bindParam(':preconv_id', $preconvention['id'], PDO::PARAM_INT);
    $check_token->execute();
    
    if ($check_token->rowCount() > 0) {
        $token = $check_token->fetchColumn();
        $company_access_link = "http://" . $_SERVER['HTTP_HOST'] . "/app/preconv.php?token=" . $token;
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préconvention de stage | Stage'athon</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/general.css">
</head>
<body>
    <div class="container mt-5">
        <?php if (!$is_token_access): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="classes.php">Classes</a></li>
                    <?php if (isset($_GET['classe_id'])): ?>
                        <li class="breadcrumb-item"><a href="eleves.php?id=<?php echo $_GET['classe_id']; ?>">Élèves</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active" aria-current="page">Préconvention de <?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?></li>
                </ol>
            </nav>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">📝 Préconvention de stage</h3>
                    <?php if ($preconvention && !$is_token_access): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light" data-toggle="modal" data-target="#shareModal">
                                <i class="fas fa-share-alt"></i> Partager
                            </button>
                            <?php if ($user_role === 'secretaire' || $user_role === 'enseignant'): ?>
                                <a href="print_preconv.php?id=<?php echo $preconvention['id']; ?>" class="btn btn-light ml-2" target="_blank">
                                    <i class="fas fa-print"></i> Imprimer
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <p class="mb-0"><i class="fas fa-info-circle"></i> 
                        <?php if ($is_token_access): ?>
                            Vous pouvez compléter les informations concernant l'entreprise pour la préconvention de stage de <?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?>.
                        <?php elseif ($user_role === 'eleve'): ?>
                            Veuillez remplir vos informations. Les champs concernant l'entreprise seront à compléter par votre employeur.
                        <?php else: ?>
                            Vous pouvez modifier toutes les informations du formulaire.
                        <?php endif; ?>
                    </p>
                </div>
                
                <form method="post" action="">
                    <input type="hidden" name="eleve_id" value="<?php echo $eleve_id; ?>">
                    
                    <!-- Informations générales -->
                    <div class="bg-light p-3 mb-4 rounded">
                        <h4 class="border-bottom pb-2 mb-3">Informations générales</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom_prenom">Nom/Prénom :</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="classe">Classe :</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($eleve['classe_name'] . ' - Promotion ' . $eleve['annee']); ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut">Date du début de stage :</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?php echo $preconvention['date_debut'] ?? ''; ?>" 
                                       <?php echo $can_edit_student_part ? '' : 'readonly'; ?> required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_fin">Date de fin de stage :</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="<?php echo $preconvention['date_fin'] ?? ''; ?>" 
                                       <?php echo $can_edit_student_part ? '' : 'readonly'; ?> required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="referent">Référent(e) de la filière :</label>
                                <input type="text" class="form-control" id="referent" name="referent" 
                                       value="<?php echo htmlspecialchars($enseignant_email); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section ETUDIANT(E) -->
                    <h5 class="border-bottom pb-2 mb-3">👨‍🎓 ETUDIANT(E)</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="civilite_etudiant">Civilité :</label>
                            <select class="form-control" id="civilite_etudiant" name="civilite_etudiant" 
                                    <?php echo $can_edit_student_part ? '' : 'disabled'; ?> required>
                                <option value="">Sélectionnez</option>
                                <option value="M." <?php echo (isset($preconvention['civilite_etudiant']) && $preconvention['civilite_etudiant'] === 'M.') ? 'selected' : ''; ?>>M.</option>
                                <option value="Mme" <?php echo (isset($preconvention['civilite_etudiant']) && $preconvention['civilite_etudiant'] === 'Mme') ? 'selected' : ''; ?>>Mme</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Section ENTREPRISE -->
                    <h5 class="border-bottom pb-2 mb-3">🏢 ENTREPRISE</h5>
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label for="raison_sociale">Raison sociale (nom de l'entreprise) :</label>
                            <input type="text" class="form-control" id="raison_sociale" name="raison_sociale" 
                                   value="<?php echo $preconvention['raison_sociale'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label for="adresse_entreprise">Adresse de l'entreprise (n° et nom de la voie, code postal, ville) :</label>
                            <input type="text" class="form-control" id="adresse_entreprise" name="adresse_entreprise" 
                                   value="<?php echo $preconvention['adresse_entreprise'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="lieu_stage">Lieu du stage si différent (n° et nom de la voie, code postal, ville) :</label>
                            <input type="text" class="form-control" id="lieu_stage" name="lieu_stage" 
                                   value="<?php echo $preconvention['lieu_stage'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    
                    <!-- Section TUTEUR / TUTRICE -->
                    <h5 class="border-bottom pb-2 mb-3">👨‍💼 TUTEUR / TUTRICE</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="civilite_tuteur">Civilité :</label>
                            <select class="form-control" id="civilite_tuteur" name="civilite_tuteur" 
                                    <?php echo $can_edit_company_part ? '' : 'disabled'; ?> required>
                                <option value="">Sélectionnez</option>
                                <option value="M." <?php echo (isset($preconvention['civilite_tuteur']) && $preconvention['civilite_tuteur'] === 'M.') ? 'selected' : ''; ?>>M.</option>
                                <option value="Mme" <?php echo (isset($preconvention['civilite_tuteur']) && $preconvention['civilite_tuteur'] === 'Mme') ? 'selected' : ''; ?>>Mme</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="nom_prenom_tuteur">Nom/Prénom :</label>
                            <input type="text" class="form-control" id="nom_prenom_tuteur" name="nom_prenom_tuteur" 
                                   value="<?php echo $preconvention['nom_prenom_tuteur'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="fonction_tuteur">Fonction :</label>
                            <input type="text" class="form-control" id="fonction_tuteur" name="fonction_tuteur" 
                                   value="<?php echo $preconvention['fonction_tuteur'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="telephone_tuteur">Téléphone :</label>
                            <input type="tel" class="form-control" id="telephone_tuteur" name="telephone_tuteur" 
                                   value="<?php echo $preconvention['telephone_tuteur'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email_tuteur">Email :</label>
                            <input type="email" class="form-control" id="email_tuteur" name="email_tuteur" 
                                   value="<?php echo $preconvention['email_tuteur'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                    </div>
                    
                    <!-- Section DIRECTEUR / DIRECTRICE -->
                    <h5 class="border-bottom pb-2 mb-3">👩‍💼 DIRECTEUR / DIRECTRICE</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="civilite_directeur">Civilité :</label>
                            <select class="form-control" id="civilite_directeur" name="civilite_directeur" 
                                    <?php echo $can_edit_company_part ? '' : 'disabled'; ?> required>
                                <option value="">Sélectionnez</option>
                                <option value="M." <?php echo (isset($preconvention['civilite_directeur']) && $preconvention['civilite_directeur'] === 'M.') ? 'selected' : ''; ?>>M.</option>
                                <option value="Mme" <?php echo (isset($preconvention['civilite_directeur']) && $preconvention['civilite_directeur'] === 'Mme') ? 'selected' : ''; ?>>Mme</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nom_prenom_directeur">Nom/Prénom :</label>
                            <input type="text" class="form-control" id="nom_prenom_directeur" name="nom_prenom_directeur" 
                                   value="<?php echo $preconvention['nom_prenom_directeur'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email_directeur">Email :</label>
                            <input type="email" class="form-control" id="email_directeur" name="email_directeur" 
                                   value="<?php echo $preconvention['email_directeur'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required>
                        </div>
                    </div>
                    
                    <!-- Section Activités -->
                    <h5 class="border-bottom pb-2 mb-3">📋 DESCRIPTION DES ACTIVITÉS</h5>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="description_activites">Description des principales activités qui seront confiées à l'étudiant(e) :</label>
                            <textarea class="form-control" id="description_activites" name="description_activites" rows="4" 
                                      <?php echo $can_edit_company_part ? '' : 'readonly'; ?> required><?php echo $preconvention['description_activites'] ?? ''; ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Section Horaires -->
                    <h5 class="border-bottom pb-2 mb-3">🕒 JOURS ET HORAIRES DE TRAVAIL</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="horaire_lundi">Lundi :</label>
                            <input type="text" class="form-control" id="horaire_lundi" name="horaire_lundi" 
                                   placeholder="ex: 9h-12h, 14h-17h" 
                                   value="<?php echo $preconvention['horaire_lundi'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="horaire_mardi">Mardi :</label>
                            <input type="text" class="form-control" id="horaire_mardi" name="horaire_mardi" 
                                   placeholder="ex: 9h-12h, 14h-17h" 
                                   value="<?php echo $preconvention['horaire_mardi'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="horaire_mercredi">Mercredi :</label>
                            <input type="text" class="form-control" id="horaire_mercredi" name="horaire_mercredi" 
                                   placeholder="ex: 9h-12h, 14h-17h" 
                                   value="<?php echo $preconvention['horaire_mercredi'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="horaire_jeudi">Jeudi :</label>
                            <input type="text" class="form-control" id="horaire_jeudi" name="horaire_jeudi" 
                                   placeholder="ex: 9h-12h, 14h-17h" 
                                   value="<?php echo $preconvention['horaire_jeudi'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="horaire_vendredi">Vendredi :</label>
                            <input type="text" class="form-control" id="horaire_vendredi" name="horaire_vendredi" 
                                   placeholder="ex: 9h-12h, 14h-17h" 
                                   value="<?php echo $preconvention['horaire_vendredi'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="horaire_samedi">Samedi :</label>
                            <input type="text" class="form-control" id="horaire_samedi" name="horaire_samedi" 
                                   placeholder="ex: 9h-12h, 14h-17h" 
                                   value="<?php echo $preconvention['horaire_samedi'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="horaire_dimanche">Dimanche :</label>
                            <input type="text" class="form-control" id="horaire_dimanche" name="horaire_dimanche" 
                                   placeholder="ex: 9h-12h, 14h-17h" 
                                   value="<?php echo $preconvention['horaire_dimanche'] ?? ''; ?>" 
                                   <?php echo $can_edit_company_part ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="horaires_variables" name="horaires_variables" 
                                       <?php echo (isset($preconvention['horaires_variables']) && $preconvention['horaires_variables'] == 1) ? 'checked' : ''; ?> 
                                       <?php echo $can_edit_company_part ? '' : 'disabled'; ?>>
                                <label class="form-check-label" for="horaires_variables">
                                    Horaires variables transmis au stagiaire par email
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section Remarques -->
                    <h5 class="border-bottom pb-2 mb-3">💬 REMARQUES</h5>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="remarques">Remarques éventuelles :</label>
                            <textarea class="form-control" id="remarques" name="remarques" rows="3"><?php echo $preconvention['remarques'] ?? ''; ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i> Enregistrer la préconvention
                            </button>
                        </div>
                        <?php if (($user_role === 'secretaire' || $user_role === 'enseignant') && $preconvention): ?>
                            <div class="col-md-6 mb-3">
                                <button type="submit" name="submit_final" class="btn btn-success btn-block">
                                    <i class="fas fa-check-circle mr-2"></i> Valider définitivement
                                </button>
                            </div>
                        <?php elseif ($is_token_access): ?>
                            <div class="col-md-6 mb-3">
                                <button type="submit" name="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-paper-plane mr-2"></i> Envoyer les informations
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#shareModal">
                                    <i class="fas fa-share-alt mr-2"></i> Partager avec l'entreprise
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal pour partager le lien avec l'entreprise -->
    <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareModalLabel">Partager avec l'entreprise</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($company_access_link): ?>
                        <div class="alert alert-info">
                            <p>Un lien d'accès a déjà été généré. Vous pouvez le copier ci-dessous :</p>
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="share_link" value="<?php echo $company_access_link; ?>" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <input type="hidden" name="eleve_id" value="<?php echo $eleve_id; ?>">
                        <div class="form-group">
                            <label for="email_to_share">Email de l'entreprise :</label>
                            <input type="email" class="form-control" id="email_to_share" name="email_to_share" required>
                            <small class="form-text text-muted">Un email avec un lien d'accès sera envoyé à cette adresse.</small>
                        </div>
                        <button type="submit" name="send_company_link" class="btn btn-primary">Envoyer le lien d'accès</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function copyLink() {
            var copyText = document.getElementById("share_link");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Lien copié!");
        }
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>