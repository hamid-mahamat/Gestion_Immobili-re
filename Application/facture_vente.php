<?php
session_start();
$conn = mysqli_connect("127.0.0.1", "root", "", "gestion_immo");

if (isset($_GET['id_vente'])) {
    $id_vente = intval($_GET['id_vente']);
    
    $req_vente = "SELECT v.*, b.*, 
                         p.nom_pro AS nom_proprietaire, 
                         p.prenom_pro AS prenom_proprietaire, 
                         p.adresse_pro AS adresse_proprietaire,
                         p.contact_pro AS contact_proprietaire
                  FROM ventes v 
                  JOIN bien b ON v.id_bien = b.id_bien 
                  JOIN propritaire p ON b.id_pro = p.id_pro
                  WHERE v.id_vente = $id_vente";
                  
    $res_vente = mysqli_query($conn, $req_vente);
    
    if ($res_vente && mysqli_num_rows($res_vente) > 0) {
        $vente = mysqli_fetch_assoc($res_vente);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture de vente immobilière</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        body { 
            background-color: #f5f7fa; 
            padding: 15px; 
            color: #333;
            line-height: 1.5;
            font-size: 14px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.03;
            font-size: 100px;
            font-weight: 900;
            color: #2c3e50;
            pointer-events: none;
            white-space: nowrap;
            z-index: 0;
        }
        
        /*Conteneur pour le logo et le titre */
        .header-logo-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            justify-content: center;
        }
        
        /*Style du logo */
        .header-logo {
            max-height: 60px; /* Taille réduite */
            max-width: 60px;
            object-fit: contain;
            align-self: flex-start;
        }
        
        .invoice-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .invoice-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }
        .invoice-ref {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.15);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        .invoice-body {
            padding: 25px;
            position: relative;
            z-index: 1;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .detail-group {
            padding: 15px;
            background: #f9fbfd;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
            margin-bottom: 12px;
            position: relative;
        }
        .section-title:after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: #e74c3c;
        }
        .detail-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .detail-label {
            min-width: 100px;
            font-weight: 600;
            color: #2c3e50;
        }
        .detail-value {
            color: #555;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        .invoice-table th {
            background: #2c3e50;
            color: white;
            text-align: left;
            padding: 12px;
            font-weight: 600;
        }
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .total-row {
            background-color: rgba(39, 174, 96, 0.1);
            font-weight: 700;
        }
        .conditions {
            background: #f9fbfd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
            margin-top: 20px;
            font-size: 14px;
        }
        .condition-item {
            display: flex;
            margin-bottom: 6px;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }
        .btn {
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            font-size: 15px;
        }
        .btn-print {
            background: #2c3e50;
            color: white;
        }
        .btn-back {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .btn i {
            margin-right: 8px;
            font-size: 16px;
        }
        @media print {
            body {
                padding: 0;
                background: white;
                font-size: 13px;
            }
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                margin: 0;
            }
            .invoice-header, .invoice-body {
                padding: 15px;
            }
            .action-buttons {
                display: none;
            }
            .watermark {
                font-size: 90px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="invoice-container">
        <div class="watermark">SOLID IMMO</div>
        
        <div class="invoice-header">
            <div class="invoice-ref">N° : FV-<?= htmlspecialchars($vente['id_vente'] ?? '') ?></div>
            
           
            <div class="header-logo-title">
                
                <img src="logo_solid.png" alt="Logo SOLID IMMO" class="header-logo">
                <div>
                    <h1 class="invoice-title">FACTURE DE VENTE IMMOBILIÈRE</h1>
                    <h4>SOLID IMMO</h4>
                </div>
            </div>
            
            <p>N° RCCM : TD-NDJ-01-2021-All-00230, NIF : 9032706.</p>
            <p class="invoice-subtitle">Date: <?= isset($vente['date_operation']) ? date('d/m/Y', strtotime($vente['date_operation'])) : '' ?></p>
        </div>
        
        <div class="invoice-body">
            <div class="details-grid">
                <div class="detail-group">
                    <h3 class="section-title">Détails du bien</h3>
                    <div class="detail-item">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['type'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['adresse'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Surface:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['surface'] ?? '') ?> m²</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Chambres:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['nb_chambre'] ?? '') ?></span>
                    </div>
                </div>
                
                <div class="detail-group">
                    <h3 class="section-title">Propriétaire</h3>
                    <div class="detail-item">
                        <span class="detail-label">Nom:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['nom_proprietaire'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Prénom:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['prenom_proprietaire'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['contact_proprietaire'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['adresse_proprietaire'] ?? '') ?></span>
                    </div>
                </div>
                
                <div class="detail-group">
                    <h3 class="section-title">Acheteur</h3>
                    <div class="detail-item">
                        <span class="detail-label">Nom:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['nom_acheteur'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Prénom:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['prenom_acheteur'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Téléphone:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['contact_acheteur'] ?? '') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?= htmlspecialchars($vente['adresse_acheteur'] ?? '') ?></span>
                    </div>
                </div>
            </div>
            
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Montant (CFA)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Vente du bien immobilier</td>
                        <td><?= isset($vente['prix_vente']) ? number_format($vente['prix_vente'], 0, ',', ' ') : '' ?></td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td><strong><?= isset($vente['prix_vente']) ? number_format($vente['prix_vente'], 0, ',', ' ') : '' ?></strong></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="conditions">
                <thead>
                     <tr>
                <th><h3 class="section-title">Conditions de vente</h3></th>
                <div class="condition-item">
                    <span class="detail-label">Mode de paiement:</span>
                    <span class="detail-value">Virement bancaire</span>
                </div>
                <div class="condition-item">
                    <span class="detail-label">Date d'effet:</span>
                    <span class="detail-value">Signature chez notaire</span>
                </div>
               

            </tr>

            </thead>
            </div>
            
            <div class="action-buttons">
                <button onclick="window.print()" class="btn btn-print">
                    <i class="fas fa-print"></i> Imprimer
                </button>
                <a href="vente.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
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
    </script>
</body>
</html>
<?php
    } else {
        ?>
        <div style="max-width:800px;margin:50px auto;text-align:center;padding:30px;background:#fff;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,0.1);">
            <h2 style="color:#e74c3c;margin-bottom:20px;font-size:24px;">Vente introuvable</h2>
            <p style="margin-bottom:25px;font-size:18px;">La référence demandée n'existe pas dans notre système.</p>
            <a href="vente.php" style="display:inline-block;padding:12px 25px;background:#2c3e50;color:white;border-radius:6px;text-decoration:none;font-size:16px;transition:all 0.3s;">
                Retour aux ventes
            </a>
        </div>
        <?php
    }
} else {
    header("Location: vente.php");
    exit();
}
?>