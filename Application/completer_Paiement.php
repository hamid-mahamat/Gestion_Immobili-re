<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "gestion_immo");

// Traitement de l'ajout de paiement
if (isset($_POST['ajouter_paiement'])) {
    $id_vente = mysqli_real_escape_string($conn, $_POST['id_vente']);
    $montant = mysqli_real_escape_string($conn, $_POST['montant']);
    $mode_payement = mysqli_real_escape_string($conn, $_POST['mode_payement']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);

    // Insertion du paiement
    $req = "INSERT INTO payement (id_vente, montant, date_operation, mode_payement) 
            VALUES('$id_vente', '$montant', '$date_operation', '$mode_payement')";
    
    $execution = mysqli_query($conn, $req);
    
    if($execution) {
        echo '<script>alert("Paiement ajouté avec succès");</script>';
        // Redirection pour éviter la resoumission du formulaire
        header("Location: completer_Paiement.php?id_vente=" . $id_vente);
        exit();
    } else {
        echo '<script>alert("Erreur lors de l\'ajout du paiement");</script>';
    }
}

include('entete.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compléter les Paiements</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --warning-color: #f8961e;
            --success-color: #06d6a0;
            --light-bg: #f8f9fc;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
            --border-radius: 8px;
            --dark-color: #1e293b;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            color: #333;
        }
        
        .page-header {
            background: linear-gradient(120deg, #f8961e, #e85d04);
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
        
        .table thead th {
            background-color: var(--warning-color);
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
            background-color: rgba(248, 150, 30, 0.05);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            padding: 8px 20px;
            font-weight: 500;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 6px rgba(248, 150, 30, 0.3);
        }
        
        .btn-warning:hover {
            background-color: #e85d04;
            border-color: #e85d04;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(248, 150, 30, 0.4);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            padding: 8px 20px;
            font-weight: 500;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 6px rgba(6, 214, 160, 0.3);
        }
        
        .btn-success:hover {
            background-color: #05b38a;
            border-color: #05b38a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(6, 214, 160, 0.4);
        }
        
        .progress {
            height: 10px;
            margin: 10px 0;
        }
        
        .progress-bar {
            background-color: var(--warning-color);
        }
        
        .montant-restant {
            color: #e85d04;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .montant-paye {
            color: var(--success-color);
            font-weight: bold;
        }
        
        .info-card {
            border-left: 4px solid var(--warning-color);
            background-color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
        }
        
        .info-card h5 {
            color: var(--warning-color);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .section-title {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(248, 150, 30, 0.1);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .card-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .card-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
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
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Compléter les Paiements</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="vente.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i> Retour aux ventes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Liste des ventes avec paiements incomplets -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h5 class="mb-0">Ventes avec paiements incomplets</h5>
                    <div class="card-toolbar">
                        <div class="input-group search-container">
                            <input type="text" class="form-control" placeholder="Rechercher une vente...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                // Récupérer les ventes avec paiements incomplets
                $req_ventes = "SELECT v.*, b.type, b.adresse, 
                              COALESCE(SUM(p.montant), 0) as total_paye,
                              (v.prix_vente - COALESCE(SUM(p.montant), 0)) as montant_restant
                       FROM ventes v
                       JOIN bien b ON v.id_bien = b.id_bien
                       LEFT JOIN payement p ON v.id_vente = p.id_vente
                       GROUP BY v.id_vente
                       HAVING montant_restant > 0
                       ORDER BY v.date_operation DESC";
                
                $res_ventes = mysqli_query($conn, $req_ventes);
                
                if (mysqli_num_rows($res_ventes) > 0) : ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID Vente</th>
                                    <th>Bien</th>
                                    <th>Acheteur</th>
                                    <th>Prix Total</th>
                                    <th>Déjà Payé</th>
                                    <th>Reste à Payer</th>
                                    <th>Progression</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($vente = mysqli_fetch_assoc($res_ventes)) : 
                                    $pourcentage = ($vente['total_paye'] / $vente['prix_vente']) * 100;
                                ?>
                                    <tr>
                                        <td><?= $vente['id_vente'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($vente['type']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($vente['adresse']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($vente['prenom_acheteur']) ?> <?= htmlspecialchars($vente['nom_acheteur']) ?></td>
                                        <td><?= number_format($vente['prix_vente'], 0, ',', ' ') ?> CFA</td>
                                        <td class="montant-paye"><?= number_format($vente['total_paye'], 0, ',', ' ') ?> CFA</td>
                                        <td class="montant-restant"><?= number_format($vente['montant_restant'], 0, ',', ' ') ?> CFA</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $pourcentage ?>%"
                                                     aria-valuenow="<?= $pourcentage ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small><?= number_format($pourcentage, 1) ?>%</small>
                                        </td>
                                        <td>
                                            <a href="completer_Paiement.php?id_vente=<?= $vente['id_vente'] ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-money-bill-wave me-1"></i> Compléter
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="alert alert-success text-center py-4">
                        <i class="fas fa-check-circle fa-2x mb-3"></i>
                        <h5>Toutes les ventes sont complètement payées</h5>
                        <p class="mb-0">Aucun paiement en attente</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulaire de complément de paiement -->
        <?php if (isset($_GET['id_vente'])) : 
            $id_vente = intval($_GET['id_vente']);
            
            // Récupérer les informations de la vente
            $req_vente = "SELECT v.*, b.type, b.adresse, 
                         COALESCE(SUM(p.montant), 0) as total_paye,
                         (v.prix_vente - COALESCE(SUM(p.montant), 0)) as montant_restant
                  FROM ventes v
                  JOIN bien b ON v.id_bien = b.id_bien
                  LEFT JOIN payement p ON v.id_vente = p.id_vente
                  WHERE v.id_vente = $id_vente
                  GROUP BY v.id_vente";
            
            $res_vente = mysqli_query($conn, $req_vente);
            
            if (mysqli_num_rows($res_vente) > 0) {
                $vente = mysqli_fetch_assoc($res_vente);
                $pourcentage = ($vente['total_paye'] / $vente['prix_vente']) * 100;
            ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Compléter le paiement - Vente #<?= $vente['id_vente'] ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Résumé de la vente -->
                        <div class="info-card mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6>Bien vendu</h6>
                                    <p class="mb-1"><strong><?= htmlspecialchars($vente['type']) ?></strong></p>
                                    <p class="text-muted"><?= htmlspecialchars($vente['adresse']) ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Acheteur</h6>
                                    <p class="mb-1"><?= htmlspecialchars($vente['prenom_acheteur']) ?> <?= htmlspecialchars($vente['nom_acheteur']) ?></p>
                                    <p class="text-muted"><?= htmlspecialchars($vente['contact_acheteur']) ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6>État du paiement</h6>
                                    <p class="mb-1">Total: <strong><?= number_format($vente['prix_vente'], 0, ',', ' ') ?> CFA</strong></p>
                                    <p class="mb-1">Payé: <span class="montant-paye"><?= number_format($vente['total_paye'], 0, ',', ' ') ?> CFA</span></p>
                                    <p class="mb-0">Reste: <span class="montant-restant"><?= number_format($vente['montant_restant'], 0, ',', ' ') ?> CFA</span></p>
                                </div>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?= $pourcentage ?>%"
                                     aria-valuenow="<?= $pourcentage ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= number_format($pourcentage, 1) ?>%
                                </div>
                            </div>
                        </div>

                        <!-- Historique des paiements -->
                        <div class="mb-4">
                            <h6 class="section-title">Historique des paiements</h6>
                            <?php
                            $req_paiements = "SELECT * FROM payement 
                                            WHERE id_vente = $id_vente 
                                            ORDER BY date_operation DESC";
                            $res_paiements = mysqli_query($conn, $req_paiements);
                            
                            if (mysqli_num_rows($res_paiements) > 0) : ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Montant</th>
                                                <th>Mode de paiement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($paiement = mysqli_fetch_assoc($res_paiements)) : ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($paiement['date_operation'])) ?></td>
                                                    <td class="montant-paye"><?= number_format($paiement['montant'], 0, ',', ' ') ?> CFA</td>
                                                    <td><span class="badge bg-warning"><?= htmlspecialchars($paiement['mode_payement']) ?></span></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Aucun paiement enregistré pour cette vente.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Formulaire d'ajout de paiement -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter un paiement</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="completer_Paiement.php">
                                    <input type="hidden" name="id_vente" value="<?= $vente['id_vente'] ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Montant du paiement (CFA) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">CFA</span>
                                                <input type="number" name="montant" class="form-control" 
                                                       max="<?= $vente['montant_restant'] ?>" 
                                                       placeholder="Max: <?= number_format($vente['montant_restant'], 0, ',', ' ') ?>" required>
                                            </div>
                                            <small class="form-text text-muted">Montant maximum: <?= number_format($vente['montant_restant'], 0, ',', ' ') ?> CFA</small>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                            <select name="mode_payement" class="form-select" required>
                                                <option value="">Choisir un mode</option>
                                                <option value="Espèces">Espèces</option>
                                                <option value="Carte Bancaire">Carte Bancaire</option>
                                                <option value="Virement">Virement</option>
                                                <option value="Chèque">Chèque</option>
                                                <option value="Mobile Money">Mobile Money</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Date du paiement <span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="date_operation" class="form-control" 
                                                   value="<?= date('Y-m-d\TH:i') ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="submit" name="ajouter_paiement" class="btn btn-warning">
                                            <i class="fas fa-plus me-2"></i>Ajouter le paiement
                                        </button>
                                        
                                        <?php if ($vente['montant_restant'] == 0) : ?>
                                            <a href="generer_facture_vente.php?id_vente=<?= $vente['id_vente'] ?>" class="btn btn-success">
                                                <i class="fas fa-file-invoice me-2"></i>Générer la Facture
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger text-center py-4">
                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                    <h5>Vente non trouvée</h5>
                    <p class="mb-0">La vente demandée n'existe pas ou a été supprimée</p>
                    <a href="completer_Paiement.php" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-redo me-2"></i>Retour à la liste
                    </a>
                </div>
            <?php } ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function(){
            // Formatage des montants
            $('input[name="montant"]').on('blur', function() {
                const value = $(this).val();
                if (value) {
                    $(this).val(parseFloat(value).toLocaleString('fr-FR'));
                }
            });
            
            // Validation du montant maximum
            $('form').on('submit', function() {
                const montantInput = $('input[name="montant"]');
                const montantMax = parseFloat(montantInput.attr('max'));
                const montantSaisi = parseFloat(montantInput.val().replace(/\s/g, ''));
                
                if (montantSaisi > montantMax) {
                    alert('Le montant saisi dépasse le montant restant à payer!');
                    return false;
                }
            });

            // Recherche en temps réel dans le tableau
            $('.search-container input').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>