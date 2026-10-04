<?php
include('entete.php');

$id_vente = isset($_GET['id_vente']) ? intval($_GET['id_vente']) : 0;

if ($id_vente <= 0) {
    die("ID de vente invalide");
}

// Récupérer les informations détaillées de la vente avec les infos du propriétaire
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
    p.nom_pro,
    p.prenom_pro,
    p.contact_pro,
    p.adresse_pro,
    (SELECT COALESCE(SUM(montant), 0) FROM payement WHERE id_vente = v.id_vente) AS total_paye,
    (v.prix_vente - (SELECT COALESCE(SUM(montant), 0) FROM payement WHERE id_vente = v.id_vente)) AS reste_a_payer
FROM
    ventes v
INNER JOIN
    bien b ON v.id_bien = b.id_bien
INNER JOIN
    propritaire p ON b.id_pro = p.id_pro
WHERE v.id_vente = ?";

$stmt_vente = mysqli_prepare($conn, $req_vente);
mysqli_stmt_bind_param($stmt_vente, "i", $id_vente);
mysqli_stmt_execute($stmt_vente);
$result_vente = mysqli_stmt_get_result($stmt_vente);
$vente = mysqli_fetch_assoc($result_vente);
mysqli_stmt_close($stmt_vente);

if (!$vente) {
    die("Vente non trouvée");
}

// Récupérer les paiements de cette vente
$req_paiements = "SELECT * FROM payement WHERE id_vente = ? ORDER BY date_operation ASC";
$stmt_paiements = mysqli_prepare($conn, $req_paiements);
mysqli_stmt_bind_param($stmt_paiements, "i", $id_vente);
mysqli_stmt_execute($stmt_paiements);
$paiements = mysqli_stmt_get_result($stmt_paiements);
mysqli_stmt_close($stmt_paiements);

// Calculer le pourcentage payé
$pourcentage_paye = $vente['prix_vente'] > 0 ? ($vente['total_paye'] / $vente['prix_vente']) * 100 : 0;

