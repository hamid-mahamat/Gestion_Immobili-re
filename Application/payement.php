<?php
include('entete.php');

if (isset($_POST['enregistrer'])) {
    $id_location = $_POST['id_location'] ?? null;
    $montant = $_POST['montant'] ?? null;
    $date_operation = $_POST['date_operation'] ?? null;
    $mode_payement = $_POST['mode_payement'] ?? null;

    if (empty($id_location) || empty($montant) || empty($date_operation) || empty($mode_payement)) {
        echo '<script type="text/javascript">alert("Veuillez remplir tous les champs !");</script>';
    } else {
        $req_insert = "INSERT INTO payement (id_location, montant, date_operation, mode_payement) VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $req_insert)) {
            mysqli_stmt_bind_param($stmt, "isss", $id_location, $montant, $date_operation, $mode_payement);
            
            if (mysqli_stmt_execute($stmt)) {
                echo '<script type="text/javascript">alert("Paiement enregistré avec succès !!");</script>';
            } else {
                echo '<script type="text/javascript">alert("Erreur lors de l\'enregistrement du paiement : ' . mysqli_error($conn) . '");</script>';
            }
            mysqli_stmt_close($stmt); 
        } else {
            echo '<script type="text/javascript">alert("Erreur de préparation de la requête d\'insertion : ' . mysqli_error($conn) . '");</script>';
        }
    }
}

$search_query = ""; 
if (isset($_GET['search_locataire']) && !empty($_GET['search_locataire'])) {
    $search_query = '%' . $_GET['search_locataire'] . '%';
}

// Configuration de la pagination
$limit = 5; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Requête pour le nombre total d'éléments
$count_query = "SELECT COUNT(*) as total FROM payement p
                LEFT JOIN location loc ON p.id_location = loc.id_location
                LEFT JOIN bien b ON loc.id_bien = b.id_bien
                LEFT JOIN locataire l ON loc.id_loca = l.id_loca";

if (!empty($search_query)) {
    $count_query .= " WHERE l.nom_loca LIKE ? OR l.prenom_loca LIKE ? OR b.type LIKE ?";
}

if ($stmt_count = mysqli_prepare($conn, $count_query)) {
    if (!empty($search_query)) {
        mysqli_stmt_bind_param($stmt_count, "sss", $search_query, $search_query, $search_query);
    }
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $total_row = mysqli_fetch_assoc($result_count);
    $total_records = $total_row['total'];
    mysqli_stmt_close($stmt_count);
} else {
    $total_records = 0;
}

// Calcul du nombre total de pages
$total_pages = ceil($total_records / $limit);

// Requête principale avec pagination
$sql_payments = "SELECT
    p.id_payement,
    p.montant,
    p.date_operation,
    p.mode_payement,
    COALESCE(b.type, 'Non associé') AS type_bien,
    COALESCE(CONCAT(l.nom_loca, ' ', l.prenom_loca), 'Non associé') AS nom_complet
FROM
    payement p
LEFT JOIN
    location loc ON p.id_location = loc.id_location
LEFT JOIN
    bien b ON loc.id_bien = b.id_bien
LEFT JOIN
    locataire l ON loc.id_loca = l.id_loca";

if (!empty($search_query)) {
    $sql_payments .= " WHERE l.nom_loca LIKE ? OR l.prenom_loca LIKE ? OR b.type LIKE ?";
}

$sql_payments .= " ORDER BY p.date_operation DESC LIMIT ? OFFSET ?"; 

if ($stmt_payments = mysqli_prepare($conn, $sql_payments)) {
    if (!empty($search_query)) {
        mysqli_stmt_bind_param($stmt_payments, "sssii", $search_query, $search_query, $search_query, $limit, $offset);
    } else {
        mysqli_stmt_bind_param($stmt_payments, "ii", $limit, $offset);
    }
    mysqli_stmt_execute($stmt_payments);
    $res_tab = mysqli_stmt_get_result($stmt_payments);
} else {
    $res_tab = false;
    echo '<script type="text/javascript">alert("Erreur de préparation de la requête de recherche : ' . mysqli_error($conn) . '");</script>';
}

$req_total = "SELECT COUNT(*) AS total, SUM(montant) AS somme FROM payement";
$res_total = mysqli_query($conn, $req_total);
$row_total = mysqli_fetch_assoc($res_total);

