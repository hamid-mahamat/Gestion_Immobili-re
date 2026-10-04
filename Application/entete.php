<?php

session_start();
$conn = mysqli_connect("127.0.0.1", "root", "", "gestion_immo");
$current_page = basename($_SERVER['PHP_SELF']);

if(!isset($_SESSION['id_user'])){
  header("location:connexion.php");
}
$id_user=$_SESSION['id_user'];

$req_user="SELECT * FROM user WHERE id_user='$id_user' ";

$execution_user=mysqli_query($conn,$req_user);
$ligne=mysqli_fetch_array($execution_user);
$nom_user=$ligne['nom_user'];
$prenom_user=$ligne['prenom_user'];
$mot_passe=$ligne['mot_passe'];
$role=$ligne['role'];

function getInitiales($nom_user,$prenom_user) {
    $initiales = '';
    
    if (!empty($nom_user)) $initiales .= mb_substr($nom_user, 0, 1);
    if (!empty($prenom_user)) $initiales .= mb_substr($prenom_user, 0, 1);
    return $initiales;
}

$initiales = getInitiales($nom_user,$prenom_user);


$req_proprietaires = "SELECT COUNT(*) as nb_proprietaires FROM propritaire";
$exec_proprietaires = mysqli_query($conn, $req_proprietaires);
$row_proprietaires = mysqli_fetch_assoc($exec_proprietaires);
$nb_proprietaires = $row_proprietaires['nb_proprietaires'];


$req_locataires = "SELECT COUNT(*) as nb_locataires FROM locataire";
$exec_locataires = mysqli_query($conn, $req_locataires);
$row_locataires = mysqli_fetch_assoc($exec_locataires);
$nb_locataires = $row_locataires['nb_locataires'];


$req_demandeurs = "SELECT COUNT(*) as nb_demandeurs FROM demandeur";
$exec_demandeurs = mysqli_query($conn, $req_demandeurs);
$row_demandeurs = mysqli_fetch_assoc($exec_demandeurs);
$nb_demandeurs = $row_demandeurs['nb_demandeurs'];

// Compter les interventions
$req_interventions = "SELECT COUNT(*) as nb_interventions FROM maintenance";
$exec_interventions = mysqli_query($conn, $req_interventions);
$row_interventions = mysqli_fetch_assoc($exec_interventions);
$nb_interventions = $row_interventions['nb_interventions'];

$req_biens = "SELECT COUNT(*) as nb_biens FROM bien";
$exec_biens = mysqli_query($conn, $req_biens);
$row_biens = mysqli_fetch_assoc($exec_biens);
$nb_biens = $row_biens['nb_biens'];

$req_payement = "SELECT COUNT(*) as nb_payement FROM payement";
$exec_payement = mysqli_query($conn, $req_payement);
$row_payement = mysqli_fetch_assoc($exec_payement);
$nb_payement = $row_payement['nb_payement'];

