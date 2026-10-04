<?php
include('entete.php');

if (isset($_POST['enregistrer'])) {
    $nom_user = mysqli_real_escape_string($conn, $_POST['nom_user']);
    $prenom_user = mysqli_real_escape_string($conn, $_POST['prenom_user']);
    $mot_passe = mysqli_real_escape_string($conn, $_POST['mot_passe']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $req = "INSERT INTO user(nom_user, prenom_user, mot_passe, role) 
            VALUES('$nom_user','$prenom_user','$mot_passe','$role')";
    
    $execution = mysqli_query($conn, $req);
    if($execution){
        echo '<script type="text/javascript">alert(\'Utilisateur enregistré avec succès !!\');</script>'; 
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs</title>
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
            --dark-color: #1e293b;
            --success-color: #06d6a0;
            --warning-color: #ffc107;
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
        
        .role-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .role-admin {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .role-user {
            background-color: #dcfce7;
            color: #166534;
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
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Utilisateurs</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <!-- Modification ici : Remplacement du bouton "Retour" par "Ajouter un utilisateur" -->
                    <button class="btn btn-light add-btn" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="fas fa-plus-circle me-2"></i> Ajouter un utilisateur
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Carte principale avec tableau -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h5 class="mb-0">Liste des utilisateurs</h5>
                    
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Rôle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $req_tab = "SELECT * FROM user";
                            $res_tab = mysqli_query($conn, $req_tab);
                            while ($row = mysqli_fetch_array($res_tab)):
                                // Déterminer la classe CSS en fonction du rôle
                                $role_class = ($row['role'] == 'Admin') ? 'role-admin' : 'role-user';
                            ?>
                                <tr>
                                    <td class="table-cell"><?= $row['id_user'] ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['nom_user']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['prenom_user']) ?></td>
                                    <td class="table-cell">
                                        <span class="role-badge <?= $role_class ?>"><?= $row['role'] ?></span>
                                    </td>
                                  
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="#" 
                                               class="btn btn-sm action-btn btn-edit">
                                                <i class="fas fa-edit me-1"></i>
                                            </a>
                                            <a href="#" 
                                               class="btn btn-sm action-btn btn-delete"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <i class="fas fa-trash-alt me-1"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
               
            </div>
        </div>
    </div>

    <!-- Modal d'ajout d'utilisateur -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="user.php">
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom_user" class="form-control" required placeholder="Entrez le nom">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="prenom_user" class="form-control" required placeholder="Entrez le prénom">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mot de passe *</label>
                            <div class="input-group">
                                <input type="password" name="mot_passe" id="passwordInput" class="form-control" required placeholder="Créer un mot de passe">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Minimum 8 caractères avec chiffres et lettres</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Rôle *</label>
                            <select name="role" class="form-select" required>
                                <option value="">Sélectionner un rôle</option>
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
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

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function(){
            // Fonction pour afficher/masquer le mot de passe
            $('#togglePassword').click(function(){
                const passwordInput = $('#passwordInput');
                const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                passwordInput.attr('type', type);
                
                // Changer l'icône
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });
            
            // Validation du mot de passe
            $('form').on('submit', function(e) {
                const password = $('#passwordInput').val();
                if (password.length < 8) {
                    alert('Le mot de passe doit contenir au moins 8 caractères');
                    e.preventDefault();
                    return false;
                }
                
                if (!/\d/.test(password) || !/[a-zA-Z]/.test(password)) {
                    alert('Le mot de passe doit contenir des lettres et des chiffres');
                    e.preventDefault();
                    return false;
                }
            });
            
            // Initialisation des tooltips
            $('[title]').tooltip();
        });
    </script>
    <script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>