$total_locataire = 0;
if (!empty($search_query)) {
    $req_total_loc = "SELECT SUM(p.montant) AS total_locataire
                      FROM payement p
                      LEFT JOIN location loc ON p.id_location = loc.id_location
                      LEFT JOIN locataire l ON loc.id_loca = l.id_loca
                      WHERE l.nom_loca LIKE ? OR l.prenom_loca LIKE ?";
    
    if ($stmt_total = mysqli_prepare($conn, $req_total_loc)) {
        mysqli_stmt_bind_param($stmt_total, "ss", $search_query, $search_query);
        mysqli_stmt_execute($stmt_total);
        $result_total = mysqli_stmt_get_result($stmt_total);
        $row_total_loc = mysqli_fetch_assoc($result_total);
        $total_locataire = $row_total_loc['total_locataire'] ?? 0;
        mysqli_stmt_close($stmt_total);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Paiements</title>
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
    </style>
</head>
<body>
    <div class="alert-container">
        </div>

    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Paiements - Location</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-light add-btn" data-bs-toggle="modal" data-bs-target="#paiementModal">
                        <i class="fas fa-plus-circle"></i> Ajouter un paiement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="stats-container">
            <div class="stats-card">
                <div class="stats-value"><?php echo $row_total['total'] ?? 0; ?></div>
                <div class="stats-label">Paiements enregistrés</div>
            </div>
            <div class="stats-card">
                <div class="stats-value"><?php echo number_format($row_total['somme'] ?? 0, 2, ',', ' '); ?> CFA</div>
                <div class="stats-label">Montant total</div>
            </div>

            <div class="stats-card">
                <div class="stats-value">
                    <?php 
                    if (!empty($search_query)) {
                        echo number_format($total_locataire, 2, ',', ' ') . ' CFA';
                    } else {
                        echo '<i class="fas fa-user" style="font-size: 1.5rem;"></i>';
                    }
                    ?>
                </div>
                <div class="stats-label">
                    <?php
                    if (!empty($search_query)) {
                        echo 'Total pour ' . htmlspecialchars($_GET['search_locataire']);
                    } else {
                        echo 'Rechercher un locataire';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h5 class="mb-0">Liste des paiements</h5>
                    <div class="card-toolbar">
                        <form method="GET" action="payement.php" class="input-group search-container">
                            <input type="text" class="form-control" placeholder="Rechercher par locataire ou type de bien..." name="search_locataire" value="<?php echo htmlspecialchars($_GET['search_locataire'] ?? ''); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type du bien</th>
                                <th>Locataire</th>
                                <th>Montant</th>
                                <th>Mode Paiement</th>
                                <th>Date Opération</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($res_tab && mysqli_num_rows($res_tab) > 0) {
                                while ($row = mysqli_fetch_assoc($res_tab)) {
                                    echo '<tr>
                                        <td class="table-cell">Pay-' . sprintf('%04d', htmlspecialchars($row['id_payement'])) . '</td>
                                        <td class="table-cell">' . htmlspecialchars($row['type_bien']) . '</td>
                                        <td class="table-cell">' . htmlspecialchars($row['nom_complet']) . '</td>
                                        <td class="table-cell">' . number_format($row['montant'], 2, ',', ' ') . ' CFA</td>
                                        <td class="table-cell">' . htmlspecialchars($row['mode_payement']) . '</td>
                                        <td class="table-cell">' . date('d/m/Y', strtotime($row['date_operation'])) . '</td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="modifier_paiement.php?id=' . htmlspecialchars($row['id_payement']) . '" class="btn btn-sm action-btn btn-edit" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="supprimer_paiement.php?id=' . htmlspecialchars($row['id_payement']) . '" class="btn btn-sm action-btn btn-delete" title="Supprimer" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce paiement ?\');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                                
                                                <a href="facture_paiement.php?id=' . htmlspecialchars($row['id_payement']) . '" class="btn btn-sm action-btn btn-print" title="Voir la Facture" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center py-4">Aucun paiement trouvé';
                                if (!empty($_GET['search_locataire'])) {
                                    echo ' pour "' . htmlspecialchars($_GET['search_locataire']) . '"';
                                }
                                echo '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Affichage de <?php echo (($page - 1) * $limit) + 1; ?> à 
                        <?php 
                        $end = min($page * $limit, $total_records);
                        echo $end;
                        ?> 
                        sur <?php echo $total_records; ?> paiements
                    </div>
                    
                    <nav aria-label="Pagination">
                        <ul class="pagination">
                            <!-- Bouton Précédent -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" 
                                   href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   aria-label="Précédent">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Pages -->
                            <?php
                            // Afficher les pages autour de la page courante
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Bouton Suivant -->
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" 
                                   href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   aria-label="Suivant">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="printSection" class="print-section" style="display: none;">
        </div>

    <div class="modal fade" id="paiementModal" tabindex="-1" aria-labelledby="paiementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paiementModalLabel">Ajouter un paiement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="payement.php">
                        <div class="mb-3">
                            <label for="id_location" class="form-label">Location (Bien - Locataire)</label>
                            <select name="id_location" id="id_location" class="form-control" required>
                                <option value="">Choisir la location</option>
                                <?php
                                $req_locations = "SELECT
                                    loc.id_location,
                                    b.type AS type_bien,
                                    CONCAT(loca.nom_loca, ' ', loca.prenom_loca) AS nom_complet
                                FROM
                                    location loc
                                JOIN
                                    bien b ON loc.id_bien = b.id_bien
                                JOIN
                                    locataire loca ON loc.id_loca = loca.id_loca
                                ORDER BY loc.id_location DESC";
                                $res_locations = mysqli_query($conn, $req_locations);

                                if ($res_locations && mysqli_num_rows($res_locations) > 0) {
                                    while ($loc = mysqli_fetch_assoc($res_locations)) {
                                        echo '<option value="' . htmlspecialchars($loc['id_location']) . '">';
                                        echo htmlspecialchars($loc['type_bien']) . ' - ' . htmlspecialchars($loc['nom_complet']);
                                        echo '</option>';
                                    }
                                } else {
                                    echo '<option value="">Aucune location disponible</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant du paiement</label>
                            <div class="input-group">
                                <input type="number" name="montant" id="montant" required class="form-control" placeholder="Entrez le montant" step="0.01" min="0">
                                <span class="input-group-text">CFA</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mode_payement" class="form-label">Mode de paiement</label>
                            <select name="mode_payement" id="mode_payement" required class="form-control">
                                <option value="">Sélectionnez un mode</option>
                                <option value="Virement">Virement</option>
                                <option value="Chèque">Chèque</option>
                                <option value="Espèces">Espèces</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_operation" class="form-label">Date de l'opération</label>
                            <input type="date" name="date_operation" id="date_operation" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="d-grid mt-4">
                            <input type="submit" name="enregistrer" class="btn btn-primary btn-lg" value="Enregistrer">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function(){
        $('[title]').tooltip();
        
        $('#paiementModal').on('shown.bs.modal', function () {
            $('#id_location').focus();
        });
        
        $('#montant').on('change', function() {
            if ($(this).val() <= 0) {
                showAlert('Le montant doit être supérieur à zéro', 'danger');
                $(this).val('');
            }
        });
        
        function showAlert(message, type) {
            const alert = $(`
                <div class="alert alert-${type} alert-dismissible fade show fade-in" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
            
            $('.alert-container').append(alert);
            
            setTimeout(() => {
                alert.alert('close');
            }, 5000);
        }
        
        <?php if(isset($_POST['enregistrer']) && !empty($_POST['enregistrer'])): ?>
            $(document).ready(function() {
                showAlert('Paiement enregistré avec succès !', 'success');
            });
        <?php endif; ?>
    });

    // Fonction pour imprimer un paiement
    function imprimerPaiement(idPaiement) {
        // Récupérer les données du paiement via AJAX
        $.ajax({
            url: 'get_paiement_details.php',
            type: 'GET',
            data: { id: idPaiement },
            success: function(response) {
                const paiement = JSON.parse(response);
                
                // Générer le contenu HTML pour l'impression
                const printContent = `
                    <div style="padding: 20px; font-family: Arial, sans-serif;">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h2 style="color: #4361ee; margin-bottom: 5px;">REÇU DE PAIEMENT</h2>
                            <p style="color: #666; font-size: 14px;">N° Pay-${String(paiement.id_payement).padStart(4, '0')}</p>
                        </div>
                        
                        <div style="border: 2px solid #4361ee; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px 0; width: 40%;"><strong>Locataire:</strong></td>
                                    <td style="padding: 8px 0;">${paiement.nom_complet}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Type de bien:</strong></td>
                                    <td style="padding: 8px 0;">${paiement.type_bien}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Montant payé:</strong></td>
                                    <td style="padding: 8px 0; font-weight: bold; color: #20c997;">${parseFloat(paiement.montant).toLocaleString('fr-FR', {minimumFractionDigits: 2})} CFA</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Mode de paiement:</strong></td>
                                    <td style="padding: 8px 0;">${paiement.mode_payement}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Date de l'opération:</strong></td>
                                    <td style="padding: 8px 0;">${new Date(paiement.date_operation).toLocaleDateString('fr-FR')}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Date d'émission:</strong></td>
                                    <td style="padding: 8px 0;">${new Date().toLocaleDateString('fr-FR')}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">
                            <p>Ce document fait foi de paiement effectué</p>
                            <p style="margin-top: 40px;">Signature</p>
                            <div style="margin-top: 60px; border-top: 1px solid #ccc; padding-top: 10px;">
                                <p>Reçu généré automatiquement le ${new Date().toLocaleString('fr-FR')}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Insérer le contenu dans la section d'impression
                $('#printSection').html(printContent).show();
                
                // Lancer l'impression
                window.print();
                
                // Masquer à nouveau la section après l'impression
                setTimeout(() => {
                    $('#printSection').hide().empty();
                }, 100);
            },
            error: function() {
                alert('Erreur lors de la récupération des données du paiement');
            }
        });
    }
</script>
</body>
</html>