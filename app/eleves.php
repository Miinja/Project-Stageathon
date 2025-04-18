<?php
session_start();
require_once '../includes/DatabaseLinker.php';
$db = DataBaseLinker::getConnexion();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

// Vérifiez si l'ID de la classe est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: classes.php");
    exit();
}

$classe_id = $_GET['id'];

// Récupérer les informations de la classe
$query_classe = "SELECT c.id, c.name, c.annee, t.nom as teacher_nom, t.prenom as teacher_prenom
                FROM classes c 
                LEFT JOIN teacher t ON c.teacher_id = t.id 
                WHERE c.id = :classe_id";
$stmt_classe = $db->prepare($query_classe);
$stmt_classe->bindParam(':classe_id', $classe_id, PDO::PARAM_INT);
$stmt_classe->execute();

if ($stmt_classe->rowCount() == 0) {
    header("Location: classes.php");
    exit();
}

$classe = $stmt_classe->fetch(PDO::FETCH_ASSOC);

// Récupérer les élèves de la classe
$query_eleves = "SELECT s.id, s.nom, s.prenom 
                FROM student s 
                WHERE s.classe_id = :classe_id 
                ORDER BY s.nom, s.prenom";
$stmt_eleves = $db->prepare($query_eleves);
$stmt_eleves->bindParam(':classe_id', $classe_id, PDO::PARAM_INT);
$stmt_eleves->execute();
$eleves = $stmt_eleves->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/general.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="classes.php">Classes</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($classe['name']); ?></li>
        </ol>
    </nav>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0">📚 Classe : <?php echo htmlspecialchars($classe['name']); ?></h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Promotion :</strong> <?php echo htmlspecialchars($classe['annee']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Enseignant : Mr/Mme.</strong> <?php echo htmlspecialchars($classe['teacher_nom'] ?? 'Non assigné'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-3">🎓 Liste des élèves</h3>

    <?php if (empty($eleves)): ?>
        <div class="alert alert-info">
            <p class="text-center">Aucun élève n'est inscrit dans cette classe pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($eleves as $eleve): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body text-center">
                            <div class="avatar mb-3">
                                <img src="assets/images/profile-placeholder.png" alt="Photo de profil" 
                                    class="rounded-circle img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($classe['name']); ?> - Promotion <?php echo htmlspecialchars($classe['annee']); ?>
                            </p>
                            <div class="btn-group">
                                <a href="preconv.php?eleve_id=<?php echo $eleve['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-file-signature"></i> Préconvention de stage
                                </a>
                                <?php if ($user_role === 'secretaire' || $user_role === 'enseignant'): ?>
                                <a href="edit_eleve.php?id=<?php echo $eleve['id']; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($user_role === 'secretaire' || $user_role === 'enseignant'): ?>
        <div class="text-center mt-3 mb-5">
            <a href="add_eleve.php?classe_id=<?php echo $classe_id; ?>" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Ajouter un élève
            </a>
        </div>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>
</div>