// Génération du nom de fichier pour le PDF
$nom_fichier = "facture_vente_" . sprintf('%04d', $vente['id_vente']) . "_" . date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture de Vente Immobilière</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        body { 
            background-color: #f5f7fa; 
            padding: 10px; 
            color: #333;
            line-height: 1.4;
            font-size: 12px;
        }
        
        /* Cacher tout le contenu sauf la facture lors de l'impression */
        @media print {
            body * {
                visibility: hidden;
                margin: 0;
                padding: 0;
            }
            .invoice-container, .invoice-container * {
                visibility: visible;
            }
            .invoice-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
                background: white;
                font-size: 11px;
            }
        }
        
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.03;
            font-size: 80px;
            font-weight: 900;
            color: #2c3e50;
            pointer-events: none;
            white-space: nowrap;
            z-index: 0;
        }
        
        .header-logo-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            justify-content: center;
        }
        
        .header-logo {
            max-height: 50px;
            max-width: 50px;
            object-fit: contain;
            align-self: flex-start;
        }
        
        .invoice-header {
            background: #2c3e50;
            color: white;
            padding: 15px;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .invoice-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        .invoice-ref {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.15);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
        }
        .invoice-body {
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 15px;
        }
        .detail-group {
            padding: 12px;
            background: #f9fbfd;
            border-radius: 6px;
            border-left: 3px solid #3498db;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            padding-bottom: 6px;
            border-bottom: 1px solid #3498db;
            margin-bottom: 10px;
            position: relative;
        }
        .section-title:after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 50px;
            height: 1px;
            background: #e74c3c;
        }
        .detail-item {
            display: flex;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .detail-label {
            min-width: 80px;
            font-weight: 600;
            color: #2c3e50;
        }
        .detail-value {
            color: #555;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 12px;
        }
        .invoice-table th {
            background: #2c3e50;
            color: white;
            text-align: left;
            padding: 10px;
            font-weight: 600;
        }
        .invoice-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .total-row {
            background-color: rgba(39, 174, 96, 0.1);
            font-weight: 700;
        }
        .montant-positive {
            color: #28a745;
            font-weight: bold;
        }
        .montant-negative {
            color: #dc3545;
            font-weight: bold;
        }
        .conditions {
            background: #f9fbfd;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #27ae60;
            margin-top: 15px;
            font-size: 12px;
        }
        .condition-item {
            display: flex;
            margin-bottom: 4px;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            font-size: 12px;
            text-decoration: none;
        }
        .btn-print {
            background: #2c3e50;
            color: white;
        }
        .btn-pdf {
            background: #e74c3c;
            color: white;
        }
        .btn-back {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .btn i {
            margin-right: 5px;
            font-size: 12px;
        }
        
        /* Style pour les informations acheteur */
        .acheteur-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .acheteur-title {
            font-size: 14px;
            font-weight: 700;
            color: #856404;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .acheteur-title i {
            margin-right: 8px;
        }
        .acheteur-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
        }
        
        /* Style pour les informations propriétaire */
        .proprietaire-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .proprietaire-title {
            font-size: 14px;
            font-weight: 700;
            color: #0c5460;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .proprietaire-title i {
            margin-right: 8px;
        }
        .proprietaire-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
        }
        
        /* Style pour la progression du paiement */
        .progression-paiement {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            padding: 12px;
            margin: 15px 0;
        }
        .progression-title {
            font-size: 14px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .progression-title i {
            margin-right: 8px;
        }
        
        /* Styles d'impression optimisés */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            
            .invoice-header, .invoice-body {
                padding: 8px;
            }
            
            .watermark {
                font-size: 60px;
            }
            
            .details-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
                margin-bottom: 10px;
            }
            
            .acheteur-details, .proprietaire-details {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .invoice-table {
                font-size: 10px;
                margin: 10px 0;
            }
            
            .invoice-table th,
            .invoice-table td {
                padding: 6px 8px;
            }
            
            .detail-group {
                padding: 8px;
            }
            
            .acheteur-info,
            .proprietaire-info,
            .progression-paiement,
            .conditions {
                padding: 8px;
                margin-bottom: 10px;
            }
            
            /* Optimiser l'espace */
            .invoice-title {
                font-size: 18px;
                margin-bottom: 2px;
            }
            
            .invoice-subtitle {
                font-size: 12px;
            }
            
            .section-title {
                font-size: 12px;
                margin-bottom: 8px;
            }
            
            .detail-item {
                margin-bottom: 4px;
                font-size: 11px;
            }
            
            .condition-item {
                font-size: 11px;
                margin-bottom: 3px;
            }
        }
        
        /* Style pour le modal de confirmation */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 15px;
            border-radius: 8px;
            width: 350px;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        }
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-confirm {
            background: #27ae60;
            color: white;
        }
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
    <!-- Boutons en dehors du conteneur de la facture -->
    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button onclick="generatePDF()" class="btn btn-pdf">
            <i class="fas fa-file-pdf"></i> Télécharger PDF
        </button>
        <a href="javascript:history.back()" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="invoice-container" id="invoice-content">
        <div class="watermark">SOLID IMMO</div>
        
        <div class="invoice-header">
            <div class="invoice-ref">N° : VENTE-<?php echo sprintf('%04d', $vente['id_vente']); ?></div>
            
            <div class="header-logo-title">
                <img src="logo_solid.png" alt="Logo SOLID IMMO" class="header-logo" onerror="this.style.display='none'">
                <div>
                    <h1 class="invoice-title">FACTURE DE VENTE IMMOBILIÈRE</h1>
                    <h4>SOLID IMMO</h4>
                </div>
            </div>
            
            <p>N° RCCM : TD-NDJ-01-2021-All-00230, NIF : 9032706.</p>
            <p class="invoice-subtitle">Date: <?php echo date('d/m/Y', strtotime($vente['date_vente'])); ?></p>
        </div>
        
        <div class="invoice-body">
            <!-- Informations de l'acheteur -->
            <div class="acheteur-info">
                <div class="acheteur-title">
                    <i class="fas fa-user-tie"></i>
                    Informations de l'Acheteur
                </div>
                <div class="acheteur-details">
                    <div class="detail-item">
                        <span class="detail-label">Nom complet:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['prenom_acheteur'] . ' ' . $vente['nom_acheteur']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['contact_acheteur']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['adresse_acheteur']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Informations du propriétaire -->
            <div class="proprietaire-info">
                <div class="proprietaire-title">
                    <i class="fas fa-user"></i>
                    Informations du Propriétaire
                </div>
                <div class="proprietaire-details">
                    <div class="detail-item">
                        <span class="detail-label">Nom complet:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['prenom_pro'] . ' ' . $vente['nom_pro']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['contact_pro']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['adresse_pro']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="details-grid">
                <div class="detail-group">
                    <h3 class="section-title">Informations de la vente</h3>
                    <div class="detail-item">
                        <span class="detail-label">Référence:</span>
                        <span class="detail-value">VENTE-<?php echo sprintf('%04d', $vente['id_vente']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date vente:</span>
                        <span class="detail-value"><?php echo date('d/m/Y', strtotime($vente['date_vente'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Prix de vente:</span>
                        <span class="detail-value"><?php echo number_format($vente['prix_vente'], 0, ',', ' '); ?> CFA</span>
                    </div>
                </div>
                
                <div class="detail-group">
                    <h3 class="section-title">Détails du bien</h3>
                    <div class="detail-item">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['type_bien']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['adresse']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Surface:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['surface']); ?> m²</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Chambres:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($vente['nb_chambre']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Progression du paiement -->
            <div class="progression-paiement">
                <div class="progression-title">
                    <i class="fas fa-chart-line"></i>
                    Progression du Paiement
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total payé:</span>
                    <span class="detail-value montant-positive"><?php echo number_format($vente['total_paye'], 0, ',', ' '); ?> CFA</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Reste à payer:</span>
                    <span class="detail-value montant-negative"><?php echo number_format($vente['reste_a_payer'], 0, ',', ' '); ?> CFA</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Progression:</span>
                    <span class="detail-value"><?php echo number_format($pourcentage_paye, 1); ?>%</span>
                </div>
            </div>
            
            <!-- Historique des paiements -->
            <?php if (mysqli_num_rows($paiements) > 0): ?>
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mode paiement</th>
                            <th>Montant</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_pourcentage = 0;
                        while ($paiement = mysqli_fetch_assoc($paiements)): 
                            $payment_percentage = $vente['prix_vente'] > 0 ? ($paiement['montant'] / $vente['prix_vente']) * 100 : 0;
                            $total_pourcentage += $payment_percentage;
                        ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($paiement['date_operation'])); ?></td>
                                <td><?php echo htmlspecialchars($paiement['mode_payement']); ?></td>
                                <td class="montant-positive"><?php echo number_format($paiement['montant'], 0, ',', ' '); ?> CFA</td>
                                <td><?php echo number_format($payment_percentage, 1); ?>%</td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL</strong></td>
                            <td><strong><?php echo number_format($vente['total_paye'], 0, ',', ' '); ?> CFA</strong></td>
                            <td><strong><?php echo number_format($total_pourcentage, 1); ?>%</strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="conditions">
                <div class="condition-item">
                    <i class="fas fa-check-circle" style="color: #27ae60; margin-right: 8px;"></i>
                    <span>Date d'émission : <?php echo date('d/m/Y à H:i'); ?></span>
                </div>
                <div class="condition-item">
                    <i class="fas fa-check-circle" style="color: #27ae60; margin-right: 8px;"></i>
                    <span>Ce document fait foi de contrat de vente entre les parties</span>
                </div>
                <div class="condition-item">
                    <i class="fas fa-check-circle" style="color: #27ae60; margin-right: 8px;"></i>
                    <span>Signature électronique validée</span>
                </div>
            </div>
        </div>
    </div>
    
    <div id="pdfModal" class="modal no-print">
        <div class="modal-content">
            <h3><i class="fas fa-file-pdf" style="color:#e74c3c; margin-right:10px;"></i> Génération du PDF</h3>
            <p>Le PDF de la facture est en cours de génération...</p>
            <div class="modal-buttons">
                <button id="cancelPdf" class="btn btn-cancel">Annuler</button>
            </div>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('print') === '1') {
                window.print();
            }
        };

        function generatePDF() {
            // Afficher le modal
            const modal = document.getElementById('pdfModal');
            modal.style.display = 'block';
            
            // Options pour le PDF
            const options = {
                margin: [5, 5, 5, 5],
                filename: '<?php echo $nom_fichier; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2, 
                    useCORS: true,
                    width: 800
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait',
                    compress: true
                }
            };
            
            // Générer le PDF
            html2pdf().set(options).from(document.getElementById('invoice-content')).save().then(() => {
                // Fermer le modal une fois le PDF généré
                modal.style.display = 'none';
            });
            
            // Annuler la génération
            document.getElementById('cancelPdf').onclick = function() {
                modal.style.display = 'none';
            };
        }

        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('pdfModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>