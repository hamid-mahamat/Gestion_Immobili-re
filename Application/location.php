<?php

include('entete.php'); 

if (isset($_POST['enregistrer'])) {
    $id_bien = mysqli_real_escape_string($conn, $_POST['id_bien']);
    $id_loca = mysqli_real_escape_string($conn, $_POST['id_loca']);
    $date_debut = mysqli_real_escape_string($conn, $_POST['date_debut']);
    $date_fin = mysqli_real_escape_string($conn, $_POST['date_fin']);
    $loyer_mensuel = mysqli_real_escape_string($conn, $_POST['loyer_mensuel']);
    $caution = mysqli_real_escape_string($conn, $_POST['caution']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);

    $req = "INSERT INTO location (id_bien, id_loca, date_debut, date_fin, loyer_mensuel, caution, date_operation) 
            VALUES('$id_bien', '$id_loca', '$date_debut', '$date_fin', '$loyer_mensuel', '$caution', '$date_operation')";

    $execution = mysqli_query($conn, $req);
    if ($execution) {
        echo '<script type="text/javascript">alert(\'Contrat de location enregistré avec succès !!\');</script>';
    } else {
        echo '<script type="text/javascript">alert(\'Erreur lors de l\'enregistrement du contrat : ' . mysqli_error($conn) . '\');</script>';
    }
}

// Configuration de la pagination
$limit = 2; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Requête pour le nombre total d'éléments
$count_query = "SELECT COUNT(*) as total 
                FROM location 
                LEFT JOIN bien ON location.id_bien = bien.id_bien 
                LEFT JOIN locataire ON location.id_loca = locataire.id_loca";
$result_count = mysqli_query($conn, $count_query);
$total_row = mysqli_fetch_assoc($result_count);
$total_records = $total_row['total'];

// Calcul du nombre total de pages
$total_pages = ceil($total_records / $limit);

// Requête principale avec pagination
$req_tab = "SELECT location.*, bien.type, 
           CONCAT(locataire.nom_loca, ' ', locataire.prenom_loca) AS locataire_complet 
           FROM location 
           LEFT JOIN bien ON location.id_bien = bien.id_bien 
           LEFT JOIN locataire ON location.id_loca = locataire.id_loca
           ORDER BY location.id_location DESC LIMIT $limit OFFSET $offset";
$res_tab = mysqli_query($conn, $req_tab);

