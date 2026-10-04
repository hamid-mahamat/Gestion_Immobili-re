<?php
// Démarrer la session au tout début
session_start();

// Vérifier si l'utilisateur est connecté AVANT tout output
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: connexion.php");
    exit();
}

include('entete.php');

$id_paiement = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_vente = isset($_GET['id_vente']) ? intval($_GET['id_vente']) : 0;

// Redirection si paramètres manquants
if ($id_paiement <= 0 || $id_vente <= 0) {
    header("Location: paiement_achat.php");
    exit();
}

// Récupérer les informations du paiement à modifier
$req_paiement = "SELECT * FROM payement WHERE id_payement = ? AND id_vente = ?";
$stmt_paiement = mysqli_prepare($conn, $req_paiement);
mysqli_stmt_bind_param($stmt_paiement, "ii", $id_paiement, $id_vente);
mysqli_stmt_execute($stmt_paiement);
$paiement = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_paiement));
mysqli_stmt_close($stmt_paiement);

if (!$paiement) {
    $_SESSION['error_message'] = 'Paiement non trouvé!';
    header("Location: paiement_achat.php");
    exit();
}

// Récupérer les informations de la vente
$req_vente = "SELECT 
    v.id_vente, 
    v.prix_vente,
    v.nom_acheteur,
    v.prenom_acheteur,
    b.type AS type_bien,
    b.adresse,
    (SELECT COALESCE(SUM(montant), 0) FROM payement WHERE id_vente = v.id_vente) AS total_paye,
    (v.prix_vente - (SELECT COALESCE(SUM(montant), 0) FROM payement WHERE id_vente = v.id_vente)) AS reste_a_payer
FROM ventes v 
INNER JOIN bien b ON v.id_bien = b.id_bien 
WHERE v.id_vente = ?";

$stmt_vente = mysqli_prepare($conn, $req_vente);
mysqli_stmt_bind_param($stmt_vente, "i", $id_vente);
mysqli_stmt_execute($stmt_vente);
$vente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_vente));
mysqli_stmt_close($stmt_vente);

if (!$vente) {
    $_SESSION['error_message'] = 'Vente non trouvée!';
    header("Location: paiement_achat.php");
    exit();
}

// Variables pour le formulaire
$montant = $paiement['montant'];
$mode_paiement = $paiement['mode_payement'];
$date_paiement = $paiement['date_operation'];
$erreurs = [];
$success = false;

// Calculer le montant maximum autorisé (reste à payer + montant actuel du paiement)
$montant_max = $vente['reste_a_payer'] + $paiement['montant'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $montant = floatval(str_replace(' ', '', $_POST['montant']));
    $mode_paiement = mysqli_real_escape_string($conn, $_POST['mode_paiement']);
    $date_paiement = mysqli_real_escape_string($conn, $_POST['date_paiement']);
    
    // Validation du montant
    if ($montant <= 0) {
        $erreurs[] = "Le montant doit être supérieur à 0.";
    }
    
    if ($montant > $montant_max) {
        $erreurs[] = "Le montant ne peut pas dépasser " . number_format($montant_max, 2, ',', ' ') . " CFA.";
    }
    
    // Validation du mode de paiement
    if (empty($mode_paiement)) {
        $erreurs[] = "Veuillez sélectionner un mode de paiement.";
    }
    
    // Validation de la date
    if (empty($date_paiement)) {
        $erreurs[] = "Veuillez sélectionner une date.";
    }
    
    // Si aucune erreur, mise à jour du paiement
    if (empty($erreurs)) {
        $req_update = "UPDATE payement SET montant = ?, mode_payement = ?, date_operation = ? WHERE id_payement = ? AND id_vente = ?";
        $stmt_update = mysqli_prepare($conn, $req_update);
        mysqli_stmt_bind_param($stmt_update, "dssii", $montant, $mode_paiement, $date_paiement, $id_paiement, $id_vente);
        
        if (mysqli_stmt_execute($stmt_update)) {
            $success = true;
            
            // Mettre à jour les informations de la vente après modification
            $stmt_vente = mysqli_prepare($conn, $req_vente);
            mysqli_stmt_bind_param($stmt_vente, "i", $id_vente);
            mysqli_stmt_execute($stmt_vente);
            $vente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_vente));
            mysqli_stmt_close($stmt_vente);
            
            // Recalculer le montant maximum
            $montant_max = $vente['reste_a_payer'] + $montant;
        } else {
            $erreurs[] = "Erreur lors de la mise à jour du paiement : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update);
    }
}

