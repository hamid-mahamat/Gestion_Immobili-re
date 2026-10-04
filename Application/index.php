<?php
// index.php
include('entete.php');
$req_user="SELECT * FROM user WHERE id_user='$id_user' ";

$execution_user=mysqli_query($conn,$req_user);
$ligne=mysqli_fetch_array($execution_user);
$nom_user=$ligne['nom_user'];
$prenom_user=$ligne['prenom_user'];
$mot_passe=$ligne['mot_passe'];
$role=$ligne['role'];

// Compter les biens gérés
$req_biens = "SELECT COUNT(*) as nb_biens FROM bien";
$exec_biens = mysqli_query($conn, $req_biens);
$row_biens = mysqli_fetch_assoc($exec_biens);
$nb_biens = $row_biens['nb_biens'];

// Compter les locations actives (contrats en cours)
$req_locations_actives = "SELECT COUNT(*) as nb_locations_actives 
                          FROM location 
                          WHERE date_debut <= CURDATE() 
                          AND date_fin >= CURDATE()";
$exec_locations_actives = mysqli_query($conn, $req_locations_actives);
$row_locations_actives = mysqli_fetch_assoc($exec_locations_actives);
$nb_locations_actives = $row_locations_actives['nb_locations_actives'];

// Compter les ventes du mois (du mois en cours)
$req_ventes_mois = "SELECT COUNT(*) as nb_ventes_mois 
                    FROM ventes 
                    WHERE MONTH(date_operation) = MONTH(CURDATE()) 
                    AND YEAR(date_operation) = YEAR(CURDATE())";
$exec_ventes_mois = mysqli_query($conn, $req_ventes_mois);
$row_ventes_mois = mysqli_fetch_assoc($exec_ventes_mois);
$nb_ventes_mois = $row_ventes_mois['nb_ventes_mois'];

// Compter les maintenances en cours (interventions non terminées)
$req_maintenance_cours = "SELECT COUNT(*) as nb_maintenance_cours 
                          FROM maintenance 
                          WHERE statut != 'Terminé'";
$exec_maintenance_cours = mysqli_query($conn, $req_maintenance_cours);
$row_maintenance_cours = mysqli_fetch_assoc($exec_maintenance_cours);
$nb_maintenance_cours = $row_maintenance_cours['nb_maintenance_cours'];
?>

<!-- Welcome Banner -->

<div class="welcome-banner" style="background: var(--gradient-primary);
    border-radius: var(--radius);
    padding: 25px 30px;
    color: white;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;">
    
    <div class="welcome-text" style="position: relative; z-index: 2;">
        <h1 style="font-size: 26px; margin-bottom: 8px; font-weight: 700;">Bienvenue, <?php echo $nom_user . ' ' . $prenom_user; ?></h1>
        <p style="opacity: 0.9; max-width: 600px; font-size: 15px;">Gérez efficacement votre portefeuille immobilier avec notre solution tout-en-un</p>
    </div>
    
    <div class="welcome-icon" style="font-size: 50px; opacity: 0.2; position: absolute; right: 30px; top: 50%; transform: translateY(-50%); z-index: 1;">
        <i class="fas fa-home"></i>
    </div>
    
    <div style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); z-index: 2;">
        <i class="fas fa-home" style="font-size: 40px; opacity: 0.8;"></i>
    </div>
</div>
<!-- Stats Overview -->
<div class="dashboard-stats" style="display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;">
    
    <div class="stat-card" style="background: white;
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        border-left: 4px solid #3498db;">
        <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 15px; flex-shrink: 0; background-color: rgba(52, 152, 219, 0.15); color: #3498db;">
            <i class="fas fa-home"></i>
        </div>
        <div class="stat-info" style="flex: 1;">
            <div class="stat-value" style="font-size: 24px; font-weight: 700; margin-bottom: 2px;"><?= $nb_biens ?></div>
            <div class="stat-title" style="color: var(--gray); font-size: 14px;">Biens gérés</div>
        </div>
    </div>
    
    <div class="stat-card" style="background: white;
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        border-left: 4px solid #2ecc71;">
        <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 15px; flex-shrink: 0; background-color: rgba(46, 204, 113, 0.15); color: #2ecc71;">
            <i class="fas fa-key"></i>
        </div>
        <div class="stat-info" style="flex: 1;">
            <div class="stat-value" style="font-size: 24px; font-weight: 700; margin-bottom: 2px;"><?= $nb_locations_actives ?></div>
            <div class="stat-title" style="color: var(--gray); font-size: 14px;">Locations actives</div>
        </div>
    </div>
    
    <div class="stat-card" style="background: white;
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        border-left: 4px solid #9b59b6;">
        <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 15px; flex-shrink: 0; background-color: rgba(155, 89, 182, 0.15); color: #9b59b6;">
            <i class="fas fa-handshake"></i>
        </div>
        <div class="stat-info" style="flex: 1;">
            <div class="stat-value" style="font-size: 24px; font-weight: 700; margin-bottom: 2px;"><?= $nb_ventes_mois ?></div>
            <div class="stat-title" style="color: var(--gray); font-size: 14px;">Ventes ce mois</div>
        </div>
    </div>
    
    <div class="stat-card" style="background: white;
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        border-left: 4px solid #e74c3c;">
        <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 15px; flex-shrink: 0; background-color: rgba(231, 76, 60, 0.15); color: #e74c3c;">
            <i class="fas fa-tools"></i>
        </div>
        <div class="stat-info" style="flex: 1;">
            <div class="stat-value" style="font-size: 24px; font-weight: 700; margin-bottom: 2px;"><?= $nb_maintenance_cours ?></div>
            <div class="stat-title" style="color: var(--gray); font-size: 14px;">Réfection en cours</div>
        </div>
    </div>
</div>
<!-- Summary Cards -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation pour les cartes
        const cards = document.querySelectorAll('.stat-card, .summary-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
        
        // Animation pour le titre
        const title = document.querySelector('.page-title h1');
        title.style.opacity = '0';
        title.style.transform = 'translateY(10px)';
        title.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            title.style.opacity = '1';
            title.style.transform = 'translateY(0)';
        }, 300);
    });
</script>

</div> <!-- Fin de .page-content -->
</div> <!-- Fin de .main-content -->
</div> <!-- Fin de .dashboard-container -->

<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>

<script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
<script type="text/javascript" src="assets/js/modernizr/modernizr.js"></script>
<script src="assets/pages/widget/amchart/amcharts.min.js"></script>
<script src="assets/pages/widget/amchart/serial.min.js"></script>
<script type="text/javascript" src="assets/js/chart.js/Chart.js"></script>
<script type="text/javascript" src="assets/pages/dashboard/custom-dashboard.min.js"></script>
<script type="text/javascript" src="assets/js/script.js"></script>
<script src="assets/js/pcoded.min.js"></script>
<script src="assets/js/vartical-demo.js"></script>
<script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
</body>
</html>