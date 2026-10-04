<?php
// Démarrage du buffer de sortie
ob_start();
include('entete.php');

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID du bien manquant";
    ob_end_clean(); // Nettoyer le buffer avant redirection
    header("Location: ajout.php");
    exit();
}

$id_bien = intval($_GET['id']); // Utilisation de intval pour plus de sécurité

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    // Récupérer le chemin de l'image
    $sql_img = "SELECT image FROM bien WHERE id_bien = $id_bien";
    $result_img = mysqli_query($conn, $sql_img);
    
    if ($result_img && mysqli_num_rows($result_img) > 0) {
        $row = mysqli_fetch_assoc($result_img);
        $image_path = $row['image'];
    }
    
    // Supprimer le bien de la base de données
    $sql_delete = "DELETE FROM bien WHERE id_bien = $id_bien";
    $result_delete = mysqli_query($conn, $sql_delete);
    
    if ($result_delete && mysqli_affected_rows($conn) > 0) {
        // Supprimer l'image physique si elle existe
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
        
        $_SESSION['success_message'] = "Bien supprimé avec succès!";
        ob_end_clean(); // Nettoyer le buffer avant redirection
        header("Location: ajout.php");
        exit();
    } else {
        $error = "Erreur lors de la suppression: " . mysqli_error($conn);
    }
}

// Récupérer les détails du bien
$sql = "SELECT * FROM bien WHERE id_bien = $id_bien";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Bien introuvable";
    ob_end_clean(); // Nettoyer le buffer avant redirection
    header("Location: ajout.php");
    exit();
}

$bien = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Bien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin-top: 30px;
            border-radius: 15px;
        }
        .card-header {
            background: #ff416c;
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-danger {
            background: #ff416c;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
        }
        .bien-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Supprimer un Bien Immobilier</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <h5>Attention !</h5>
                            <p>Êtes-vous sûr de vouloir supprimer ce bien ? Cette action est irréversible.</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Détails du Bien</h5>
                            <p><strong>ID:</strong> <?= $bien['id_bien'] ?></p>
                            <p><strong>Type:</strong> <?= $bien['type'] ?></p>
                            <p><strong>Adresse:</strong> <?= $bien['adresse'] ?></p>
                            <p><strong>Prix:</strong> <?= $bien['prix_location'] ?> CFA</p>
                            
                            <?php if (!empty($bien['image'])): ?>
                                <div class="mt-3">
                                    <p><strong>Image:</strong></p>
                                    <img src="<?= $bien['image'] ?>" class="bien-image">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="post">
                            <div class="d-flex justify-content-between">
                                <a href="ajout.php" class="btn btn-secondary">
                                    Annuler
                                </a>
                                <button type="submit" name="supprimer" class="btn btn-danger">
                                    Confirmer la suppression
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ob_end_flush(); // Envoyer le contenu du buffer et désactiver la temporisation ?>
</body>
</html>