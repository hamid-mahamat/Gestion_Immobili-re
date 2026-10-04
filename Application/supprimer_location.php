<?php
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: location.php");
    exit();
}

$id_location = mysqli_real_escape_string($conn, $_GET['id']);

// Traitement de la suppression
if (isset($_POST['supprimer'])) {
    $req_delete = "DELETE FROM location WHERE id_location = '$id_location'";
    
    if (mysqli_query($conn, $req_delete)) {
        $success = "Location supprimée avec succès !";
    } else {
        $error = "Erreur lors de la suppression: " . mysqli_error($conn);
    }
} else {
    // Récupérer les informations de la location
    $req_location = "SELECT location.*, 
                    CONCAT(locataire.nom_loca, ' ', locataire.prenom_loca) AS locataire,
                    bien.type AS type_bien
                    FROM location 
                    LEFT JOIN locataire ON location.id_loca = locataire.id_loca
                    LEFT JOIN bien ON location.id_bien = bien.id_bien
                    WHERE id_location = '$id_location'";
    
    $res_location = mysqli_query($conn, $req_location);
    
    if (!$res_location || mysqli_num_rows($res_location) == 0) {
        header("Location: location.php");
        exit();
    }
    
    $location = mysqli_fetch_assoc($res_location);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer une Location</title>
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
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
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
                        <h3 class="card-title">Supprimer une Location</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                            <div class="d-flex justify-content-center mt-4">
                                <a href="location.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Retour aux Locations
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5 class="alert-heading">Attention !</h5>
                                <p>Vous êtes sur le point de supprimer définitivement cette location. Cette action est irréversible.</p>
                            </div>
                            
                            <div class="payment-details">
                                <h5 class="mb-4">Détails de la Location</h5>
                                
                                <div class="detail-item">
                                    <div class="detail-label">ID Location</div>
                                    <div class="detail-value">CONTR-<?= str_pad($location['id_location'], 4, '0', STR_PAD_LEFT) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Type de Bien</div>
                                    <div class="detail-value"><?= htmlspecialchars($location['type_bien']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Locataire</div>
                                    <div class="detail-value"><?= htmlspecialchars($location['locataire']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Date Début</div>
                                    <div class="detail-value"><?= date('d/m/Y', strtotime($location['date_debut'])) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Date Fin</div>
                                    <div class="detail-value"><?= date('d/m/Y', strtotime($location['date_fin'])) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Loyer Mensuel</div>
                                    <div class="detail-value"><?= number_format($location['loyer_mensuel'], 2, ',', ' ') ?> CFA</div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Caution</div>
                                    <div class="detail-value"><?= number_format($location['caution'], 2, ',', ' ') ?> CFA</div>
                                </div>
                            </div>
                            
                            <form method="post" action="">
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="location.php" class="btn back-btn">
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
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script type="text/javascript" src="assets/js/modernizr/modernizr.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
</body>
</html>