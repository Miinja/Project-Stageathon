<?php
session_start();
require_once '../includes/DatabaseLinker.php';
$db = DataBaseLinker::getConnexion();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

// Récupérer toutes les classes avec le nom des enseignants
$query = "SELECT c.id, c.name, c.annee, t.nom as teacher_nom
          FROM classes c 
          LEFT JOIN teacher t ON c.teacher_id = t.id
          ORDER BY c.name";
$stmt = $db->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php'; 
?>
<link rel="stylesheet" href="assets/css/general.css">
<div class="container mt-4">
    <h2 class="text-center mb-4">📚 Liste des Classes</h2>
    
    <?php if (empty($classes)): ?>
        <div class="alert alert-info">
            <p class="text-center">Aucune classe n'est disponible pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($classes as $classe): ?>
                <div class="col-md-4">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($classe['name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><strong>Promotion :</strong> <?php echo htmlspecialchars($classe['annee']); ?></p>
                            <p class="card-text"><strong>Enseignant :</strong> <?php echo htmlspecialchars($classe['teacher_nom'] ?? 'Non assigné'); ?></p>
                            <a href="eleves.php?id=<?php echo $classe['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($user_role === 'secretaire'): ?>
        <div class="text-center mt-4">
            <a href="add_classe.php" class="btn btn-success">
                <i class="fa fa-plus"></i> Ajouter une classe
            </a>
        </div>
    <?php endif; ?>
    
    <?php include 'includes/footer.php'; ?>
</div>