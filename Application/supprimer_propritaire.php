<?php
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: propritaires.php");
    exit();
}

$id_pro = mysqli_real_escape_string($conn, $_GET['id']);

// Traitement de la suppression
if (isset($_POST['supprimer'])) {
    $req_delete = "DELETE FROM propritaire WHERE id_pro = '$id_pro'";
    
    if (mysqli_query($conn, $req_delete)) {
        $success = "Propriétaire supprimé avec succès !";
    } else {
        $error = "Erreur lors de la suppression: " . mysqli_error($conn);
    }
} else {
    // Récupérer les informations du propriétaire pour confirmation
    $req_proprietaire = "SELECT * FROM propritaire WHERE id_pro = '$id_pro'";
    $res_proprietaire = mysqli_query($conn, $req_proprietaire);
    
    if (!$res_proprietaire || mysqli_num_rows($res_proprietaire) == 0) {
        header("Location: propritaires.php");
        exit();
    }
    
    $proprietaire = mysqli_fetch_assoc($res_proprietaire);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Propriétaire</title>
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
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
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
        .payment-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .detail-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .detail-value {
            font-size: 1.1rem;
            color: #212529;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 75, 43, 0.4);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Supprimer un Propriétaire</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                            <div class="d-flex justify-content-center mt-4">
                                <a href="propritaires.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Retour aux Propriétaires
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5 class="alert-heading">Attention !</h5>
                                <p>Vous êtes sur le point de supprimer définitivement ce propriétaire. Cette action est irréversible.</p>
                            </div>
                            
                            <div class="payment-details">
                                <h5 class="mb-4">Détails du Propriétaire</h5>
                                
                                <div class="detail-item">
                                    <div class="detail-label">ID Propriétaire</div>
                                    <div class="detail-value"><?= htmlspecialchars($proprietaire['id_pro']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Nom</div>
                                    <div class="detail-value"><?= htmlspecialchars($proprietaire['nom_pro']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Prénom</div>
                                    <div class="detail-value"><?= htmlspecialchars($proprietaire['prenom_pro']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Adresse</div>
                                    <div class="detail-value"><?= htmlspecialchars($proprietaire['adresse_pro']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Date d'inscription</div>
                                    <div class="detail-value"><?= htmlspecialchars($proprietaire['date_creation']) ?></div>
                                </div>
                            </div>
                            
                            <form method="post" action="">
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="propritaires.php" class="btn back-btn">
                                        <i class="fas fa-times me-2"></i> Annuler
                                    </a>
                                    <button type="submit" name="supprimer" class="btn btn-danger">
                                        <i class="fas fa-trash-alt me-2"></i> Confirmer la suppression
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
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