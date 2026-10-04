<?php include('entete.php'); ?>

<?php
if (isset($_POST['enregistrer'])) {
    $id_bien = mysqli_real_escape_string($conn, $_POST['id_bien']); 
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_demande = mysqli_real_escape_string($conn, $_POST['date_demande']);
    $cout = mysqli_real_escape_string($conn, $_POST['cout']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);
    $statut = mysqli_real_escape_string($conn, $_POST['statut']);

    $req = "INSERT INTO maintenance (id_bien, description, date_demande, cout, date_operation, statut) 
            VALUES('$id_bien','$description','$date_demande','$cout','$date_operation','$statut')";

    $execution = mysqli_query($conn, $req);
    if($execution){
        echo '<script type="text/javascript">alert(\'Maintenance enregistrée avec succès !!\');</script>'; 
    }
}

// Configuration de la pagination
$limit = 3; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Requête pour compter le nombre total d'éléments
$count_query = "SELECT COUNT(*) as total FROM maintenance 
                JOIN bien ON maintenance.id_bien = bien.id_bien
                JOIN propritaire ON bien.id_pro = propritaire.id_pro";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Requête principale avec pagination
$req_tab = "SELECT maintenance.*, bien.type, 
           propritaire.nom_pro, propritaire.prenom_pro
    FROM maintenance 
    JOIN bien ON maintenance.id_bien = bien.id_bien
    JOIN propritaire ON bien.id_pro = propritaire.id_pro
    ORDER BY maintenance.id_main DESC
    LIMIT $limit OFFSET $offset";
$res_tab = mysqli_query($conn, $req_tab);
?>

<!-- Font Awesome local -->
<link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

<!-- Bootstrap local -->
<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">

