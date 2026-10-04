<?php
include('entete.php');

$id_vente = isset($_GET['id_vente']) ? intval($_GET['id_vente']) : 0;

// Debug: afficher l'ID vente reçu
error_log("ID Vente reçu: " . $id_vente);

if ($id_vente <= 0) {
    header("Location: paiement_achat.php");
    exit();
}

// Récupérer les informations détaillées de la vente
$req_vente = "SELECT
    v.id_vente,
    b.type AS type_bien,
    b.adresse,
    b.surface,
    b.nb_chambre,
    v.nom_acheteur,
    v.prenom_acheteur,
    v.contact_acheteur,
    v.adresse_acheteur,
    v.prix_vente,
    v.date_operation AS date_vente,
    (SELECT COALESCE(SUM(montant), 0) FROM payement WHERE id_vente = v.id_vente) AS total_paye,
    (v.prix_vente - (SELECT COALESCE(SUM(montant), 0) FROM payement WHERE id_vente = v.id_vente)) AS reste_a_payer,
    (SELECT COUNT(*) FROM payement WHERE id_vente = v.id_vente) AS nb_paiements,
    (SELECT MAX(date_operation) FROM payement WHERE id_vente = v.id_vente) AS dernier_paiement
FROM
    ventes v
INNER JOIN
    bien b ON v.id_bien = b.id_bien
WHERE v.id_vente = ?";

$stmt_vente = mysqli_prepare($conn, $req_vente);
mysqli_stmt_bind_param($stmt_vente, "i", $id_vente);
mysqli_stmt_execute($stmt_vente);
$result_vente = mysqli_stmt_get_result($stmt_vente);
$vente = mysqli_fetch_assoc($result_vente);
mysqli_stmt_close($stmt_vente);

// Debug: vérifier si la vente existe
error_log("Vente trouvée: " . ($vente ? 'OUI' : 'NON'));

if (!$vente) {
    echo "<script>alert('Vente non trouvée!'); window.location.href='paiement_achat.php';</script>";
    exit();
}

// Récupérer les paiements de cette vente
$req_paiements = "SELECT * FROM payement WHERE id_vente = ? ORDER BY date_operation DESC";
$stmt_paiements = mysqli_prepare($conn, $req_paiements);
mysqli_stmt_bind_param($stmt_paiements, "i", $id_vente);
mysqli_stmt_execute($stmt_paiements);
$paiements = mysqli_stmt_get_result($stmt_paiements);
mysqli_stmt_close($stmt_paiements);

// Calculer le pourcentage payé
$pourcentage_paye = $vente['prix_vente'] > 0 ? ($vente['total_paye'] / $vente['prix_vente']) * 100 : 0;
$reste_a_payer = $vente['prix_vente'] - $vente['total_paye'];

