<?php
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: payement.php");
    exit();
}

$id_paiement = mysqli_real_escape_string($conn, $_GET['id']);

// Traitement de la modification
if (isset($_POST['modifier'])) {
    $id_location = mysqli_real_escape_string($conn, $_POST['id_location']);
    $montant = mysqli_real_escape_string($conn, $_POST['montant']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);
    $mode_payement = mysqli_real_escape_string($conn, $_POST['mode_payement']);

    $req_update = "UPDATE payement SET 
                    id_location = '$id_location',
                    montant = '$montant',
                    date_operation = '$date_operation',
                    mode_payement = '$mode_payement'
                   WHERE id_payement = '$id_paiement'";

    if (mysqli_query($conn, $req_update)) {
        $success = "Paiement modifié avec succès !";
        // Rafraîchir les données après modification
        $req_paiement = "SELECT * FROM payement WHERE id_payement = '$id_paiement'";
        $res_paiement = mysqli_query($conn, $req_paiement);
        $paiement = mysqli_fetch_assoc($res_paiement);
    } else {
        $error = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}

// Récupérer les informations du paiement

$req_paiement = "SELECT * FROM payement WHERE id_payement = '$id_paiement'";
$res_paiement = mysqli_query($conn, $req_paiement);

if (!$res_paiement || mysqli_num_rows($res_paiement) == 0) {
    header("Location: payement.php");
    exit();
}

$paiement = mysqli_fetch_assoc($res_paiement);

// Récupérer la liste des locations pour le select

$req_locations = "SELECT
    loc.id_location,
    b.type AS type_bien,
    CONCAT(loca.nom_loca, ' ', loca.prenom_loca) AS nom_complet
FROM
    location loc
JOIN
    bien b ON loc.id_bien = b.id_bien
JOIN
    locataire loca ON loc.id_loca = loca.id_loca
ORDER BY loc.id_location DESC";
$res_locations = mysqli_query($conn, $req_locations);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Paiement</title>
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
            background: linear-gradient(135deg, #4361ee, #3a0ca3); /* Couleur bleue/violette */
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
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
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
        .input-group-text {
            background-color: #e9ecef;
        }
        .card-header-icon {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        .currency-input {
            position: relative;
        }
        .currency-symbol {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-money-bill-wave card-header-icon"></i>Modifier un Paiement
                        </h3>
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
                                <label class="form-label required-label">Location (Bien - Locataire)</label>
                                <select name="id_location" class="form-control" required>
                                    <option value="">Sélectionner une location</option>
                                    <?php while($location = mysqli_fetch_assoc($res_locations)): ?>
                                        <?php $selected = ($location['id_location'] == $paiement['id_location']) ? 'selected' : ''; ?>
                                        <option value="<?= $location['id_location'] ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($location['type_bien']) ?> - <?= htmlspecialchars($location['nom_complet']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3 currency-input">
                                <label class="form-label required-label">Montant</label>
                                <input type="number" name="montant" class="form-control" 
                                       value="<?= htmlspecialchars($paiement['montant']) ?>" step="0.01" min="0" required>
                                <div class="currency-symbol">
                                    <i class="fas fa-euro-sign"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Mode de paiement</label>
                                <select name="mode_payement" class="form-control" required>
                                    <option value="">Sélectionnez un mode</option>
                                    <option value="Virement" <?= ($paiement['mode_payement'] == 'Virement') ? 'selected' : '' ?>>Virement</option>
                                    <option value="Chèque" <?= ($paiement['mode_payement'] == 'Chèque') ? 'selected' : '' ?>>Chèque</option>
                                    <option value="Espèces" <?= ($paiement['mode_payement'] == 'Espèces') ? 'selected' : '' ?>>Espèces</option>
                                    <option value="Carte Bancaire" <?= ($paiement['mode_payement'] == 'Carte Bancaire') ? 'selected' : '' ?>>Carte Bancaire</option>
                                    <option value="Prélèvement" <?= ($paiement['mode_payement'] == 'Prélèvement') ? 'selected' : '' ?>>Prélèvement</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Date de l'opération</label>
                                <input type="date" name="date_operation" class="form-control" 
                                       value="<?= htmlspecialchars($paiement['date_operation']) ?>" required>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="payement.php" class="btn back-btn">
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
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script type="text/javascript" src="assets/js/modernizr/modernizr.js"></script>
    <script src="assets/pages/widget/amchart/amcharts.min.js"></script>
    <script src="assets/pages/widget/amchart/serial.min.js"></script>
    <script type="text/javascript" src="assets/js/chart.js/Chart.js"></script>
    <script type="text/javascript " src="assets/pages/todo/todo.js "></script>
    <script type="text/javascript" src="assets/pages/dashboard/custom-dashboard.min.js"></script>
    <script type="text/javascript" src="assets/js/script.js"></script>
    <script type="text/javascript " src="assets/js/SmoothScroll.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/vartical-demo.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Validation du montant
            $('input[name="montant"]').on('change', function() {
                if ($(this).val() <= 0) {
                    alert('Le montant doit être supérieur à zéro');
                    $(this).val('');
                }
            });
        });
    </script>
</body>
</html>