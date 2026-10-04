<?php
ob_start(); // Début de la temporisation de sortie
include('entete.php');

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    ob_end_clean(); // Nettoyer le buffer
    header('Location: ajout.php');
    exit;
}

$id_bien = $_GET['id'];

// Récupérer les données du bien
$sql = "SELECT * FROM bien WHERE id_bien = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_bien);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$bien = mysqli_fetch_assoc($res);

if (!$bien) {
    ob_end_clean(); // Nettoyer le buffer
    header('Location: ajout.php');
    exit;
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pro = $_POST['id_pro'];
    $type = $_POST['type'];
    $adresse = $_POST['adresse'];
    $surface = $_POST['surface'];
    $nb_chambre = $_POST['nb_chambre'];
    $prix_location = $_POST['prix_location'];
    $statut = $_POST['statut'];
    $date_operation = $_POST['date_operation'];
    
    // Gestion de l'image
    $imagePath = $bien['image']; // Conserver l'image actuelle par défaut

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Supprimer l'ancienne image uniquement si nouvelle image valide
            if (!empty($bien['image']) && file_exists($bien['image'])) {
                unlink($bien['image']);
            }
            $imagePath = $targetPath;
        }
    }
    
    // Gestion de la suppression d'image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
        if (!empty($bien['image']) && file_exists($bien['image'])) {
            unlink($bien['image']);
        }
        $imagePath = ''; // Réinitialiser le chemin de l'image
    }

    // Mise à jour dans la base
    $sql = "UPDATE bien SET 
            id_pro = ?,
            type = ?,
            adresse = ?,
            surface = ?,
            nb_chambre = ?,
            prix_location = ?,
            statut = ?,
            date_operation = ?,
            image = ?
            WHERE id_bien = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'issiiisssi', 
        $id_pro, $type, $adresse, $surface, $nb_chambre, $prix_location, $statut, $date_operation, $imagePath, $id_bien);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Bien modifié avec succès";
        ob_end_clean(); // Nettoyer le buffer avant redirection
        header('Location: ajout.php');
        exit;
    } else {
        $error_message = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Bien</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
              /* Reprendre le style de ajout.php */
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
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            font-weight: 500;
            border-radius: var(--border-radius);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .img-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 5px;
            margin-top: 10px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <h1 class="text-center">Modifier le Bien Immobilier</h1>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_bien" value="<?php echo $bien['id_bien']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type de bien</label>
                            <input type="text" id="type" name="type" required class="form-control" 
                                   value="<?php echo htmlspecialchars($bien['type']); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="id_pro" class="form-label">Propriétaire</label>
                            <select id="id_pro" name="id_pro" class="form-control" required>
                                <option value="">Choisir un propriétaire</option>
                                <?php
                                $req_proprio = "SELECT id_pro, nom_pro, prenom_pro FROM propritaire";
                                $res_proprio = mysqli_query($conn, $req_proprio);
                                while($proprio = mysqli_fetch_assoc($res_proprio)){
                                    $selected = ($proprio['id_pro'] == $bien['id_pro']) ? 'selected' : '';
                                    echo '<option value="'.$proprio['id_pro'].'" '.$selected.'>'.$proprio['nom_pro'].' '.$proprio['prenom_pro'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse complète</label>
                        <input type="text" id="adresse" name="adresse" required class="form-control" 
                               value="<?php echo htmlspecialchars($bien['adresse']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="surface" class="form-label">Surface (m²)</label>
                            <input type="number" id="surface" name="surface" required class="form-control" 
                                   value="<?php echo $bien['surface']; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="nb_chambre" class="form-label">Nombre de chambres</label>
                            <input type="number" id="nb_chambre" name="nb_chambre" required class="form-control" 
                                   value="<?php echo $bien['nb_chambre']; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="prix_location" class="form-label">Prix de location (€)</label>
                            <input type="number" id="prix_location" name="prix_location" required class="form-control" 
                                   value="<?php echo $bien['prix_location']; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select id="statut" name="statut" class="form-control" required>
                                <option value="Loué" <?php echo ($bien['statut'] == 'Loué') ? 'selected' : ''; ?>>Loué</option>
                                <option value="Non Loué" <?php echo ($bien['statut'] == 'Non Loué') ? 'selected' : ''; ?>>Non Loué</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="date_operation" class="form-label">Date d'ajout</label>
                            <input type="date" id="date_operation" name="date_operation" required class="form-control" 
                                   value="<?php echo $bien['date_operation']; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Image du bien</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        
                        <?php if (!empty($bien['image'])): ?>
                            <p class="mt-2">Image actuelle :</p>
                            <img src="<?php echo $bien['image']; ?>" alt="Image du bien" class="img-preview">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                <label class="form-check-label" for="remove_image">
                                    Supprimer l'image actuelle
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>