if (!$res_tab) {
    die("Erreur dans la requête SQL : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrats de Location</title>
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
        
        * {
            box-sizing: border-box;
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
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Styles ajoutés pour les bordures du tableau */
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
            padding: 12px 15px;
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
            padding: 5px 8px;
            margin: 0 3px;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: all 0.2s;
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
            flex-wrap: nowrap;
            gap: 5px;
        }
        
        .table-cell {
            white-space: normal;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 12px 15px !important;
            vertical-align: middle;
        }
        
        .table-cell-actions {
            min-width: 150px;
            white-space: nowrap;
        }
        
        .table-cell-number {
            text-align: right;
            white-space: nowrap;
        }
        
        .table-cell-date {
            white-space: nowrap;
        }
        
        .table-cell-id {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .card-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
            width: 100%;
        }
        
        .table {
            font-size: 0.9rem;
            width: 100%;
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
        }
        
        @media (max-width: 991px) {
            .table-cell {
                max-width: 150px;
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
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .add-btn {
                font-size: 0.9rem;
                padding: 8px 12px;
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
        
        @media (max-width: 575px) {
            .table-cell {
                max-width: 120px;
                font-size: 0.85rem;
            }
            
            .action-btn {
                padding: 4px 6px;
                font-size: 0.8rem;
            }
            
            .btn-primary {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Contrats de Location</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-light add-btn" data-bs-toggle="modal" data-bs-target="#locationModal">
                        <i class="fas fa-plus-circle"></i> Ajouter un contrat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h5 class="mb-0">Liste des Contrats</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered">
                        <thead>
                            <tr>
                                <th>ID Contrat</th>
                                <th>Type de Bien</th>
                                <th>Locataire</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Mensualité</th>
                                <th>Caution</th>
                                <th>Date Opération</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while ($row = mysqli_fetch_array($res_tab)): 
                            ?>
                                <tr>
                                    <td class="table-cell table-cell-id"><?php echo isset($row['id_location']) ? 'CONTR-' . str_pad($row['id_location'], 4, '0', STR_PAD_LEFT) : 'N/A'; ?></td>
                                    <td class="table-cell"><?php echo isset($row['type']) ? htmlspecialchars($row['type']) : 'N/A'; ?></td>
                                    <td class="table-cell"><?php echo isset($row['locataire_complet']) ? htmlspecialchars($row['locataire_complet']) : 'N/A'; ?></td>
                                    <td class="table-cell table-cell-date">
                                        <?php echo !empty($row['date_debut']) ? date('d/m/Y', strtotime($row['date_debut'])) : 'N/A'; ?>
                                    </td>
                                    <td class="table-cell table-cell-date">
                                        <?php echo !empty($row['date_fin']) ? date('d/m/Y', strtotime($row['date_fin'])) : 'N/A'; ?>
                                    </td>
                                    <td class="table-cell table-cell-number">
                                        <?php echo isset($row['loyer_mensuel']) ? number_format($row['loyer_mensuel'], 2, ',', ' ') : '0,00'; ?> CFA
                                    </td>
                                    <td class="table-cell table-cell-number">
                                        <?php echo isset($row['caution']) ? number_format($row['caution'], 2, ',', ' ') : '0,00'; ?> CFA
                                    </td>
                                    <td class="table-cell table-cell-date">
                                        <?php echo !empty($row['date_operation']) ? date('d/m/Y', strtotime($row['date_operation'])) : 'N/A'; ?>
                                    </td>
                                    <td class="table-cell-actions">
                                        <div class="actions-cell">
                                            <a href="modifier_location.php?id=<?php echo htmlspecialchars($row['id_location']); ?>" 
                                               class="btn btn-sm action-btn btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="supprimer_location.php?id=<?php echo htmlspecialchars($row['id_location']); ?>" 
                                               class="btn btn-sm action-btn btn-delete"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat de location ?');"
                                               title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
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
                        sur <?php echo $total_records; ?> contrats
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

    <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="locationModalLabel">Nouveau Contrat de Location</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="location.php">
                        <div class="mb-3">
                            <label for="id_bien" class="form-label">Type de Bien</label>
                            <select name="id_bien" id="id_bien" class="form-control" required>
                                <option value="">Choisir le type de bien</option>
                                <?php
                                $req_bien = "SELECT id_bien, type FROM bien";
                                $res_bien = mysqli_query($conn, $req_bien);
                                while($bien = mysqli_fetch_assoc($res_bien)){
                                    echo '<option value="'.htmlspecialchars($bien['id_bien']).'">'.htmlspecialchars($bien['type']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_loca" class="form-label">Locataire</label>
                            <select name="id_loca" id="id_loca" class="form-control" required>
                                <option value="">Choisir le locataire</option>
                                <?php
                                $req_loca = "SELECT id_loca, nom_loca, prenom_loca FROM locataire";
                                $res_loca = mysqli_query($conn, $req_loca);
                                while($locataire = mysqli_fetch_assoc($res_loca)){
                                    echo '<option value="'.htmlspecialchars($locataire['id_loca']).'">'.htmlspecialchars($locataire['nom_loca']).' '.htmlspecialchars($locataire['prenom_loca']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_debut" class="form-label">Date de Début</label>
                            <input type="date" name="date_debut" id="date_debut" required class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_fin" class="form-label">Date de Fin</label>
                            <input type="date" name="date_fin" id="date_fin" required class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label for="loyer_mensuel" class="form-label">Mensualité (CFA)</label>
                            <input type="number" name="loyer_mensuel" id="loyer_mensuel" required class="form-control" placeholder="Entrez le loyer mensuel" step="0.01">
                        </div>
                        
                        <div class="mb-3">
                            <label for="caution" class="form-label">Caution (CFA)</label>
                            <input type="number" name="caution" id="caution" required class="form-control" placeholder="Entrez la caution" step="0.01">
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_operation" class="form-label">Date de l'Opération</label>
                            <input type="date" name="date_operation" id="date_operation" required class="form-control">
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
   <script src="assets/js/script.js"></script>
    <script>
        $(document).ready(function(){
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            $('#locationModal').on('shown.bs.modal', function () {
                $('#id_bien').focus();
            });
            
            $('#date_debut').on('change', function() {
                var startDate = new Date(this.value);
                if ($('#date_fin').val() === '') {
                    var endDate = new Date(startDate);
                    endDate.setFullYear(endDate.getFullYear() + 1);
                    var formattedDate = endDate.toISOString().split('T')[0];
                    $('#date_fin').val(formattedDate);
                }
            });
        });
    </script>
</body>
</html>