// Calculer le pourcentage payé
$pourcentage_paye = $vente['prix_vente'] > 0 ? ($vente['total_paye'] / $vente['prix_vente']) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Paiement - Vente #<?php echo $id_vente; ?></title>
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-bg: #f8f9fc;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
            --border-radius: 8px;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(120deg, #4361ee, #3a0ca3);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            border-radius: 0 0 15px 15px;
        }
        
        .info-card {
            border-left: 4px solid #4361ee;
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
        }
        
        .form-card {
            border-left: 4px solid #28a745;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .progress {
            height: 12px;
            border-radius: 10px;
            margin: 15px 0;
        }
        
        .stat-badge {
            font-size: 0.9rem;
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .amount-display {
            font-size: 1.4rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .btn-custom {
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-save {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-cancel {
            background: linear-gradient(45deg, #6c757d, #adb5bd);
            color: white;
            border: none;
        }
        
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .alert-success {
            border-radius: 10px;
            border: none;
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
            color: #155724;
            font-weight: 500;
        }
        
        .alert-danger {
            border-radius: 10px;
            border: none;
            background: linear-gradient(45deg, #f8d7da, #f5c6cb);
            color: #721c24;
            font-weight: 500;
        }
        
        .info-badge {
            background: linear-gradient(45deg, #4361ee, #3a0ca3);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .financial-summary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">
                        <i class="fas fa-edit me-2"></i>
                        Modifier le Paiement
                    </h1>
                    <p class="mb-0">Vente #<?php echo $id_vente; ?> - Paiement #<?php echo $id_paiement; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="details_vente.php?id_vente=<?php echo $id_vente; ?>" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i> Retour aux détails
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Messages d'alerte -->
        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Paiement modifié avec succès!</h5>
                    <p class="mb-0">Redirection vers les détails de la vente...</p>
                </div>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'details_vente.php?id_vente=<?php echo $id_vente; ?>';
                }, 2000);
            </script>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Erreurs</h5>
                <ul class="mb-0">
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo $erreur; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Informations de la vente -->
            <div class="col-lg-5 mb-4">
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informations de la Vente</h4>
                    
                    <div class="info-badge">
                        Vente #<?php echo $id_vente; ?>
                    </div>
                    
                    <div class="financial-summary">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-muted">Prix Total</small>
                                <div class="amount-display text-primary">
                                    <?php echo number_format($vente['prix_vente'], 0, ',', ' '); ?> CFA
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Reste à Payer</small>
                                <div class="amount-display <?php echo $vente['reste_a_payer'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo number_format($vente['reste_a_payer'], 0, ',', ' '); ?> CFA
                                </div>
                            </div>
                        </div>
                        
                        <div class="progress">
                            <div class="progress-bar <?php echo $pourcentage_paye >= 100 ? 'bg-success' : ($pourcentage_paye >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                 style="width: <?php echo min($pourcentage_paye, 100); ?>%"
                                 role="progressbar">
                            </div>
                        </div>
                        <div class="text-center">
                            <small class="text-muted"><?php echo number_format($pourcentage_paye, 1); ?>% payé</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <p><strong>Acheteur:</strong> <?php echo htmlspecialchars($vente['nom_acheteur']) . ' ' . htmlspecialchars($vente['prenom_acheteur']); ?></p>
                        <p><strong>Bien:</strong> <?php echo htmlspecialchars($vente['type_bien']); ?> - <?php echo htmlspecialchars($vente['adresse']); ?></p>
                        <p><strong>Total payé:</strong> <span class="text-success"><?php echo number_format($vente['total_paye'], 0, ',', ' '); ?> CFA</span></p>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="fas fa-lightbulb me-2"></i>Instructions</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Modifiez le montant du paiement</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Sélectionnez le mode de paiement</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Vérifiez la date du paiement</li>
                        <li><i class="fas fa-print text-primary me-2"></i> Imprimez la facture après modification</li>
                    </ul>
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="col-lg-7">
                <div class="form-card">
                    <h4 class="mb-4"><i class="fas fa-edit me-2"></i>Modifier le Paiement</h4>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="montant" class="form-label">
                                    <i class="fas fa-money-bill-wave me-1"></i>Montant du Paiement
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="montant" 
                                       name="montant" 
                                       value="<?php echo htmlspecialchars($montant); ?>" 
                                       step="0.01"
                                       min="0.01"
                                       max="<?php echo $montant_max; ?>"
                                       required>
                                <div class="form-text">
                                    Montant maximum: <strong><?php echo number_format($montant_max, 0, ',', ' '); ?> CFA</strong>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mode_paiement" class="form-label">
                                    <i class="fas fa-credit-card me-1"></i>Mode de Paiement
                                </label>
                                <select class="form-select" id="mode_paiement" name="mode_paiement" required>
                                    <option value="">Sélectionnez...</option>
                                    <option value="Espèces" <?php echo $mode_paiement == 'Espèces' ? 'selected' : ''; ?>>Espèces</option>
                                    <option value="Chèque" <?php echo $mode_paiement == 'Chèque' ? 'selected' : ''; ?>>Chèque</option>
                                    <option value="Virement" <?php echo $mode_paiement == 'Virement' ? 'selected' : ''; ?>>Virement</option>
                                    <option value="Carte Bancaire" <?php echo $mode_paiement == 'Carte Bancaire' ? 'selected' : ''; ?>>Carte Bancaire</option>
                                    <option value="Mobile Money" <?php echo $mode_paiement == 'Mobile Money' ? 'selected' : ''; ?>>Mobile Money</option>
                                    <option value="Autre" <?php echo $mode_paiement == 'Autre' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="date_paiement" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Date du Paiement
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_paiement" 
                                   name="date_paiement" 
                                   value="<?php echo htmlspecialchars($date_paiement); ?>" 
                                   required>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="details_vente.php?id_vente=<?php echo $id_vente; ?>" class="btn btn-cancel btn-custom me-md-2">
                                <i class="fas fa-times me-2"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-save btn-custom">
                                <i class="fas fa-save me-2"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Actions rapides -->
                <div class="info-card mt-4">
                    <h5><i class="fas fa-bolt me-2"></i>Actions Rapides</h5>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="generer_facture_vente.php?id_vente=<?php echo $id_vente; ?>" 
                           class="btn btn-outline-primary" 
                           target="_blank">
                            <i class="fas fa-print me-2"></i> Imprimer Facture
                        </a>
                        <a href="ajouter_paiement.php?id_vente=<?php echo $id_vente; ?>" 
                           class="btn btn-outline-success">
                            <i class="fas fa-plus me-2"></i> Nouveau Paiement
                        </a>
                        <a href="details_vente.php?id_vente=<?php echo $id_vente; ?>" 
                           class="btn btn-outline-info">
                            <i class="fas fa-eye me-2"></i> Voir Détails
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mettre à jour le montant maximum dynamiquement
        document.addEventListener('DOMContentLoaded', function() {
            const montantInput = document.getElementById('montant');
            const maxAmount = <?php echo $montant_max; ?>;
            
            montantInput.addEventListener('input', function() {
                if (parseFloat(this.value) > maxAmount) {
                    this.setCustomValidity(`Le montant ne peut pas dépasser ${maxAmount.toLocaleString('fr-FR')} CFA`);
                } else {
                    this.setCustomValidity('');
                }
            });
            
            // Afficher la date d'aujourd'hui par défaut si le champ est vide
            const dateInput = document.getElementById('date_paiement');
            if (!dateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
        });
    </script>
</body>
</html>