// Debug: informations financières
error_log("Prix vente: " . $vente['prix_vente'] . ", Total payé: " . $vente['total_paye'] . ", Reste: " . $reste_a_payer);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Vente #<?php echo $id_vente; ?></title>
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
            overflow-x: hidden;
        }
        
        .page-header {
            background: linear-gradient(120deg, #4361ee, #3a0ca3);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .page-title {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eef2f7;
            font-weight: 600;
            padding: 15px 20px;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }
        
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: auto;
        }
        
        .table-bordered {
            border: 1px solid #dee2e6 !important;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #eaeff4 !important;
            padding: 12px 15px;
        }

        .table-bordered thead th {
            border-bottom-width: 2px !important;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 20px;
            white-space: nowrap;
        }
        
        .table tbody tr {
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            font-weight: 500;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.4);
        }
        
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .btn-edit {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        .btn-edit:hover {
            background-color: #ffc107;
            color: white;
        }
        
        .btn-delete {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .btn-delete:hover {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-print {
            background-color: rgba(32, 201, 151, 0.1);
            color: #20c997;
            border: 1px solid #20c997;
        }
        
        .btn-print:hover {
            background-color: #20c997;
            color: white;
        }
        
        .btn-invoice {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 1px solid #6c757d;
        }
        
        .btn-invoice:hover {
            background-color: #6c757d;
            color: white;
        }
        
        .modal-header {
            background: linear-gradient(120deg, #4361ee, #3a0ca3);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .search-container {
            max-width: 100%;
            flex-grow: 1;
        }
        
        .add-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .actions-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .card-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
            width: 100%;
        }
        
        .no-location {
            color: #dc3545;
            font-style: italic;
        }
        
        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .stats-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        /* Styles pour la pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
            padding: 15px 0;
        }

        .pagination-info {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .pagination {
            margin: 0;
        }

        .page-link {
            color: var(--primary-color);
            border: 1px solid #dee2e6;
            padding: 8px 12px;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .page-link:hover {
            color: var(--secondary-color);
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        
        /* Styles spécifiques pour les détails de vente */
        .info-card {
            border-left: 4px solid #4361ee;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-2px);
        }
        
        .progress {
            height: 12px;
            border-radius: 10px;
        }
        
        .progress-bar-animated {
            animation: progress-animation 2s ease-in-out;
        }
        
        @keyframes progress-animation {
            0% { width: 0%; }
        }
        
        .stat-badge {
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 20px;
        }
        
        .payment-complete {
            color: #28a745;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .financial-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #28a745;
        }
        
        .amount-display {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .history-item {
            border-left: 3px solid #4361ee;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .history-item:hover {
            background-color: #f8f9fa;
            padding-left: 20px;
        }
        
        .dynamic-percentage {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4361ee;
        }
        
        .vente-id-badge {
            background: linear-gradient(45deg, #4361ee, #3a0ca3);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: bold;
        }
        
        .alert-debug {
            display: none; /* Cacher en production */
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .btn-completer {
            background: linear-gradient(45deg, #f8961e, #e85d04);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(248, 150, 30, 0.3);
        }

        .btn-completer:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(248, 150, 30, 0.4);
            background: linear-gradient(45deg, #e85d04, #d00000);
            color: white;
        }

        .table-cell {
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        
        @media (min-width: 768px) {
            .card-header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .card-toolbar {
                margin-top: 0;
                justify-content: flex-end;
                width: auto;
            }
            
            .stats-container {
                display: flex;
                gap: 15px;
            }
            
            .stats-card {
                flex: 1;
            }
        }
        
        @media (max-width: 767px) {
            .card-header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-toolbar {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-container {
                max-width: none;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .stats-container {
                flex-direction: column;
            }
            
            .pagination-container {
                flex-direction: column;
                text-align: center;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Debug info (à cacher en production) -->
    <div class="alert-debug">
        <strong>Debug Info:</strong> 
        ID Vente: <?php echo $id_vente; ?> | 
        Vente trouvée: <?php echo $vente ? 'OUI' : 'NON'; ?> | 
        Nombre de paiements: <?php echo $vente['nb_paiements']; ?>
    </div>

    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Détails de la Vente 
                        <span class="vente-id-badge ms-2">#<?php echo $id_vente; ?></span>
                    </h1>
                    <p class="mb-0">Suivi des paiements et état financier</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="paiement_achat.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i> Retour à la liste
                    </a>
                    <button class="btn btn-outline-light" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-2"></i> Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Informations générales -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-card">
                    <h5><i class="fas fa-home me-2"></i>Informations du Bien</h5>
                    <hr>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($vente['type_bien']); ?></p>
                    <p><strong>Adresse:</strong> <?php echo htmlspecialchars($vente['adresse']); ?></p>
                    <p><strong>Surface:</strong> <?php echo htmlspecialchars($vente['surface']); ?> m²</p>
                    <p><strong>Nombre de chambres:</strong> <?php echo htmlspecialchars($vente['nb_chambre']); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <h5><i class="fas fa-user me-2"></i>Informations de l'Acheteur</h5>
                    <hr>
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($vente['nom_acheteur']) . ' ' . htmlspecialchars($vente['prenom_acheteur']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($vente['contact_acheteur']); ?></p>
                    <p><strong>Adresse:</strong> <?php echo htmlspecialchars($vente['adresse_acheteur']); ?></p>
                    <p><strong>Date de vente:</strong> <?php echo date('d/m/Y', strtotime($vente['date_vente'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Informations financières dynamiques -->
        <div class="info-card financial-card">
            <h5><i class="fas fa-chart-line me-2"></i>Informations Financières</h5>
            <hr>
            <div class="row">
                <div class="col-md-4 text-center">
                    <p><strong>Prix de vente total:</strong></p>
                    <div class="amount-display text-primary">
                        <?php echo number_format($vente['prix_vente'], 2, ',', ' '); ?> CFA
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <p><strong>Total payé:</strong></p>
                    <div class="amount-display text-success">
                        <?php echo number_format($vente['total_paye'], 2, ',', ' '); ?> CFA
                    </div>
                    <div class="progress mt-3">
                        <div class="progress-bar progress-bar-animated <?php echo $pourcentage_paye >= 100 ? 'bg-success' : ($pourcentage_paye >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                             style="width: <?php echo min($pourcentage_paye, 100); ?>%"
                             role="progressbar" 
                             aria-valuenow="<?php echo $pourcentage_paye; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div class="dynamic-percentage mt-2">
                        <?php echo number_format($pourcentage_paye, 1); ?>%
                    </div>
                    <small class="text-muted">Pourcentage du total payé</small>
                </div>
                <div class="col-md-4 text-center">
                    <p><strong>Reste à payer:</strong></p>
                    <div class="amount-display <?php echo $reste_a_payer > 0 ? 'text-danger' : 'payment-complete'; ?>">
                        <?php echo number_format($reste_a_payer, 2, ',', ' '); ?> CFA
                    </div>
                    <?php if ($reste_a_payer <= 0): ?>
                        <div class="mt-2">
                            <span class="badge bg-success stat-badge">
                                <i class="fas fa-check-circle me-1"></i> Paiement terminé
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="mt-2">
                            <span class="badge bg-warning stat-badge">
                                <i class="fas fa-clock me-1"></i> En cours de paiement
                            </span>
                        </div>
                        <div class="mt-2">
                            <a href="completer_Paiement.php?id_vente=<?php echo $vente['id_vente']; ?>" class="btn btn-completer">
                                <i class="fas fa-money-bill-wave me-1"></i> Compléter le paiement
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historique des paiements -->
        <div class="info-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Historique des Paiements - Vente #<?php echo $id_vente; ?>
                </h5>
                <div>
                    <span class="badge bg-primary me-2"><?php echo $vente['nb_paiements']; ?> paiement(s)</span>
                    <?php if ($vente['dernier_paiement']): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-calendar me-1"></i>
                            Dernier: <?php echo date('d/m/Y', strtotime($vente['dernier_paiement'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            
            <?php if (mysqli_num_rows($paiements) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID Paiement</th>
                                <th>Montant</th>
                                <th>Pourcentage</th>
                                <th>Mode de paiement</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cumulative_percentage = 0;
                            while ($paiement = mysqli_fetch_assoc($paiements)): 
                                $payment_percentage = $vente['prix_vente'] > 0 ? ($paiement['montant'] / $vente['prix_vente']) * 100 : 0;
                                $cumulative_percentage += $payment_percentage;
                            ?>
                                <tr>
                                    <td class="table-cell">
                                        <strong>Pay-<?php echo sprintf('%04d', $paiement['id_payement']); ?></strong>
                                    </td>
                                    <td class="table-cell">
                                        <span class="text-success">
                                            <?php echo number_format($paiement['montant'], 2, ',', ' '); ?> CFA
                                        </span>
                                    </td>
                                    <td class="table-cell">
                                        <span class="badge bg-info">
                                            <?php echo number_format($payment_percentage, 1); ?>%
                                        </span>
                                    </td>
                                    <td class="table-cell"><?php echo htmlspecialchars($paiement['mode_payement']); ?></td>
                                    <td class="table-cell"><?php echo date('d/m/Y', strtotime($paiement['date_operation'])); ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="modifier_paiement_achat.php?id=<?php echo $paiement['id_payement']; ?>&id_vente=<?php echo $id_vente; ?>" 
                                               class="btn btn-sm action-btn btn-edit" 
                                               title="Modifier ce paiement">
                                                <i class="fas fa-edit me-1"></i> Modifier
                                            </a>
                                            <a href="supprimer_paiement_achat.php?id=<?php echo $paiement['id_payement']; ?>&id_vente=<?php echo $id_vente; ?>" 
                                               class="btn btn-sm action-btn btn-delete" 
                                               title="Supprimer ce paiement" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?');">
                                                <i class="fas fa-trash me-1"></i> Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun paiement enregistré pour cette vente.</p>
                    <a href="completer_Paiement.php?id_vente=<?php echo $vente['id_vente']; ?>" class="btn btn-completer">
                        <i class="fas fa-plus me-2"></i>Ajouter le premier paiement
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="info-card text-center">
            <h5>Actions Disponibles</h5>
            <hr>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="paiement_achat.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
                <button class="btn btn-primary" onclick="genererFactureVente(<?php echo $vente['id_vente']; ?>)">
                    <i class="fas fa-print me-2"></i>Imprimer la facture
                </button>
                <?php if ($reste_a_payer > 0): ?>
                    <a href="completer_Paiement.php?id_vente=<?php echo $vente['id_vente']; ?>" class="btn btn-completer">
                        <i class="fas fa-money-bill-wave me-2"></i>Compléter les paiements
                    </a>
                <?php endif; ?>
                <a href="completer_Paiement.php" class="btn btn-info">
                    <i class="fas fa-list me-2"></i>Voir tous les paiements incomplets
                </a>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function genererFactureVente(idVente) {
            window.open('generer_facture_vente.php?id_vente=' + idVente, '_blank');
        }
        
        // Animation pour la barre de progression
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                // Déclencher l'animation après un court délai
                setTimeout(() => {
                    progressBar.classList.add('progress-bar-animated');
                }, 300);
            }
            
            // Vérifier si l'ID vente est valide
            const urlParams = new URLSearchParams(window.location.search);
            const idVente = urlParams.get('id_vente');
            if (!idVente || idVente <= 0) {
                alert('ID de vente invalide!');
                window.location.href = 'paiement_achat.php';
            }
        });
        
        // Fonction pour rafraîchir les données
        function refreshData() {
            window.location.reload();
        }
    </script>
</body>
</html>