<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --warning-color: #ffc107;
        --light-bg: #f8f9fc;
        --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
        --border-radius: 8px;
        --dark-color: #1e293b;
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
    
    .btn-warning {
        background-color: var(--warning-color);
        border-color: var(--warning-color);
        padding: 8px 20px;
        font-weight: 500;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);
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
    
    .modal-header {
        background: linear-gradient(120deg, #4361ee, #3a0ca3);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
    
    .add-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    
    .info-card {
        border-left: 4px solid var(--primary-color);
        background-color: white;
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        box-shadow: var(--card-shadow);
    }
    
    .section-title {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(67, 97, 238, 0.1);
    }
    
    .stat-badge {
        font-size: 0.85rem;
        padding: 5px 10px;
        border-radius: 20px;
        background-color: #e9ecef;
        color: #495057;
        font-weight: 500;
    }
    
    .table-cell {
        white-space: nowrap;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-terminé {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .status-en-cour {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-annuler {
        background-color: #fee2e2;
        color: #b91c1c;
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
        padding: 8px 15px;
        border-radius: var(--border-radius);
        transition: all 0.3s;
    }
    
    .page-link:hover {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #f8f9fa;
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
        
        .table-cell {
            max-width: 100px;
        }
        
        .pagination-container {
            flex-direction: column;
            align-items: center;
        }
        
        .pagination-info {
            text-align: center;
        }
    }
</style>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Maintenance</h1>
            </div>
            <div class="col-md-6 text-md-end">
                <button class="btn btn-light add-btn" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                    <i class="fas fa-plus-circle me-2"></i> Ajouter une maintenance
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="card-header-content">
                <h5 class="mb-0">Liste des maintenances</h5>
                <div class="card-toolbar">
                    <!-- Vous pouvez ajouter des éléments de recherche ou filtres ici -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type de bien</th>
                            <th>Propriétaire</th>
                            <th>Description</th>
                            <th>Date demande</th>
                            <th>Coût</th>
                            <th>Date opération</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($res_tab) > 0):
                            while ($row = mysqli_fetch_array($res_tab)):
                                $status_class = '';
                                if ($row['statut'] == 'Terminé') $status_class = 'status-terminé';
                                elseif ($row['statut'] == 'en cour') $status_class = 'status-en-cour';
                                elseif ($row['statut'] == 'annuler') $status_class = 'status-annuler';
                        ?>
                            <tr>
                                <td class="table-cell">Main-<?=sprintf('%04d', htmlspecialchars($row['id_main']))?></td>
                                <td class="table-cell"><?= htmlspecialchars($row['type']) ?></td>
                                
                                <td class="table-cell">
                                    <?= htmlspecialchars($row['nom_pro'] . ' ' . $row['prenom_pro']) ?>
                                </td>
                                <td class="table-cell"><?= htmlspecialchars($row['description']) ?></td>
                                <td class="table-cell"><?= date('d/m/Y', strtotime($row['date_demande'])) ?></td>
                                <td class="table-cell"><?= number_format($row['cout'], 0, ',', ' ') ?> CFA</td>
                                <td class="table-cell"><?= date('d/m/Y', strtotime($row['date_operation'])) ?></td>
                                <td class="table-cell">
                                    <span class="status-badge <?= $status_class ?>"><?= $row['statut'] ?></span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="modifier_maintenance.php?id=<?= htmlspecialchars($row['id_main']) ?>" 
                                           class="btn btn-sm action-btn btn-edit">
                                            <i class="fas fa-edit me-1"></i>
                                        </a>
                                        <a href="supprimer_maintenance.php?id=<?= htmlspecialchars($row['id_main']) ?>" 
                                           class="btn btn-sm action-btn btn-delete"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette maintenance ?');">
                                            <i class="fas fa-trash-alt me-1"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3"></i>
                                        <p>Aucune maintenance trouvée</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Affichage de <?= min(($offset + 1), $total_rows) ?> à <?= min(($offset + $limit), $total_rows) ?> sur <?= $total_rows ?> entrées
                </div>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Bouton Précédent -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Précédent">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Pages -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        // Afficher la première page si nécessaire
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        // Afficher les pages autour de la page courante
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                        }
                        
                        // Afficher la dernière page si nécessaire
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                        }
                        ?>
                        
                        <!-- Bouton Suivant -->
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Suivant">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal d'ajout de maintenance -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maintenanceModalLabel">Ajouter une maintenance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="maintenance.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type de bien *</label>
                            <select name="id_bien" class="form-select" required>
                                <option value="">Choisir le type du bien</option>
                                <?php
                                $req_bien = "SELECT id_bien, type FROM bien";
                                $res_bien = mysqli_query($conn, $req_bien);
                                while($bien = mysqli_fetch_assoc($res_bien)){
                                    echo '<option value="'.$bien['id_bien'].'">'.$bien['type'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statut *</label>
                            <select name="statut" class="form-select" required>
                                <option value="">Sélectionner un statut</option>
                                <option value="Terminé">Terminé</option>
                                <option value="en cour">En cours</option>
                                <option value="annuler">Annulé</option>
                            </select>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="3" required placeholder="Décrivez la maintenance nécessaire"></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date de demande *</label>
                            <input type="date" name="date_demande" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date d'opération *</label>
                            <input type="date" name="date_operation" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coût (CFA) *</label>
                            <div class="input-group">
                                <span class="input-group-text">CFA</span>
                                <input type="number" name="cout" class="form-control" required placeholder="Montant" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="enregistrer" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
<script>
    $(document).ready(function(){
        $('input[name="cout"]').on('blur', function() {
            const value = $(this).val();
            if (value) {
                $(this).val(parseFloat(value).toLocaleString('fr-FR'));
            }
        });
        
        $('input[type="date"]').on('change', function() {
            const today = new Date().toISOString().split('T')[0];
            if ($(this).val() < today && $(this).attr('name') === 'date_operation') {
                alert('La date d\'opération ne peut pas être dans le passé');
                $(this).val(today);
            }
        });
    });
</script>

</div> <!-- Fermeture de page-content -->
</div> <!-- Fermeture de main-content -->
</div> <!-- Fermeture de dashboard-container -->

<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>

</body>
</html>