$req_location = "SELECT COUNT(*) as nb_location FROM location";
$exec_location = mysqli_query($conn, $req_location);
$row_location = mysqli_fetch_assoc($exec_location);
$nb_location = $row_location['nb_location'];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolidImmo | Gestion Immobilière</title>
     <!-- Font Awesome local -->
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    
    <!-- Bootstrap local -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #1abc9c;
            --accent: #e74c3c;
            --light: #f8f9fa;
            --dark: #34495e;
            --gray: #95a5a6;
            --light-gray: #eef2f6;
            --border: #e0e6ed;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --radius: 10px;
            --gradient-primary: linear-gradient(135deg, #3498db 0%, #1abc9c 100%);
            --gradient-secondary: linear-gradient(135deg, #1abc9c 0%, #16a085 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            overflow-x: hidden;
            line-height: 1.6;
        }
        
        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.03);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }
        
        .logo-container {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo-icon {
            font-size: 28px;
            color: var(--secondary);
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 22px;
        }
        
        .logo-text span {
            color: var(--secondary);
        }
        
        .sidebar-section {
            margin-bottom: 25px;
            padding: 0 15px;
        }
        
        .sidebar-title {
            text-transform: uppercase;
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 15px;
            letter-spacing: 1px;
            font-weight: 600;
            padding-left: 10px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-item {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            cursor: pointer;
            color: var(--dark);
            text-decoration: none;
        }
        
        .sidebar-item:hover {
            background-color: rgba(26, 188, 156, 0.08);
            color: var(--secondary);
        }
        
        .sidebar-item.active {
            background-color: rgba(26, 188, 156, 0.15);
            color: var(--secondary);
            font-weight: 500;
        }
        
        .sidebar-icon {
            width: 30px;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-text {
            flex: 1;
            font-size: 15px;
        }
        
        .sidebar-badge {
            background-color: var(--accent);
            color: white;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        
        .page-title h1 {
            font-size: 24px;
            color: var(--primary);
            font-weight: 700;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        
        .page-title p {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
            font-weight: 500;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .notification-bell {
            position: relative;
            font-size: 18px;
            color: var(--dark);
            cursor: pointer;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--accent);
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gradient-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            font-size: 16px;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 15px;
        }
        
        .user-role {
            font-size: 13px;
            color: var(--gray);
        }
        
        /* Navigation */
        .nav-container {
            background-color: white;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            border-radius: var(--radius);
            overflow: hidden;
        }
        
        .nav-menu {
            display: flex;
            flex-wrap: wrap;
        }
        
        .nav-item {
            padding: 12px 18px;
            position: relative;
            font-weight: 500;
            color: var(--dark);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }
        
        .nav-item:hover, .nav-item.active {
            color: var(--secondary);
        }
        
        .nav-item.active {
            background: rgba(26, 188, 156, 0.1);
        }
        
        .nav-icon {
            margin-right: 8px;
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-stats,
            .summary-cards {
                grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .user-info {
                display: none;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .nav-menu {
                overflow-x: auto;
            }
            
            .nav-item {
                padding: 12px 15px;
                font-size: 13px;
            }
        }
        .current-time {
    text-align: right;
    padding-right: 20px;
    color: var(--dark);
    font-size: 14px;
    font-weight: 500;
}

#current-time {
    font-size: 18px;
    font-weight: 600;
    color: var(--secondary);
}
        
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-container">
                <a href="index.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="logo-text">Solid<span>_Immo</span></div>
                </a>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">Gestion des biens</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="index.php" class="sidebar-item <?= $current_page === 'index.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="sidebar-text">Tableau de bord</div>
                        </a>
                    </li>
                    <li>
                        <a href="ajout.php" class="sidebar-item <?= $current_page === 'ajout.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="sidebar-text">Ajouter un bien</div>
                        </a>
                    </li>
                    <li>
                        <a href="ajout.php" class="sidebar-item <?= $current_page === 'biens.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="sidebar-text">Tous les biens</div>
                            <div class="sidebar-badge"><?= $nb_biens ?></div>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">Gestion des clients</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="propritaires.php" class="sidebar-item <?= $current_page === 'propritaires.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="sidebar-text">Propriétaires</div>
                            <div class="sidebar-badge"><?= $nb_proprietaires ?></div>
                        </a>
                    </li>
                    <li>
                        <a href="locataire.php" class="sidebar-item <?= $current_page === 'locataire.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div class="sidebar-text">Locataires</div>
                            <div class="sidebar-badge"><?= $nb_locataires ?></div>
                        </a>
                    </li>
                    <li>
                        <a href="demandeur.php" class="sidebar-item <?= $current_page === 'demandeur.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="sidebar-text">Demandeurs</div>
                            <div class="sidebar-badge"><?= $nb_demandeurs ?></div>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">Contrats</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="vente.php" class="sidebar-item <?= $current_page === 'vente.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="sidebar-text">Ventes</div>
                        </a>
                    </li>
                    <li>
                        <a href="location.php" class="sidebar-item <?= $current_page === 'location.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="sidebar-text">Locations</div>
                            <div class="sidebar-badge"><?= $nb_location ?></div>
                        </a>
                    </li>
                   
                </ul>
            </div>
                <div class="sidebar-section">
                <div class="sidebar-title">Transactions</div>
                <ul class="sidebar-menu">
                   
                    <li>
                        <a href="paiement_achat.php" class="sidebar-item <?= $current_page === 'paiement_achat.php' ? 'active' : '' ?>">
                             <div class="sidebar-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="sidebar-text">Paiements </div>
                            <div class="sidebar-badge"><?= $nb_location ?></div>
                        </a>
                    </li>
                    <li>
                        <a href="payement.php" class="sidebar-item <?= $current_page === 'payement.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="sidebar-text">Paiements Location</div>
                            <div class="sidebar-badge"><?=$nb_payement?></div>
                        </a>
                    </li>
                </ul>
            </div>
            
            
            <div class="sidebar-section">
                <div class="sidebar-title">Réfection</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="maintenance.php" class="sidebar-item <?= $current_page === 'maintenance.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="sidebar-text">Interventions</div>
                            <div class="sidebar-badge"><?= $nb_interventions ?></div>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">Administration</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="user.php" class="sidebar-item <?= $current_page === 'user.php' ? 'active' : '' ?>">
                            <div class="sidebar-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="sidebar-text">Utilisateurs</div>
                        </a>
                    </li>
                    <li>
                        <a href="deconnexion.php" class="sidebar-item">
                            <div class="sidebar-icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <div class="sidebar-text">Déconnexion</div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="page-title">
                    <h1>Tableau de bord</h1>
                </div>
                
                <div class="user-actions">
                    <div class="current-time">
    <div id="current-date"></div>
    <div id="current-time"></div>
    </div>
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo $initiales; ?></div>
<div class="user-info">
    <div class="user-name"><?php echo $nom_user  ?></div>
    <div class="user-role"><?php echo $role; ?></div>
</div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="nav-container">
                <div class="nav-menu">
                    <a href="index.php" class="nav-item <?= $current_page === 'index.php' ? 'active' : '' ?>">
                        <i class="fas fa-home nav-icon"></i>
                        Tableau de bord
                    </a>
                    <a href="ajout.php" class="nav-item <?= $current_page === 'ajout.php' ? 'active' : '' ?>">
                        <i class="fas fa-plus-circle nav-icon"></i>
                        Ajouter un bien
                    </a>
                   
                    <a href="locataire.php" class="nav-item <?= $current_page === 'locataire.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-friends nav-icon"></i>
                        Locataires
                    </a>
                </div>
            </div>
            
            <!-- Contenu spécifique à la page -->
            <div class="page-content">

                <script>
function updateClock() {
    const now = new Date();
    
    // Format de la date : Jour, Date Mois Année
    const optionsDate = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    const dateString = now.toLocaleDateString('fr-FR', optionsDate);
    
    // Format de l'heure : HH:MM:SS
    const timeString = now.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    document.getElementById('current-date').textContent = dateString;
    document.getElementById('current-time').textContent = timeString;
}

// Mise à jour initiale
updateClock();

// Mise à jour chaque seconde
setInterval(updateClock, 1000);
</script>