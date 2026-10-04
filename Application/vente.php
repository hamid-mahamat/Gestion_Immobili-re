<?php

$conn = mysqli_connect("127.0.0.1", "root", "", "gestion_immo");

// Récupérer les types distincts de biens

$types_bien = [];
$req_types = "SELECT DISTINCT type FROM bien";
$res_types = mysqli_query($conn, $req_types);
if ($res_types && mysqli_num_rows($res_types) > 0) {
    while ($row = mysqli_fetch_assoc($res_types)) {
        $types_bien[] = $row['type'];
    }
}

// Traitement de l'enregistrement de la vente

if (isset($_POST['enregistrer_vente'])) {
    $id_bien = mysqli_real_escape_string($conn, $_POST['id_bien']);
    $nom_acheteur = mysqli_real_escape_string($conn, $_POST['nom_acheteur']);
    $prenom_acheteur = mysqli_real_escape_string($conn, $_POST['prenom_acheteur']);
    $prix_vente = mysqli_real_escape_string($conn, $_POST['prix_vente']);
    $contact_acheteur = mysqli_real_escape_string($conn, $_POST['contact_acheteur']);
    $adresse_acheteur = mysqli_real_escape_string($conn, $_POST['adresse_acheteur']);
    $date_operation = date('Y-m-d H:i:s');

    // Enregistrement de la vente
    $req = "INSERT INTO ventes 
            (id_bien, nom_acheteur, prenom_acheteur, prix_vente, date_operation, contact_acheteur, adresse_acheteur) 
            VALUES('$id_bien', '$nom_acheteur', '$prenom_acheteur', '$prix_vente', '$date_operation', '$contact_acheteur', '$adresse_acheteur')";
    
    $execution = mysqli_query($conn, $req);
    
    if($execution) {
        $id_vente = mysqli_insert_id($conn);
        
        // Mettre à jour le statut du bien
        $update_req = "UPDATE bien SET statut = 'Vendu' WHERE id_bien = $id_bien";
        mysqli_query($conn, $update_req);
        
        // Redirection vers la page de paiement
        header("Location: paiement_achat.php?id_vente=$id_vente");
        exit();
    } else {
        echo '<script>alert("Erreur lors de l\'enregistrement de la vente");</script>';
    }
}

