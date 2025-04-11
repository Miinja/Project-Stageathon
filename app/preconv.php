<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stageathon</title>
    <link rel="stylesheet" href="assets/css/general.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">📝 Préconvention de stage</h3>
                    </div>
                    <div class="card-body">
                        
                        <form method="post" action="save_convention.php">
                            <!-- Informations générales -->
                            <div class="bg-light p-3 mb-4 rounded">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nom_prenom">Nom/Prénom :</label>
                                        <input type="text" class="form-control" id="nom_prenom" name="nom_prenom" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="classe">Classe :</label>
                                        <input type="text" class="form-control" id="classe" name="classe" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="date_debut">Date du début de stage :</label>
                                        <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="referent">Référent(e) de la filière :</label>
                                        <input type="text" class="form-control" id="referent" name="referent" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section ETUDIANT(E) -->
                            <h5 class="border-bottom pb-2 mb-3">👨‍🎓 ETUDIANT(E)</h5>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="civilite_etudiant">Civilité :</label>
                                    <select class="form-control" id="civilite_etudiant" name="civilite_etudiant" required>
                                        <option value="">Sélectionnez</option>
                                        <option value="M.">M.</option>
                                        <option value="Mme">Mme</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Section ENTREPRISE -->
                            <h5 class="border-bottom pb-2 mb-3">🏢 ENTREPRISE</h5>
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label for="raison_sociale">Raison sociale (nom de l'entreprise) :</label>
                                    <input type="text" class="form-control" id="raison_sociale" name="raison_sociale" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label for="adresse_entreprise">Adresse de l'entreprise (n° et nom de la voie, code postal, ville) :</label>
                                    <input type="text" class="form-control" id="adresse_entreprise" name="adresse_entreprise" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="lieu_stage">Lieu du stage si différent (n° et nom de la voie, code postal, ville) :</label>
                                    <input type="text" class="form-control" id="lieu_stage" name="lieu_stage">
                                </div>
                            </div>
                            
                            <!-- Section TUTEUR / TUTRICE -->
                            <h5 class="border-bottom pb-2 mb-3">👨‍💼 TUTEUR / TUTRICE</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="civilite_tuteur">Civilité :</label>
                                    <select class="form-control" id="civilite_tuteur" name="civilite_tuteur" required>
                                        <option value="">Sélectionnez</option>
                                        <option value="M.">M.</option>
                                        <option value="Mme">Mme</option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="nom_prenom_tuteur">Nom/Prénom :</label>
                                    <input type="text" class="form-control" id="nom_prenom_tuteur" name="nom_prenom_tuteur" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="fonction_tuteur">Fonction :</label>
                                    <input type="text" class="form-control" id="fonction_tuteur" name="fonction_tuteur" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="telephone_tuteur">Téléphone :</label>
                                    <input type="tel" class="form-control" id="telephone_tuteur" name="telephone_tuteur" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email_tuteur">Email :</label>
                                    <input type="email" class="form-control" id="email_tuteur" name="email_tuteur" required>
                                </div>
                            </div>
                            
                            <!-- Section DIRECTEUR / DIRECTRICE -->
                            <h5 class="border-bottom pb-2 mb-3">👩‍💼 DIRECTEUR / DIRECTRICE</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="civilite_directeur">Civilité :</label>
                                    <select class="form-control" id="civilite_directeur" name="civilite_directeur" required>
                                        <option value="">Sélectionnez</option>
                                        <option value="M.">M.</option>
                                        <option value="Mme">Mme</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="nom_prenom_directeur">Nom/Prénom :</label>
                                    <input type="text" class="form-control" id="nom_prenom_directeur" name="nom_prenom_directeur" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email_directeur">Email :</label>
                                    <input type="email" class="form-control" id="email_directeur" name="email_directeur" required>
                                </div>
                            </div>
                            
                            <!-- Section Activités -->
                            <h5 class="border-bottom pb-2 mb-3">📋 DESCRIPTION DES ACTIVITÉS</h5>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="description_activites">Description des principales activités qui seront confiées à l'étudiant(e) :</label>
                                    <textarea class="form-control" id="description_activites" name="description_activites" rows="4" required></textarea>
                                </div>
                            </div>
                            
                            <!-- Section Horaires -->
                            <h5 class="border-bottom pb-2 mb-3">🕒 JOURS ET HORAIRES DE TRAVAIL</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="horaire_lundi">Lundi :</label>
                                    <input type="text" class="form-control" id="horaire_lundi" name="horaire_lundi" placeholder="ex: 9h-12h, 14h-17h">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="horaire_mardi">Mardi :</label>
                                    <input type="text" class="form-control" id="horaire_mardi" name="horaire_mardi" placeholder="ex: 9h-12h, 14h-17h">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="horaire_mercredi">Mercredi :</label>
                                    <input type="text" class="form-control" id="horaire_mercredi" name="horaire_mercredi" placeholder="ex: 9h-12h, 14h-17h">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="horaire_jeudi">Jeudi :</label>
                                    <input type="text" class="form-control" id="horaire_jeudi" name="horaire_jeudi" placeholder="ex: 9h-12h, 14h-17h">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="horaire_vendredi">Vendredi :</label>
                                    <input type="text" class="form-control" id="horaire_vendredi" name="horaire_vendredi" placeholder="ex: 9h-12h, 14h-17h">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="horaire_samedi">Samedi :</label>
                                    <input type="text" class="form-control" id="horaire_samedi" name="horaire_samedi" placeholder="ex: 9h-12h, 14h-17h">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="horaire_dimanche">Dimanche :</label>
                                    <input type="text" class="form-control" id="horaire_dimanche" name="horaire_dimanche" placeholder="ex: 9h-12h, 14h-17h">
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="horaires_variables" name="horaires_variables">
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
                                    <textarea class="form-control" id="remarques" name="remarques" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-3">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-save mr-2"></i> Enregistrer la préconvention
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button type="button" class="btn btn-success btn-block">
                                        <i class="fa fa-envelope mr-2"></i> Envoyer au référent
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br><br>
</body>
</html>