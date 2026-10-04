<?php
include('entete.php');

if (isset($_POST['enregistrer'])) 
{
    $bien_demande = mysqli_escape_string($conn, $_POST['bien_demande']);
    $quartier = mysqli_escape_string($conn, $_POST['quartier']);
    $montant = mysqli_escape_string($conn, $_POST['montant']);
    $description = mysqli_escape_string($conn, $_POST['description']);
    $etat = mysqli_escape_string($conn, $_POST['etat']);
    $date_operation = mysqli_escape_string($conn, $_POST['date_operation']);

    // Correction de la requête d'insertion pour inclure le champ 'etat'
    $req = "INSERT INTO demandeur (bien_demande, quartier, montant, description, etat, date_operation) 
            VALUES('$bien_demande','$quartier','$montant','$description','$etat','$date_operation')";

    $execution = mysqli_query($conn, $req);
    if($execution) {
        echo '<script type="text/javascript">alert(\'Demande enregistrée avec succès !!\');</script>'; 
    }
}

// Configuration de la pagination
$limit = 1; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Requête pour le nombre total d'éléments
$count_query = "SELECT COUNT(*) as total FROM demandeur";
$result_count = mysqli_query($conn, $count_query);
$total_row = mysqli_fetch_assoc($result_count);
$total_records = $total_row['total'];

// Calcul du nombre total de pages
$total_pages = ceil($total_records / $limit);

