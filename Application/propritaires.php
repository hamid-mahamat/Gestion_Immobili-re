<?php
include('entete.php');

if (isset($_POST['enregistrer'])) 
{
    $nom_pro = mysqli_escape_string($conn, $_POST['nom_pro']);
    $prenom_pro = mysqli_escape_string($conn, $_POST['prenom_pro']);
    $adresse_pro = mysqli_escape_string($conn, $_POST['adresse_pro']);
    $contact_pro = mysqli_escape_string($conn, $_POST['contact_pro']);
    $date_creation = mysqli_escape_string($conn, $_POST['date_creation']);

   
    $req = "INSERT INTO propritaire (nom_pro,prenom_pro,adresse_pro ,contact_pro,date_creation) 
            VALUES('$nom_pro','$prenom_pro','$adresse_pro','$contact_pro','$date_creation')";

    $execution = mysqli_query($conn,$req);
    if($execution){
        echo '<script type="text/javascript">alert(\'Propriétaire enregistré avec succès !!\');</script>'; 

        // Redirection pour éviter la soumission multiple
        
        echo '<script>setTimeout(function(){ window.location.href = "propritaires.php"; }, 500);</script>';
    } else {
        echo '<script type="text/javascript">alert(\'Erreur lors de l\'enregistrement: '.mysqli_error($conn).'\');</script>'; 
    }
}

// Configuration de la pagination
$limit = 2; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Requête pour le nombre total d'éléments
$count_query = "SELECT COUNT(*) as total FROM propritaire";
$result_count = mysqli_query($conn, $count_query);
$total_row = mysqli_fetch_assoc($result_count);
$total_records = $total_row['total'];

// Calcul du nombre total de pages
$total_pages = ceil($total_records / $limit);

// Requête principale avec pagination
$req_tab = "SELECT * FROM propritaire ORDER BY id_pro DESC LIMIT $limit OFFSET $offset";
$res_tab = mysqli_query($conn, $req_tab);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Propriétaires</title>
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
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        
        .table-container {
            border-radius: var(--border-radius);
            overflow: hidden;
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
            padding: 15px 20px;
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
            white-space: nowrap; /* Empêche le texte de passer à la ligne */
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
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: var(--border-radius);
            background: white;
            box-shadow: var(--card-shadow);
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .search-container {
            max-width: 350px;
        }
        
        .add-btn {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Correction spécifique pour les boutons d'action */
        .actions-cell {
            display: flex;
            flex-wrap: nowrap; /* Empêche le retour à la ligne */
            gap: 5px; /* Espacement entre les boutons */
        }
        
        /* Styles pour la recherche et filtre */
        .filter-label {
            background-color: #e9ecef;
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        
        .highlight {
            background-color: #ffff00 !important;
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
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Propriétaires</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-light add-btn" data-bs-toggle="modal" data-bs-target="#ownerModal">
                        <i class="fas fa-plus-circle"></i> Ajouter un propriétaire
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des propriétaires</h5>
                    <div class="d-flex">
                    
                        <div class="dropdown">
                      
                           <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                             
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-option" data-column="1">Nom</a></li>
                                <li><a class="dropdown-item filter-option" data-column="2">Prénom</a></li>
                                <li><a class="dropdown-item filter-option" data-column="3">Adresse</a></li>
                                <li><a class="dropdown-item filter-option" data-column="4">Contact</a></li>
                                <li><a class="dropdown-item filter-option" data-column="5">Date</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Adresse</th>
                                <th>Téléphone</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php 
                            while ($row = mysqli_fetch_array($res_tab)): 
                            ?>
                                <tr>
                                    <td class="table-cell">Prop-<?=sprintf('%04d', htmlspecialchars($row['id_pro']))?></td>
                                    <td><?php echo htmlspecialchars($row['nom_pro']); ?></td>
                                    <td><?php echo htmlspecialchars($row['prenom_pro']); ?></td>
                                    <td><?php echo htmlspecialchars($row['adresse_pro']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_pro']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['date_creation'])); ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="modifier_propritaire.php?id=<?php echo htmlspecialchars($row['id_pro']); ?>" 
                                               class="btn btn-sm action-btn btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i> 
                                            </a>
                                            <a href="supprimer_propritaire.php?id=<?php echo htmlspecialchars($row['id_pro']); ?>" 
                                               class="btn btn-sm action-btn btn-delete" title="Supprimer"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce propriétaire ?');">
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
                        sur <?php echo $total_records; ?> propriétaires
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

    <!-- Modal d'ajout de propriétaire -->
    <div class="modal fade" id="ownerModal" tabindex="-1" aria-labelledby="ownerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ownerModalLabel">Ajouter un propriétaire</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="propritaires.php">
                        <div class="mb-3">
                            <label for="nom_pro" class="form-label">Nom</label>
                            <input type="text" name="nom_pro" id="nom_pro" required class="form-control" placeholder="Entrez le nom">
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom_pro" class="form-label">Prénom</label>
                            <input type="text" name="prenom_pro" id="prenom_pro" required class="form-control" placeholder="Entrez le prénom">
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse_pro" class="form-label">Adresse</label>
                            <input type="text" name="adresse_pro" id="adresse_pro" required class="form-control" placeholder="Entrez l'adresse">
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_pro" class="form-label">Contact</label>
                            <input type="text" name="contact_pro" id="contact_pro" required class="form-control" placeholder="Entrez le contact">
                        </div>

                        <div class="mb-3">
                            <label for="date_creation" class="form-label">Date d'inscription</label>
                            <input type="date" name="date_creation" id="date_creation" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
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
        // Initialisation des tooltips
        $(document).ready(function(){
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Focus sur le premier champ du modal quand il s'ouvre
            $('#ownerModal').on('shown.bs.modal', function () {
                $('#nom_pro').focus();
            });
            
            // Définir la date du jour par défaut
            document.getElementById('date_creation').valueAsDate = new Date();
        });
    </script>
</body>
</html>