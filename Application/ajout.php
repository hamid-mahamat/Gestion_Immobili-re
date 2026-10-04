<?php
include('entete.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $id_pro = mysqli_real_escape_string($conn, $_POST['id_pro']);
    $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
    $surface = mysqli_real_escape_string($conn, $_POST['surface']);
    $nb_chambre = mysqli_real_escape_string($conn, $_POST['nb_chambre']);
    $prix_location = mysqli_real_escape_string($conn, $_POST['prix_location']);
    $statut = mysqli_real_escape_string($conn, $_POST['statut']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);
    
    // Insertion dans la base de données
    $sql = "INSERT INTO bien (id_pro, type, adresse, surface, nb_chambre, prix_location, statut, date_operation) 
            VALUES ('$id_pro', '$type', '$adresse', '$surface', '$nb_chambre', '$prix_location', '$statut', '$date_operation')";
    
    if(mysqli_query($conn, $sql)) {
        $success_message = "Le bien a été ajouté avec succès!";
    } else {
        $error_message = "Erreur lors de l'ajout du bien: " . mysqli_error($conn);
    }
}

// MISE À JOUR AUTOMATIQUE DES STATUTS - CORRECTION DU PROBLÈME
// Vérifier si un bien est loué et mettre à jour son statut automatiquement
$check_rentals_sql = "UPDATE bien 
                     SET statut = 'Loué' 
                     WHERE id_bien IN (SELECT id_bien FROM location) 
                     AND statut = 'Non loué'";
mysqli_query($conn, $check_rentals_sql);

// Configuration de la pagination
$limit = 3; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Requête pour le nombre total d'éléments
$count_query = "SELECT COUNT(*) as total 
                FROM bien b
                INNER JOIN propritaire p ON b.id_pro = p.id_pro";

$conditions = [];
$params = [];

if(isset($_GET['rechercher'])) {
    if(!empty($_GET['type'])) {
        $conditions[] = "b.type LIKE ?";
        $params[] = '%' . $_GET['type'] . '%';
    }
    
    if(!empty($_GET['adresse'])) {
        $conditions[] = "b.adresse LIKE ?";
        $params[] = '%' . $_GET['adresse'] . '%';
    }
}

// Ajout des conditions si elles existent
if(!empty($conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $conditions);
}