// Requête principale avec pagination
$req_tab = "SELECT * FROM demandeur ORDER BY id_demandeur DESC LIMIT $limit OFFSET $offset";
$res_tab = mysqli_query($conn, $req_tab);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande</title>
    <!-- Font Awesome local -->
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    
    <!-- Bootstrap local -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <style>
        /* Styles existants conservés */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .container {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        
        @media (min-width: 576px) {
            .container {
                max-width: 540px;
            }
        }
        
        @media (min-width: 768px) {
            .container {
                max-width: 720px;
            }
        }
        
        @media (min-width: 992px) {
            .container {
                max-width: 960px;
            }
        }
        
        @media (min-width: 1200px) {
            .container {
                max-width: 1140px;
            }
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .col-md-6 {
            position: relative;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
        }
        
        @media (min-width: 768px) {
            .col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        
        .text-md-end {
            text-align: right;
        }
        
        @media (min-width: 768px) {
            .text-md-end {
                text-align: right !important;
            }
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 8px 20px;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-light {
            color: #212529;
            background-color: #f8f9fa;
            border-color: #f8f9fa;
        }
        
        .btn-light:hover {
            background-color: #e2e6ea;
            border-color: #dae0e5;
        }
        
        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 6px;
        }
        
        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }
        
        .form-control {
            display: block;
            width: 100%;
            height: calc(1.5em + 1rem + 2px);
            padding: 0.5rem 1rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 8px 0 0 8px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #4361ee;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .input-group > .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            margin-bottom: 0;
        }
        
        .input-group-append {
            display: flex;
        }
        
        .input-group-append .btn {
            position: relative;
            z-index: 2;
            border-radius: 0 8px 8px 0;
        }
        
        .mb-3 {
            margin-bottom: 1rem !important;
        }
        
        .mt-4 {
            margin-top: 1.5rem !important;
        }
        
        .d-grid {
            display: grid !important;
        }
        
        .mb-0 {
            margin-bottom: 0 !important;
        }
        
        .align-middle {
            vertical-align: middle !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .justify-content-center {
            justify-content: center !important;
        }
        
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            border-radius: 8px;
        }
        
        .page-item {
            margin: 0 2px;
        }
        
        .page-item:first-child .page-link {
            border-radius: 8px 0 0 8px;
        }
        
        .page-item:last-child .page-link {
            border-radius: 0 8px 8px 0;
        }
        
        .page-item.active .page-link {
            z-index: 1;
            color: #fff;
            background-color: #4361ee;
            border-color: #4361ee;
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
        
        .page-link {
            position: relative;
            display: block;
            padding: 0.5rem 0.75rem;
            margin-left: -1px;
            line-height: 1.25;
            color: #4361ee;
            background-color: #fff;
            border: 1px solid #dee2e6;
            text-decoration: none;
        }
        
        .page-link:hover {
            z-index: 2;
            color: #3a0ca3;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        
        /* Modal styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            display: none;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-dialog {
            position: relative;
            width: auto;
            margin: 1.75rem auto;
            pointer-events: none;
            max-width: 500px;
        }
        
        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            pointer-events: auto;
            background-color: #fff;
            background-clip: padding-box;
            border: none;
            border-radius: 8px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            outline: 0;
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #eef2f7;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-title {
            margin-bottom: 0;
            line-height: 1.5;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .modal-body {
            position: relative;
            flex: 1 1 auto;
            padding: 1rem;
        }
        
        .btn-close {
            padding: 0.5rem;
            margin: -0.5rem -0.5rem -0.5rem auto;
            background-color: transparent;
            border: 0;
            border-radius: 0.25rem;
            opacity: 0.5;
            cursor: pointer;
        }
        
        .btn-close:focus {
            outline: none;
        }
        
        /* Font Awesome icons as inline SVG */
        .fas {
            display: inline-block;
            width: 1em;
            height: 1em;
            vertical-align: -0.125em;
        }
        
        /* Custom styles from original code */
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-bg: #f8f9fc;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
            --border-radius: 8px;
        }
        
        body {
            background-color: var(--light-bg);
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
            background-color: #fff;
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
        
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
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
        
        .table td, .table th {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        
        .table tbody tr:first-child td {
            border-top: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            font-weight: 500;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.4);
            color: white;
        }
        
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
            white-space: nowrap;
            text-decoration: none;
            display: inline-block;
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
        
        .description-cell {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Correction pour la barre d'outils */
        .card-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
            width: 100%;
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
        }
        
        .table-cell {
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Modal adjustments */
        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }
        
        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 1.25rem;
            line-height: 1.5;
            border-radius: 8px;
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        .form-label {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
       
        .icon {
            display: inline-block;
            width: 1em;
            height: 1em;
            stroke-width: 0;
            stroke: currentColor;
            fill: currentColor;
        }
        
        /* Style pour le badge d'état */
        .etat-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .etat-location {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .etat-achat {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            border: 1px solid #17a2b8;
        }
        
        /* CORRECTION DES BOUTONS D'ACTION */
        .actions-cell {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .action-btn .icon {
            width: 16px;
            height: 16px;
        }
        
        /* Assurer la visibilité des boutons */
        .btn-edit, .btn-delete {
            opacity: 1;
            visibility: visible;
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

        @media (max-width: 767px) {
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
                    <h1 class="page-title">Demande</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-light add-btn" id="addDemandeBtn">
                        <svg class="icon" viewBox="0 0 16 16" width="16" height="16">
                            <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                        </svg>
                        Ajouter une demande
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Bien Demandé</th>
                                <th>Quartier</th>
                                <th>Montant</th>
                                <th>Description</th>
                                <th>État</th>
                                <th>Date</th>
                                <th>Utilisateur</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while ($row = mysqli_fetch_array($res_tab)): 
                                // Déterminer la classe CSS en fonction de l'état
                                $etat_class = ($row['etat'] == 'Location') ? 'etat-location' : 'etat-achat';
                            ?>
                                <tr>
                                    <td class="table-cell">Dem-<?=sprintf('%04d', htmlspecialchars($row['id_demandeur']))?></td>
                                    <td class="table-cell"><?php echo htmlspecialchars($row['bien_demande']); ?></td>
                                    <td class="table-cell"><?php echo htmlspecialchars($row['quartier']); ?></td>
                                    <td class="table-cell"><?php echo number_format($row['montant'], 2, ',', ' ');  ?> CFA</td>
                                    <td class="description-cell" title="<?php echo htmlspecialchars($row['description']); ?>">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </td>
                                    <td class="table-cell">
                                        <span class="etat-badge <?php echo $etat_class; ?>">
                                            <?php echo htmlspecialchars($row['etat']); ?>
                                        </span>
                                    </td>
                                    <td class="table-cell"><?php echo date('d/m/Y', strtotime($row['date_operation'])); ?></td>
                                    <td class="table-cell">Admin</td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="modifier_demande.php?id=<?php echo htmlspecialchars($row['id_demandeur']); ?>" 
                                               class="btn btn-sm action-btn btn-edit">
                                                <svg class="icon" viewBox="0 0 16 16" width="16" height="16">
                                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                                </svg>
                                            </a>
                                            <a href="supprimer_demande.php?id=<?php echo htmlspecialchars($row['id_demandeur']); ?>" 
                                               class="btn btn-sm action-btn btn-delete"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                                                <svg class="icon" viewBox="0 0 16 16" width="16" height="16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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
                        sur <?php echo $total_records; ?> demandes
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

    <!-- Modal d'ajout de demande -->
    <div class="modal" id="demandeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Demande</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                        <svg class="icon" viewBox="0 0 16 16" width="16" height="16">
                            <path fill-rule="evenodd" d="M13.854 2.146a.5.5 0 0 1 0 .708l-11 11a.5.5 0 0 1-.708-.708l11-11a.5.5 0 0 1 .708 0z"/>
                            <path fill-rule="evenodd" d="M2.146 2.146a.5.5 0 0 0 0 .708l11 11a.5.5 0 0 0 .708-.708l-11-11a.5.5 0 0 0-.708 0z"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="demandeur.php">
                        <div class="mb-3">
                            <label for="bien_demande" class="form-label">Bien Demandé</label>
                            <input type="text" name="bien_demande" id="bien_demande" required class="form-control" placeholder="Entrez le bien demandé">
                        </div>
                        
                        <div class="mb-3">
                            <label for="quartier" class="form-label">Quartier</label>
                            <input type="text" name="quartier" id="quartier" required class="form-control" placeholder="Entrez le quartier">
                        </div>
                        
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant (CFA)</label>
                            <input type="number" name="montant" id="montant" required class="form-control" placeholder="Entrez le montant" step="0.01">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" required class="form-control" placeholder="Entrez la description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="etat" class="form-label">État de la demande</label>
                            <select name="etat" id="etat" required class="form-control">
                                <option value="">Sélectionner un état</option>
                                <option value="Location">Location</option>
                                <option value="Achat">Achat</option>
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

    
    <script>

        // Fonction pour gérer l'ouverture et la fermeture du modal

        const demandeModal = document.getElementById('demandeModal');
        const addDemandeBtn = document.getElementById('addDemandeBtn');
        const closeModalBtn = document.querySelector('.btn-close');
        
        addDemandeBtn.addEventListener('click', () => {
            demandeModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            document.getElementById('bien_demande').focus();
        });
        
        closeModalBtn.addEventListener('click', () => {
            demandeModal.style.display = 'none';
            document.body.style.overflow = '';
        });
        
        // Fermer le modal en cliquant à l'extérieur

        window.addEventListener('click', (e) => {
            if (e.target === demandeModal) {
                demandeModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
        
        // Fonction de recherche

        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const tableRows = document.querySelectorAll('tbody tr');
        
        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            
            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let found = false;
                
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                    }
                });
                
                row.style.display = found ? '' : 'none';
            });
        }
        
        searchInput.addEventListener('input', filterTable);
        searchButton.addEventListener('click', filterTable);
        
        // Fonction de confirmation pour la suppression
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')) {
                    e.preventDefault();
                }
            });
        });
        
        
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
            

            document.querySelectorAll('[title]').forEach(element => {
                element.addEventListener('mouseover', function() {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = this.title;
                    tooltip.style.position = 'absolute';
                    tooltip.style.background = 'rgba(0,0,0,0.8)';
                    tooltip.style.color = 'white';
                    tooltip.style.padding = '5px 10px';
                    tooltip.style.borderRadius = '4px';
                    tooltip.style.zIndex = '1000';
                    tooltip.style.maxWidth = '300px';
                    
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                    tooltip.style.left = rect.left + 'px';
                    
                    this.addEventListener('mouseout', () => {
                        document.body.removeChild(tooltip);
                    });
                });
            });
        });
    </script>

    <script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>