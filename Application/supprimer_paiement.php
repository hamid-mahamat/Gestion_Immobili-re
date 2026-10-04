<?php
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: payement.php");
    exit();
}

$id_payement = mysqli_real_escape_string($conn, $_GET['id']);

// Traitement de la suppression
if (isset($_POST['supprimer'])) {
    $req_delete = "DELETE FROM payement WHERE id_payement = '$id_payement'";
    
    if (mysqli_query($conn, $req_delete)) {
        $success = "Paiement supprimé avec succès !";
    } else {
        $error = "Erreur lors de la suppression: " . mysqli_error($conn);
    }
} else {
    // Récupérer les informations du paiement avec jointures
    $req_payment = "SELECT 
        p.id_payement, 
        p.montant, 
        p.date_operation, 
        p.mode_payement,
        COALESCE(b.type, 'Non associé') AS type_bien,
        COALESCE(CONCAT(loca.nom_loca, ' ', loca.prenom_loca), 'Non associé') AS nom_complet_locataire
    FROM payement p
    LEFT JOIN location loc ON p.id_location = loc.id_location
    LEFT JOIN bien b ON loc.id_bien = b.id_bien
    LEFT JOIN locataire loca ON loc.id_loca = loca.id_loca
    WHERE p.id_payement = '$id_payement'";
    
    $res_payment = mysqli_query($conn, $req_payment);
    
    if (!$res_payment || mysqli_num_rows($res_payment) == 0) {
        header("Location: payement.php");
        exit();
    }
    
    $payment = mysqli_fetch_assoc($res_payment);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Paiement</title>
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
                        <h3 class="card-title">Supprimer un Paiement</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                            <div class="d-flex justify-content-center mt-4">
                                <a href="payement.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Retour aux Paiements
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5 class="alert-heading">Attention !</h5>
                                <p>Vous êtes sur le point de supprimer définitivement ce paiement. Cette action est irréversible.</p>
                            </div>
                            
                            <div class="payment-details">
                                <h5 class="mb-4">Détails du Paiement</h5>
                                
                                <div class="detail-item">
                                    <div class="detail-label">ID Paiement</div>
                                    <div class="detail-value">Pay-<?= sprintf('%04d', htmlspecialchars($payment['id_payement'])) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Montant</div>
                                    <div class="detail-value"><?= number_format($payment['montant'], 2, ',', ' ') ?> CFA</div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Type de bien</div>
                                    <div class="detail-value"><?= htmlspecialchars($payment['type_bien']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Locataire</div>
                                    <div class="detail-value"><?= htmlspecialchars($payment['nom_complet_locataire']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Mode de paiement</div>
                                    <div class="detail-value"><?= htmlspecialchars($payment['mode_payement']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Date de l'opération</div>
                                    <div class="detail-value"><?= date('d/m/Y', strtotime($payment['date_operation'])) ?></div>
                                </div>
                            </div>
                            
                            <form method="post" action="">
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="payement.php" class="btn back-btn">
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
</body>
</html>