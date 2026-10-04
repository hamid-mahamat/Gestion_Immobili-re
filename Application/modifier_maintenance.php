<?php
include('entete.php');


if (!isset($_GET['id'])) {
    header("Location: maintenance.php");
    exit();
}

$id_main = mysqli_real_escape_string($conn, $_GET['id']);


if (isset($_POST['modifier'])) {
    $id_bien = mysqli_real_escape_string($conn, $_POST['id_bien']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_demande = mysqli_real_escape_string($conn, $_POST['date_demande']);
    $cout = mysqli_real_escape_string($conn, $_POST['cout']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);
    $statut = mysqli_real_escape_string($conn, $_POST['statut']);

    $req_update = "UPDATE maintenance SET 
                    id_bien = '$id_bien',
                    description = '$description',
                    date_demande = '$date_demande',
                    cout = '$cout',
                    date_operation = '$date_operation',
                    statut = '$statut'
                   WHERE id_main = '$id_main'";

    if (mysqli_query($conn, $req_update)) {
        $success = "Maintenance modifiée avec succès !";
        
        $req_maintenance = "SELECT maintenance.*, bien.type 
                FROM maintenance 
                JOIN bien ON maintenance.id_bien = bien.id_bien 
                WHERE maintenance.id_main = '$id_main'";
        $res_maintenance = mysqli_query($conn, $req_maintenance);
        $maintenance = mysqli_fetch_assoc($res_maintenance);
    } else {
        $error = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}


$req_maintenance = "SELECT maintenance.*, bien.type 
        FROM maintenance 
        JOIN bien ON maintenance.id_bien = bien.id_bien 
        WHERE maintenance.id_main = '$id_main'";

$res_maintenance = mysqli_query($conn, $req_maintenance);

if (!$res_maintenance || mysqli_num_rows($res_maintenance) == 0) {
    header("Location: maintenance.php");
    exit();
}

$maintenance = mysqli_fetch_assoc($res_maintenance);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Maintenance</title>
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
            background: linear-gradient(135deg, #2196f3, #0d47a1); /* Couleur bleue */
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
            border-color: #2196f3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2196f3, #0d47a1);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
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
        .input-group-icon {
            position: relative;
        }
        .card-header-icon {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        .form-icon {
            position: absolute;
            top: 50%;
            right: 15px;
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
                            <i class="fas fa-tools card-header-icon"></i>Modifier une Maintenance
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
                                <label class="form-label required-label">Type de bien</label>
                                <select name="id_bien" class="form-control" required>
                                    <option value="">Sélectionnez un bien</option>
                                    <?php
                                    $req_bien = "SELECT id_bien, type FROM bien";
                                    $res_bien = mysqli_query($conn, $req_bien);
                                    while($bien = mysqli_fetch_assoc($res_bien)){
                                        $selected = ($bien['id_bien'] == $maintenance['id_bien']) ? 'selected' : '';
                                        echo '<option value="'.$bien['id_bien'].'" '.$selected.'>'.$bien['type'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3 input-group-icon">
                                <label class="form-label required-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($maintenance['description']) ?></textarea>
                                <div class="form-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Date de demande</label>
                                    <div class="input-group-icon">
                                        <input type="date" name="date_demande" class="form-control" 
                                               value="<?= htmlspecialchars($maintenance['date_demande']) ?>" required>
                                        <div class="form-icon">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Coût (FCFA)</label>
                                    <div class="input-group-icon">
                                        <input type="number" name="cout" class="form-control" 
                                               value="<?= htmlspecialchars($maintenance['cout']) ?>" step="0.01" min="0" required>
                                        <div class="form-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Date d'opération</label>
                                    <div class="input-group-icon">
                                        <input type="date" name="date_operation" class="form-control" 
                                               value="<?= htmlspecialchars($maintenance['date_operation']) ?>" required>
                                        <div class="form-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Statut</label>
                                    <select name="statut" class="form-control" required>
                                        <option value="Terminé" <?= ($maintenance['statut'] == 'Terminé') ? 'selected' : '' ?>>Terminé</option>
                                        <option value="en cour" <?= ($maintenance['statut'] == 'en cour') ? 'selected' : '' ?>>En cours</option>
                                        <option value="annuler" <?= ($maintenance['statut'] == 'annuler') ? 'selected' : '' ?>>Annulé</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="maintenance.php" class="btn back-btn">
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