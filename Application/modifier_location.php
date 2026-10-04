<?php
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: location.php");
    exit();
}

$id_location = mysqli_real_escape_string($conn, $_GET['id']);

// Traitement de la modification
if (isset($_POST['modifier'])) {
    $id_bien = mysqli_real_escape_string($conn, $_POST['id_bien']);
    $id_loca = mysqli_real_escape_string($conn, $_POST['id_loca']);
    $date_debut = mysqli_real_escape_string($conn, $_POST['date_debut']);
    $date_fin = mysqli_real_escape_string($conn, $_POST['date_fin']);
    $loyer_mensuel = mysqli_real_escape_string($conn, $_POST['loyer_mensuel']);
    $caution = mysqli_real_escape_string($conn, $_POST['caution']);
    $date_operation = mysqli_real_escape_string($conn, $_POST['date_operation']);

    $req_update = "UPDATE location SET 
                    id_bien = '$id_bien',
                    id_loca = '$id_loca',
                    date_debut = '$date_debut',
                    date_fin = '$date_fin',
                    loyer_mensuel = '$loyer_mensuel',
                    caution = '$caution',
                    date_operation = '$date_operation'
                   WHERE id_location = '$id_location'";

    if (mysqli_query($conn, $req_update)) {
        $success = "Contrat de location modifié avec succès !";
        // Rafraîchir les données après modification
        $req_location = "SELECT location.*, bien.type, 
                        CONCAT(locataire.nom_loca, ' ', locataire.prenom_loca) AS locataire_complet 
                        FROM location 
                        LEFT JOIN bien ON location.id_bien = bien.id_bien 
                        LEFT JOIN locataire ON location.id_loca = locataire.id_loca
                        WHERE location.id_location = '$id_location'";
        $res_location = mysqli_query($conn, $req_location);
        $location = mysqli_fetch_assoc($res_location);
    } else {
        $error = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}

// Récupérer les informations de la location
$req_location = "SELECT location.*, bien.type, 
                CONCAT(locataire.nom_loca, ' ', locataire.prenom_loca) AS locataire_complet 
                FROM location 
                LEFT JOIN bien ON location.id_bien = bien.id_bien 
                LEFT JOIN locataire ON location.id_loca = locataire.id_loca
                WHERE location.id_location = '$id_location'";

$res_location = mysqli_query($conn, $req_location);

if (!$res_location || mysqli_num_rows($res_location) == 0) {
    header("Location: location.php");
    exit();
}

$location = mysqli_fetch_assoc($res_location);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Location</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            margin-top: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #4caf50, #2e7d32); /* Couleur verte */
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .card-title {
            font-weight: 700;
            margin-bottom: 0;
        }
        .card-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #4caf50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        .back-btn {
            margin-right: 15px;
            background: #6c757d;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .required-label:after {
            content: " *";
            color: red;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .alert {
            border-radius: 10px;
        }
        .input-group-icon {
            position: relative;
        }
        .card-header-icon {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        .form-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .currency-input {
            position: relative;
        }
        .currency-symbol {
            position: absolute;
            right: 15px;
            top: 38px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-contract card-header-icon"></i>Modifier un Contrat de Location
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label class="form-label required-label">Type de bien</label>
                                <select name="id_bien" class="form-control" required>
                                    <option value="">Sélectionnez un bien</option>
                                    <?php
                                    $req_bien = "SELECT id_bien, type FROM bien";
                                    $res_bien = mysqli_query($conn, $req_bien);
                                    while($bien = mysqli_fetch_assoc($res_bien)){
                                        $selected = ($bien['id_bien'] == $location['id_bien']) ? 'selected' : '';
                                        echo '<option value="'.$bien['id_bien'].'" '.$selected.'>'.$bien['type'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Locataire</label>
                                <select name="id_loca" class="form-control" required>
                                    <option value="">Sélectionnez un locataire</option>
                                    <?php
                                    $req_loca = "SELECT id_loca, nom_loca, prenom_loca FROM locataire";
                                    $res_loca = mysqli_query($conn, $req_loca);
                                    while($locataire = mysqli_fetch_assoc($res_loca)){
                                        $selected = ($locataire['id_loca'] == $location['id_loca']) ? 'selected' : '';
                                        $fullname = $locataire['nom_loca'] . ' ' . $locataire['prenom_loca'];
                                        echo '<option value="'.$locataire['id_loca'].'" '.$selected.'>'.htmlspecialchars($fullname).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Date de début</label>
                                    <div class="input-group-icon">
                                        <input type="date" name="date_debut" class="form-control" 
                                               value="<?= htmlspecialchars($location['date_debut']) ?>" required>
                                        <div class="form-icon">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-label">Date de fin</label>
                                    <div class="input-group-icon">
                                        <input type="date" name="date_fin" class="form-control" 
                                               value="<?= htmlspecialchars($location['date_fin']) ?>" required>
                                        <div class="form-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3 currency-input">
                                    <label class="form-label required-label">Loyer mensuel (CFA)</label>
                                    <input type="number" name="loyer_mensuel" class="form-control" 
                                           value="<?= htmlspecialchars($location['loyer_mensuel']) ?>" step="0.01" min="0" required>
                                    <span class="currency-symbol">CFA</span>
                                </div>
                                
                                <div class="col-md-6 mb-3 currency-input">
                                    <label class="form-label required-label">Caution (CFA)</label>
                                    <input type="number" name="caution" class="form-control" 
                                           value="<?= htmlspecialchars($location['caution']) ?>" step="0.01" min="0" required>
                                    <span class="currency-symbol">CFA</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Date de l'opération</label>
                                <div class="input-group-icon">
                                    <input type="date" name="date_operation" class="form-control" 
                                           value="<?= htmlspecialchars($location['date_operation']) ?>" required>
                                    <div class="form-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="location.php" class="btn back-btn">
                                    <i class="fas fa-arrow-left me-2"></i> Annuler
                                </a>
                                <button type="submit" name="modifier" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script pour calculer automatiquement la date de fin (1 an après la date de début)
        document.querySelector('input[name="date_debut"]').addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (startDate && !isNaN(startDate.getTime())) {
                const endDate = new Date(startDate);
                endDate.setFullYear(endDate.getFullYear() + 1);
                
                // Formater la date au format YYYY-MM-DD
                const formattedDate = endDate.toISOString().split('T')[0];
                document.querySelector('input[name="date_fin"]').value = formattedDate;
            }
        });
    </script>
</body>
</html>