include('entete.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Ventes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #06d6a0;
            --light-bg: #f8f9fc;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
            --border-radius: 8px;
            --dark-color: #1e293b;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            color: #333;
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
        
        .info-card {
            border-left: 4px solid var(--primary-color);
            background-color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
        }
        
        .info-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .info-card p {
            margin: 0;
            font-size: 0.95rem;
        }
        
        .section-title {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }
        
        .property-card {
            border: 1px solid #eaeaea;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            background-color: white;
        }
        
        .property-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-color: var(--primary-color);
        }
        
        .property-card h6 {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .property-card .badge {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .alert-container {
            margin: 20px 0;
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
        
        .contact-cell {
            direction: ltr;
            text-align: right;
        }
        
       
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
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Gestion des Ventes</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i> Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
               
            </div>
            <div class="col-md-3 mb-3">
               
            </div>
            <div class="col-md-3 mb-3">
               
            </div>
            
        </div>

        <!-- Formulaire de recherche de bien -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h5 class="mb-0">Rechercher un bien à vendre</h5>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="vente.php">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Type de bien</label>
                                <select name="type" class="form-select">
                                    <option value="">Tous les types</option>
                                    <?php foreach($types_bien as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>" 
                                            <?= (isset($_GET['type']) && $_GET['type'] == $type) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="adresse" class="form-control" placeholder="Entrez une adresse"
                                       value="<?= isset($_GET['adresse']) ? htmlspecialchars($_GET['adresse']) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Prix maximum</label>
                                <input type="number" name="prix_max" class="form-control" placeholder="Prix maximum"
                                       value="<?= isset($_GET['prix_max']) ? htmlspecialchars($_GET['prix_max']) : '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" name="rechercher" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Rechercher
                        </button>
                        <a href="vente.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt me-2"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Résultats de recherche -->
        <?php if (isset($_GET['rechercher'])) : ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-header-content">
                        <h5 class="mb-0">Résultats de la recherche</h5>
                        <div class="card-toolbar">
                            <div class="input-group search-container">
                                <input type="text" class="form-control" placeholder="Filtrer les résultats...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Construction dynamique de la requête
                    $conditions = [];
                    $params = [];
                    $types = "";
                    
                    // Filtre par type
                    if (!empty($_GET['type'])) {
                        $conditions[] = "b.type = ?";
                        $params[] = $_GET['type'];
                        $types .= "s";
                    }
                    
                    // Filtre par adresse
                    if (!empty($_GET['adresse'])) {
                        $conditions[] = "b.adresse LIKE ?";
                        $params[] = '%' . $_GET['adresse'] . '%';
                        $types .= "s";
                    }
                    
                    // Filtre par prix maximum
                    if (!empty($_GET['prix_max'])) {
                        $conditions[] = "b.prix_location <= ?";
                        $params[] = $_GET['prix_max'];
                        $types .= "i";
                    }
                    
                    // Requête avec jointure entre bien et ventes
                    $sql = "SELECT b.* 
                            FROM bien b
                            LEFT JOIN ventes v ON b.id_bien = v.id_bien
                            WHERE v.id_vente IS NULL"; // Biens non vendus
                    
                    // Ajout des conditions supplémentaires
                    if (!empty($conditions)) {
                        $sql .= " AND " . implode(" AND ", $conditions);
                    }
                    
                    $sql .= " ORDER BY b.id_bien DESC";
                    
                    $stmt = mysqli_prepare($conn, $sql);
                    
                    if ($stmt) {
                        // Liaison des paramètres si nécessaire
                        if (!empty($params)) {
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                        }
                        
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        if (mysqli_num_rows($result) > 0) : ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Adresse</th>
                                            <th>Surface</th>
                                            <th>Chambres</th>
                                            <th>Prix</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($bien = mysqli_fetch_assoc($result)) : ?>
                                            <tr>
                                                <td class="table-cell"><?= $bien['id_bien'] ?></td>
                                                <td class="table-cell"><?= htmlspecialchars($bien['type']) ?></td>
                                                <td class="table-cell"><?= htmlspecialchars($bien['adresse']) ?></td>
                                                <td class="table-cell"><?= $bien['surface'] ?> m²</td>
                                                <td class="table-cell"><?= $bien['nb_chambre'] ?></td>
                                                <td class="table-cell"><?= number_format($bien['prix_location'], 0, ',', ' ') ?> CFA</td>
                                                <td class="table-cell">
                                                    <span class="badge bg-success">Disponible</span>
                                                </td>
                                                <td>
                                                    <a href="vente.php?id_bien=<?= $bien['id_bien'] ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-shopping-cart me-1"></i> Vendre
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-warning text-center py-4">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <h5>Aucun bien disponible trouvé</h5>
                                <p class="mb-0">Essayez d'élargir vos critères de recherche</p>
                                <a href="vente.php" class="btn btn-outline-primary mt-3">
                                    <i class="fas fa-redo me-2"></i>Réinitialiser la recherche
                                </a>
                            </div>
                        <?php endif;
                        
                        mysqli_stmt_close($stmt);
                    } else {
                        echo "<div class='alert alert-danger'>Erreur dans la requête: " . mysqli_error($conn) . "</div>";
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire de vente -->
        <?php if (isset($_GET['id_bien'])) : 
            $id_bien = intval($_GET['id_bien']);
            
            // Vérifier que le bien n'a pas déjà été vendu
            $req_bien = "SELECT b.* 
                        FROM bien b
                        LEFT JOIN ventes v ON b.id_bien = v.id_bien
                        WHERE b.id_bien = $id_bien 
                        AND v.id_vente IS NULL";
            
            $res_bien = mysqli_query($conn, $req_bien);
            
            if (mysqli_num_rows($res_bien) > 0) {
                $bien = mysqli_fetch_assoc($res_bien);
            ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Formulaire de vente</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="vente.php">
                            <input type="hidden" name="id_bien" value="<?= $bien['id_bien'] ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-home me-2"></i>Informations du bien</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Type</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($bien['type']) ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Adresse</label>
                                                <textarea class="form-control" readonly rows="2"><?= htmlspecialchars($bien['adresse']) ?></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Surface</label>
                                                    <input type="text" class="form-control" value="<?= $bien['surface'] ?> m²" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Chambres</label>
                                                    <input type="text" class="form-control" value="<?= $bien['nb_chambre'] ?>" readonly>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informations de l'acheteur</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                                    <input type="text" name="nom_acheteur" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                                    <input type="text" name="prenom_acheteur" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                                                <input type="tel" name="contact_acheteur" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Adresse <span class="text-danger">*</span></label>
                                                <textarea name="adresse_acheteur" class="form-control" rows="2" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Prix de vente (CFA) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">CFA</span>
                                                    <input type="number" name="prix_vente" class="form-control" value="<?= $bien['prix_location'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date de vente <span class="text-danger">*</span></label>
                                                <input type="date" name="date_vente" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="submit" name="enregistrer_vente" class="btn btn-success btn-lg px-4">
                                    <i class="fas fa-check-circle me-2"></i>Enregistrer la vente
                                </button>
                                <a href="vente.php" class="btn btn-outline-secondary btn-lg px-4">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger text-center py-4">
                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                    <h5>Ce bien n'est plus disponible à la vente</h5>
                    <p class="mb-0">Il a peut-être déjà été vendu ou retiré du marché</p>
                    <a href="vente.php" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-redo me-2"></i>Retour à la liste
                    </a>
                </div>
            <?php } ?>
        <?php endif; ?>
        

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function(){
            // Formatage des numéros de téléphone
            $('input[type="tel"]').on('input', function() {
                this.value = this.value.replace(/[^0-9+]/g, '');
            });
            
            // Formatage des montants
            $('input[name="prix_vente"]').on('blur', function() {
                const value = $(this).val();
                if (value) {
                    $(this).val(parseFloat(value).toLocaleString('fr-FR'));
                }
            });
            
            // Initialisation des tooltips
            $('[title]').tooltip();
        });
    </script>
</body>
</html>