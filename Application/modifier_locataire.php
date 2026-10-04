<?php
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: locataire.php");
    exit();
}

$id_locataire = mysqli_real_escape_string($conn, $_GET['id']);

// Traitement de la modification
if (isset($_POST['modifier'])) {
    $nom_loca = mysqli_real_escape_string($conn, $_POST['nom_loca']);
    $prenom_loca = mysqli_real_escape_string($conn, $_POST['prenom_loca']);
    $telephone_loca = mysqli_real_escape_string($conn, $_POST['telephone_loca']);
    $contact_urgent = mysqli_real_escape_string($conn, $_POST['contact_urgent']);
    $date_creation = mysqli_real_escape_string($conn, $_POST['date_creation']);

    $req_update = "UPDATE locataire SET 
                    nom_loca = '$nom_loca',
                    prenom_loca = '$prenom_loca',
                    telephone_loca = '$telephone_loca',
                    contact_urgent = '$contact_urgent',
                    date_creation = '$date_creation'
                   WHERE id_loca = '$id_locataire'";

    if (mysqli_query($conn, $req_update)) {
        $success = "Locataire modifié avec succès !";
        // Rafraîchir les données après modification
        $req_locataire = "SELECT * FROM locataire WHERE id_loca = '$id_locataire'";
        $res_locataire = mysqli_query($conn, $req_locataire);
        $locataire = mysqli_fetch_assoc($res_locataire);
    } else {
        $error = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}

// Récupérer les informations du locataire
$req_locataire = "SELECT * FROM locataire WHERE id_loca = '$id_locataire'";
$res_locataire = mysqli_query($conn, $req_locataire);

if (!$res_locataire || mysqli_num_rows($res_locataire) == 0) {
    header("Location: locataire.php");
    exit();
}

$locataire = mysqli_fetch_assoc($res_locataire);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Locataire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            margin-top: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #4caf50, #2e7d32); /* Couleur verte */
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .card-title {
            font-weight: 700;
            margin-bottom: 0;
        }
        .card-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #4caf50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        .back-btn {
            margin-right: 15px;
            background: #6c757d;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .required-label:after {
            content: " *";
            color: red;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .alert {
            border-radius: 10px;
        }
        .contact-icons {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .input-group-icon {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-edit me-2"></i>Modifier un Locataire</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Nom</label>
                                    <input type="text" name="nom_loca" class="form-control" 
                                           value="<?= htmlspecialchars($locataire['nom_loca']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Prénom</label>
                                    <input type="text" name="prenom_loca" class="form-control" 
                                           value="<?= htmlspecialchars($locataire['prenom_loca']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3 input-group-icon">
                                <label class="form-label required-label">Téléphone</label>
                                <input type="tel" name="telephone_loca" class="form-control" 
                                       value="<?= htmlspecialchars($locataire['telephone_loca']) ?>" required>
                                <div class="contact-icons">
                                    <i class="fas fa-phone"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3 input-group-icon">
                                <label class="form-label required-label">Contact Urgent</label>
                                <input type="tel" name="contact_urgent" class="form-control" 
                                       value="<?= htmlspecialchars($locataire['contact_urgent']) ?>" required>
                                <div class="contact-icons">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Date de création</label>
                                <input type="date" name="date_creation" class="form-control" 
                                       value="<?= htmlspecialchars($locataire['date_creation']) ?>" required>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="locataire.php" class="btn back-btn">
                                    <i class="fas fa-arrow-left me-2"></i> Annuler
                                </a>
                                <button type="submit" name="modifier" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
     <script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
<!-- jquery slimscroll js -->
<script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
<!-- modernizr js -->
<script type="text/javascript" src="assets/js/modernizr/modernizr.js"></script>
<!-- am chart -->
<script src="assets/pages/widget/amchart/amcharts.min.js"></script>
<script src="assets/pages/widget/amchart/serial.min.js"></script>
<!-- Chart js -->
<script type="text/javascript" src="assets/js/chart.js/Chart.js"></script>
<!-- Todo js -->
<script type="text/javascript " src="assets/pages/todo/todo.js "></script>
<!-- Custom js -->
<script type="text/javascript" src="assets/pages/dashboard/custom-dashboard.min.js"></script>
<script type="text/javascript" src="assets/js/script.js"></script>
<script type="text/javascript " src="assets/js/SmoothScroll.js"></script>
<script src="assets/js/pcoded.min.js"></script>
<script src="assets/js/vartical-demo.js"></script>
<script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
</body>
</html>