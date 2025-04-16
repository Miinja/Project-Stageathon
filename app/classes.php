<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/general.css">
<div class="container mt-4">
    <h2 class="text-center mb-4">Liste des Classes</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Classe 1</h5>
                    <p class="card-text">Description de la classe 1.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Classe 2</h5>
                    <p class="card-text">Description de la classe 2.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Classe 3</h5>
                    <p class="card-text">Description de la classe 3.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Classe 4</h5>
                    <p class="card-text">Description de la classe 4.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Classe 5</h5>
                    <p class="card-text">Description de la classe 5.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Classe 6</h5>
                    <p class="card-text">Description de la classe 6.</p>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