// Préparation de la requête de comptage
if ($stmt_count = mysqli_prepare($conn, $count_query)) {
    if(!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt_count, $types, ...$params);
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Biens</title>
    <!-- Font Awesome local -->
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    
    <!-- Bootstrap local -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-bg: #f8f9fc;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
            --border-radius: 8px;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-color: #dee2e6;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .page-header {
            background: linear-gradient(120deg, #4361ee, #3a0ca3);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }
        
        .page-title {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }
        
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0 !important;
        }
        
        .table-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0; /* Important pour le défilement */
        }
        
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: auto;
            flex: 1;
            min-height: 0; /* Important pour le défilement */
        }
        
        /* AJOUT DE BORDURES AU TABLEAU */
        .table {
            border-collapse: collapse;
            border: 1px solid var(--border-color);
            width: 100%;
            margin: 0;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: 1px solid var(--border-color);
            padding: 15px 20px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tbody td {
            border: 1px solid var(--border-color);
            padding: 12px 15px;
            vertical-align: middle;
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
        
        .btn-info {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            border: 1px solid #17a2b8;
        }
        
        .btn-info:hover {
            background-color: #17a2b8;
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
        
        /* Correction pour la barre d'outils */
        .card-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
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
            
            .table thead th,
            .table tbody td {
                padding: 10px 12px;
                font-size: 0.9rem;
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
        
        .statut-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            display: inline-block;
            text-align: center;
            min-width: 90px;
        }
        
        /* Styles pour les différents statuts */
        .statut-vendu, .statut-loue {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }
        
        .statut-en-vente, .statut-nonloue {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            animation: fadeInOut 3s ease-in-out forwards;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        
        .no-results {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
        }
        
        .table-header {
            background-color: #f1f5fd;
            font-weight: 600;
        }
        
        .fixed-first-column {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 5;
        }
    </style>
</head>
<body>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Biens Immobiliers</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-light add-btn" data-bs-toggle="modal" data-bs-target="#bienModal">
                        <i class="fas fa-plus-circle"></i> Ajouter un bien
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container main-container">
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h5 class="mb-0">Liste des biens </h5>
                    <div class="card-toolbar">
                        <form method="get" action="" class="d-flex flex-wrap gap-2">
                            <div class="input-group search-container">
                                <input type="text" placeholder="Type de bien" name="type" class="form-control" value="<?php echo htmlspecialchars($_GET['type'] ?? ''); ?>">
                            </div>
                            <div class="input-group search-container">
                                <input type="text" placeholder="Adresse" name="adresse" class="form-control" value="<?php echo htmlspecialchars($_GET['adresse'] ?? ''); ?>">
                            </div>
                            <button type="submit" name="rechercher" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Rechercher
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Propriétaire</th>
                                    <th>Adresse</th>
                                    <th>Surface</th>
                                    <th>Chambres</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT b.*, p.nom_pro, p.prenom_pro 
                                        FROM bien b
                                        INNER JOIN propritaire p ON b.id_pro = p.id_pro";
                                
                                $conditions = [];
                                $params = [];
                                
                                if(isset($_GET['rechercher'])) {
                                    if(!empty($_GET['type'])) {
                                        $conditions[] = "b.type LIKE ?";
                                        $params[] = '%' . $_GET['type'] . '%';
                                    }
                                    
                                    if(!empty($_GET['adresse'])) {
                                        $conditions[] = "b.adresse LIKE ?";
                                        $params[] = '%' . $_GET['adresse'] . '%';
                                    }
                                }
                                
                                // Ajout des conditions si elles existent
                                if(!empty($conditions)) {
                                    $sql .= " WHERE " . implode(" AND ", $conditions);
                                }
                                
                                // Ajout de la pagination
                                $sql .= " ORDER BY b.id_bien DESC LIMIT ? OFFSET ?";
                                
                                // Ajout des paramètres de pagination
                                $params[] = $limit;
                                $params[] = $offset;
                                
                                // Préparation de la requête
                                $stmt = mysqli_prepare($conn, $sql);
                                
                                // Liaison des paramètres si nécessaire
                                if(!empty($params)) {
                                    // Déterminer le type des paramètres
                                    $types = '';
                                    foreach ($params as $param) {
                                        if (is_int($param)) {
                                            $types .= 'i'; // integer
                                        } else {
                                            $types .= 's'; // string
                                        }
                                    }
                                    mysqli_stmt_bind_param($stmt, $types, ...$params);
                                }
                                
                                mysqli_stmt_execute($stmt);
                                $res_tab = mysqli_stmt_get_result($stmt);
                                
                                // Affichage des résultats
                                if(mysqli_num_rows($res_tab) > 0) {
                                    while ($row = mysqli_fetch_assoc($res_tab)) {
                                        // Détermination de la classe CSS en fonction du statut
                                        $statut_class = '';
                                        switch ($row['statut']) {
                                            case 'Vendu':
                                                $statut_class = 'statut-vendu';
                                                break;
                                            case 'Loué':
                                                $statut_class = 'statut-loue';
                                                break;
                                            case 'En vente':
                                                $statut_class = 'statut-en-vente';
                                                break;
                                            case 'Non loué':
                                                $statut_class = 'statut-nonloue';
                                                break;
                                            default:
                                                $statut_class = 'statut-nonloue';
                                        }
                                        
                                        echo '<tr>
                                            <td class="table-cell">B-' . sprintf('%04d', htmlspecialchars($row['id_bien'])) . '</td>
                                            <td class="table-cell">'.htmlspecialchars($row['type']).'</td>
                                            <td class="table-cell">'.htmlspecialchars($row['nom_pro']).' '.htmlspecialchars($row['prenom_pro']).'</td>
                                            <td class="table-cell">'.htmlspecialchars($row['adresse']).'</td>
                                            <td class="table-cell">'.htmlspecialchars($row['surface']).' m²</td>
                                            <td class="table-cell">'.htmlspecialchars($row['nb_chambre']).'</td>
                                            <td class="table-cell">'.number_format($row['prix_location'],2, ',', ' ').' CFA</td>
                                            <td class="table-cell">
                                                <span class="statut-badge '.$statut_class.'">'.$row['statut'].'</span>
                                            </td>
                                            <td class="table-cell">'.date('d/m/Y', strtotime($row['date_operation'])).'</td>
                                            <td>
                                                <div class="actions-cell">
                                                    <a href="modifier_bien.php?id='.$row['id_bien'].'" class="btn btn-sm action-btn btn-edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="supprimer_bien.php?id='.$row['id_bien'].'" class="btn btn-sm action-btn btn-delete" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce bien ?\')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="10" class="no-results">Aucun bien trouvé. Ajoutez un nouveau bien ou modifiez vos critères de recherche.</td></tr>';
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
                            sur <?php echo $total_records; ?> biens
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
    </div>

    <!-- Modal d'ajout de bien -->
    <div class="modal fade" id="bienModal" tabindex="-1" aria-labelledby="bienModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bienModalLabel">Ajouter un Bien </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formbien" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type de bien</label>
                                <input type="text" id="type" name="type" required class="form-control" placeholder="Appartement, Maison, etc.">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="id_pro" class="form-label">Propriétaire</label>
                                <select id="id_pro" name="id_pro" class="form-control" required>
                                    <option value="">Choisir un propriétaire</option>
                                    <?php
                                    $req_proprio = "SELECT id_pro, nom_pro, prenom_pro FROM propritaire";
                                    $res_proprio = mysqli_query($conn, $req_proprio);
                                    while($proprio = mysqli_fetch_assoc($res_proprio)){
                                        echo '<option value="'.$proprio['id_pro'].'">'.$proprio['nom_pro'].' '.$proprio['prenom_pro'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse complète</label>
                            <input type="text" id="adresse" name="adresse" required class="form-control" placeholder="Adresse, Ville, Code postal">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="surface" class="form-label">Surface (m²)</label>
                                <input type="number" id="surface" name="surface" required class="form-control" placeholder="Surface en m²">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="nb_chambre" class="form-label">Nombre de chambres</label>
                                <input type="number" id="nb_chambre" name="nb_chambre" required class="form-control" placeholder="Nombre de chambres">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="prix_location" class="form-label">Prix de location (CFA)</label>
                                <input type="number" id="prix_location" name="prix_location" required class="form-control" placeholder="Prix mensuel">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select id="statut" name="statut" class="form-control" required>
                                    <option value="">Sélectionner un statut</option>
                                    <option value="Vendu">Vendu</option>
                                    <option value="Loué">Loué</option>
                                    <option value="En vente">En vente</option>
                                    <option value="Non loué">Non loué</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_operation" class="form-label">Date d'ajout</label>
                                <input type="date" id="date_operation" name="date_operation" required class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Enregistrer le bien
                            </button>
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
            // Mise au point sur le premier champ du modal quand il s'ouvre
            $('#bienModal').on('shown.bs.modal', function () {
                $('#type').focus();
            });
            
            // Validation du formulaire
            $('#formbien').submit(function(ev) {
                ev.preventDefault();
                
                // Réinitialiser les erreurs
                $('.form-control').removeClass('is-invalid');
                let isValid = true;
                
                // Vérifier les champs obligatoires
                $('#formbien .form-control[required]').each(function() {
                    if ($(this).val() === '') {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    alert('Veuillez remplir tous les champs obligatoires');
                    return;
                }
                
                // Soumettre le formulaire si tout est valide
                this.submit();
            });
            
            // Enlever l'état d'erreur quand l'utilisateur commence à taper
            $('.form-control').on('input', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Fermer automatiquement les alertes après 5 secondes
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
            
            // Personnaliser le sélecteur de date
            $('#date_operation').on('focus', function() {
                this.type = 'date';
            });
        });
    </script>
</body>
</html>