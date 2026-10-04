<?php
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: demandeur.php");
    exit();
}

$id_demandeur = mysqli_real_escape_string($conn, $_GET['id']);

// Traitement de la modification
if (isset($_POST['modifier'])) {
    $bien_demande = mysqli_real_escape_string($conn, $_POST['bien_demande']);
    $quartier = mysqli_real_escape_string($conn, $_POST['quartier']);
    $montant = mysqli_real_escape_string($conn, $_POST['montant']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);

    $req_update = "UPDATE demandeur SET 
                    bien_demande = '$bien_demande',
                    quartier = '$quartier',
                    montant = '$montant',
                    description = '$description',
                    date_operation = '$date_operation'
                   WHERE id_demandeur = '$id_demandeur'";

    if (mysqli_query($conn, $req_update)) {
        $success = "Demande modifiée avec succès !";
        // Rafraîchir les données après modification
        $req_demande = "SELECT * FROM demandeur WHERE id_demandeur = '$id_demandeur'";
        $res_demande = mysqli_query($conn, $req_demande);
        $demande = mysqli_fetch_assoc($res_demande);
    } else {
        $error = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}

// Récupérer les informations de la demande
$req_demande = "SELECT * FROM demandeur WHERE id_demandeur = '$id_demandeur'";
$res_demande = mysqli_query($conn, $req_demande);

if (!$res_demande || mysqli_num_rows($res_demande) == 0) {
    header("Location: demandeur.php");
    exit();
}

$demande = mysqli_fetch_assoc($res_demande);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Demande</title>
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
            background: linear-gradient(135deg, #ff9800, #f57c00); /* Couleur orange */
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
            border-color: #ff9800;
            box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-edit me-2"></i>Modifier une Demande</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label class="form-label required-label">Bien demandé</label>
                                <input type="text" name="bien_demande" class="form-control" 
                                       value="<?= htmlspecialchars($demande['bien_demande']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Quartier</label>
                                <input type="text" name="quartier" class="form-control" 
                                       value="<?= htmlspecialchars($demande['quartier']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Montant (FCFA)</label>
                                    <input type="number" name="montant" class="form-control" 
                                           value="<?= htmlspecialchars($demande['montant']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Date de l'opération</label>
                                    <input type="date" name="date_operation" class="form-control" 
                                           value="<?= htmlspecialchars($demande['date_operation']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" required><?= 
                                    htmlspecialchars($demande['description']) ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="demandeur.php" class="btn back-btn">
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
</body>
</html>