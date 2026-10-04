<?php
include('entete.php');

// Configuration de la pagination
$limit = 5; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Requête pour récupérer toutes les ventes (biens vendus) avec les paiements
$search_query = "";    
if (isset($_GET['search_acheteur']) && !empty($_GET['search_acheteur'])) {
    $search_query = '%' . $_GET['search_acheteur'] . '%';
}

// Requête pour le nombre total de ventes
$count_query = "SELECT COUNT(*) as total 
                FROM ventes v
                INNER JOIN bien b ON v.id_bien = b.id_bien
                WHERE v.prix_vente > 0";

if (!empty($search_query)) {
    $count_query .= " AND (v.nom_acheteur LIKE ? OR b.type LIKE ?)";
}

if ($stmt_count = mysqli_prepare($conn, $count_query)) {
    if (!empty($search_query)) {
        mysqli_stmt_bind_param($stmt_count, "ss", $search_query, $search_query);
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

// Requête simplifiée sans les colonnes de paiement
$sql_ventes = "SELECT
    v.id_vente,
    b.type AS type_bien,
    v.nom_acheteur,
    v.prenom_acheteur,
    v.prix_vente,
    v.date_operation AS date_vente
FROM
    ventes v
INNER JOIN
    bien b ON v.id_bien = b.id_bien
WHERE v.prix_vente > 0";

if (!empty($search_query)) {
    $sql_ventes .= " AND (v.nom_acheteur LIKE ? OR b.type LIKE ?)";
}

$sql_ventes .= " ORDER BY v.date_operation DESC LIMIT ? OFFSET ?";    

if ($stmt_ventes = mysqli_prepare($conn, $sql_ventes)) {
    if (!empty($search_query)) {
        mysqli_stmt_bind_param($stmt_ventes, "ssii", $search_query, $search_query, $limit, $offset);
    } else {
        mysqli_stmt_bind_param($stmt_ventes, "ii", $limit, $offset);
    }
    mysqli_stmt_execute($stmt_ventes);
    $res_tab = mysqli_stmt_get_result($stmt_ventes);
} else {
    $res_tab = false;
    echo '<script type="text/javascript">alert("Erreur de préparation de la requête : ' . mysqli_error($conn) . '");</script>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiements - Achats</title>
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
        
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .btn-details {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            border: 1px solid #17a2b8;
        }
        
        .btn-details:hover {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-print {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .btn-print:hover {
            background-color: #28a745;
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .search-container {
            max-width: 100%;
            flex-grow: 1;
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
        
        .table-cell {
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            color: #6c757d;
            padding: 10px 20px;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background-color: transparent;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-color);
        }
        
        .badge-paiement {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 12px;
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
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Paiements - Achats</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <!-- Bouton supprimé comme demandé -->
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="payement.php">Paiements Locations</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="paiement_achat.php">Paiements Achats</a>
            </li>
        </ul>
        
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h5 class="mb-0">Liste des biens vendus</h5>
                    <div class="card-toolbar">
                        <form method="GET" action="paiement_achat.php" class="input-group search-container">
                            <input type="text" class="form-control" placeholder="Rechercher par acheteur ou type de bien..." name="search_acheteur" value="<?php echo htmlspecialchars($_GET['search_acheteur'] ?? ''); ?>">
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
                                <th>ID Vente</th>
                                <th>Type du bien</th>
                                <th>Acheteur</th>
                                <th>Prix de vente</th>
                                <th>Date vente</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($res_tab && mysqli_num_rows($res_tab) > 0) {
                                while ($row = mysqli_fetch_assoc($res_tab)) {
                                    echo '<tr>
                                        <td class="table-cell">Vente-' . sprintf('%04d', htmlspecialchars($row['id_vente'])) . '</td>
                                        <td class="table-cell">' . htmlspecialchars($row['type_bien']) . '</td>
                                        <td class="table-cell">' . htmlspecialchars($row['nom_acheteur']) . ' ' . htmlspecialchars($row['prenom_acheteur']) . '</td>
                                        <td class="table-cell">' . number_format($row['prix_vente'], 2, ',', ' ') . ' CFA</td>
                                        <td class="table-cell">' . date('d/m/Y', strtotime($row['date_vente'])) . '</td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="details_vente.php?id_vente=' . htmlspecialchars($row['id_vente']) . '" class="btn btn-sm action-btn btn-details" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                              
                                            </div>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center py-4">Aucun bien vendu trouvé';
                                if (!empty($_GET['search_acheteur'])) {
                                    echo ' pour "' . htmlspecialchars($_GET['search_acheteur']) . '"';
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
                        sur <?php echo $total_records; ?> ventes
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

<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function(){
        $('[title]').tooltip();
        
        // Fonction pour générer le PDF de la facture de vente
        function genererFactureVente(idVente) {
            window.open('generer_facture_vente.php?id_vente=' + idVente, '_blank');
        }
        
        // Exposer la fonction globalement
        window.genererFactureVente = genererFactureVente;
    });
</script>
</body>
</html>