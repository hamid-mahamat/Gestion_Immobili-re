<?php
include('entete.php');

// Vérification de l'ID du paiement
if (!isset($_GET['id']) || empty($_GET['id'])) {
    exit('Erreur : ID du paiement non spécifié.');
}

$id_payement = intval($_GET['id']);

// --- REQUÊTE SQL CORRIGÉE ---
// 'l.contact_loca' est devenu 'l.telephone_loca'
// 'l.adresse_loca' a été supprimé car il n'existe pas dans la table locataire
$sql = "SELECT 
            p.*, 
            l.nom_loca, 
            l.prenom_loca,
            l.telephone_loca, -- Colonne corrigée
            loc.loyer_mensuel,
            loc.date_debut,
            b.type AS type_bien,
            b.adresse AS localisation,
            b.surface,
            b.nb_chambre,
            (SELECT COALESCE(SUM(montant), 0) FROM payement WHERE id_location = loc.id_location) AS total_paye
        FROM payement p
        INNER JOIN location loc ON p.id_location = loc.id_location
        INNER JOIN locataire l ON loc.id_loca = l.id_loca
        INNER JOIN bien b ON loc.id_bien = b.id_bien
        WHERE p.id_payement = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_payement);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    exit('Erreur : Paiement non trouvé dans la base de données.');
}

$paiement = mysqli_fetch_assoc($result);

// Calculs pour la location
$reste_a_payer = $paiement['loyer_mensuel'] - $paiement['total_paye'];

// Génération du nom de fichier pour le PDF
$nom_fichier = "facture_location_" . sprintf('%04d', $paiement['id_payement']) . "_" . date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture de paiement - Location</title>
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
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
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
        
        /* Style pour les informations locataire */
        .locataire-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .locataire-title {
            font-size: 14px;
            font-weight: 700;
            color: #856404;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .locataire-title i {
            margin-right: 8px;
        }
        .locataire-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
        }
        
        @media print {
            body {
                padding: 0;
                background: white;
                font-size: 11px;
            }
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                margin: 0;
                width: 100%;
            }
            .invoice-header, .invoice-body {
                padding: 10px;
            }
            .action-buttons {
                display: none;
            }
            .watermark {
                font-size: 70px;
            }
            .details-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            .locataire-details {
                grid-template-columns: repeat(2, 1fr);
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
    <div class="invoice-container" id="invoice-content">
        <div class="watermark">SOLID IMMO</div>
        
        <div class="invoice-header">
            <div class="invoice-ref">N° : FAC-<?php echo sprintf('%04d', $paiement['id_payement']); ?></div>
            
            <div class="header-logo-title">
                <img src="logo_solid.png" alt="Logo SOLID IMMO" class="header-logo" onerror="this.style.display='none'">
                <div>
                    <h1 class="invoice-title">FACTURE DE PAIEMENT - LOCATION</h1>
                    <h4>SOLID IMMO</h4>
                </div>
            </div>
            
            <p>N° RCCM : TD-NDJ-01-2021-All-00230, NIF : 9032706.</p>
            <p class="invoice-subtitle">Date: <?php echo date('d/m/Y', strtotime($paiement['date_operation'])); ?></p>
        </div>
        
        <div class="invoice-body">
            <div class="locataire-info">
                <div class="locataire-title">
                    <i class="fas fa-user"></i>
                    Informations du Locataire
                </div>
                <div class="locataire-details">
                    <div class="detail-item">
                        <span class="detail-label">Nom complet:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['nom_loca'] . ' ' . $paiement['prenom_loca']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['telephone_loca'] ?? 'Non spécifié'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['adresse_loca'] ?? 'Non spécifiée'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="details-grid">
                <div class="detail-group">
                    <h3 class="section-title">Informations de la facture</h3>
                    <div class="detail-item">
                        <span class="detail-label">Référence:</span>
                        <span class="detail-value">PAY-<?php echo sprintf('%04d', $paiement['id_payement']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date opération:</span>
                        <span class="detail-value"><?php echo date('d/m/Y', strtotime($paiement['date_operation'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Mode paiement:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['mode_payement']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date début location:</span>
                        <span class="detail-value"><?php echo date('d/m/Y', strtotime($paiement['date_debut'])); ?></span>
                    </div>
                </div>
                
                <div class="detail-group">
                    <h3 class="section-title">Détails du bien</h3>
                    <div class="detail-item">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['type_bien']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Localisation:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['localisation']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Surface:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['surface'] ?? 'Non spécifiée'); ?> m²</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Chambres:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['nb_chambre'] ?? 'Non spécifié'); ?></span>
                    </div>
                </div>
            </div>
            
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Montant (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Loyer mensuel</td>
                        <td><?php echo number_format($paiement['loyer_mensuel'], 0, ',', ' '); ?></td>
                    </tr>
                    <tr>
                        <td>Montant de ce paiement</td>
                        <td><?php echo number_format($paiement['montant'], 0, ',', ' '); ?></td>
                    </tr>
                    <tr>
                        <td>Total payé à ce jour</td>
                        <td><?php echo number_format($paiement['total_paye'], 0, ',', ' '); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Reste à payer</strong></td>
                        <td class="<?php echo $reste_a_payer > 0 ? 'montant-negative' : 'montant-positive'; ?>">
                            <strong><?php echo number_format($reste_a_payer, 0, ',', ' '); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="conditions">
                <div class="condition-item">
                    <i class="fas fa-check-circle" style="color: #27ae60; margin-right: 8px;"></i>
                    <span>Paiement effectué par <?php echo htmlspecialchars($paiement['mode_payement']); ?></span>
                </div>
                <div class="condition-item">
                    <i class="fas fa-check-circle" style="color: #27ae60; margin-right: 8px;"></i>
                    <span>Date d'émission : <?php echo date('d/m/Y à H:i'); ?></span>
                </div>
                <div class="condition-item">
                    <i class="fas fa-check-circle" style="color: #27ae60; margin-right: 8px;"></i>
                    <span>Cet document fait foi de paiement</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <button onclick="window.print()" class="btn btn-print">
                    <i class="fas fa-print"></i> Imprimer
                </button>
                <button onclick="generatePDF()" class="btn btn-pdf">
                    <i class="fas fa-file-pdf"></i> Télécharger PDF
                </button>
                <a href="payement.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Retour aux paiements
                </a>
            </div>
        </div>
    </div>
    
    <div id="pdfModal" class="modal">
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
                    width: 800,
                    height: document.getElementById('invoice-content').scrollHeight
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