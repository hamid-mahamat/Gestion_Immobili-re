<?php
include('connexion.php');

if(isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    
    $req = "SELECT b.id_bien, b.type, b.superficie, b.quartier, b.prix, b.statut, 
                   p.nom AS nom_proprietaire, p.prenom AS prenom_proprietaire
            FROM bien b
            JOIN proprietaire p ON b.id_proprietaire = p.id_proprietaire
            WHERE b.statut = 'Disponible' 
            AND (b.type LIKE '%$search%' 
                 OR b.adresse LIKE '%$search%' 
                 OR b.quartier LIKE '%$search%'
                 OR CONCAT(p.nom, ' ', p.prenom) LIKE '%$search%')";
    
    $res = mysqli_query($conn, $req);
    
    if($res && mysqli_num_rows($res) > 0) {
        while($bien = mysqli_fetch_assoc($res)) {
            // Formatage du prix
            $prix = number_format($bien['prix'], 0, ',', ' ');
            $proprietaire = $bien['nom_proprietaire'] . ' ' . $bien['prenom_proprietaire'];
            
            echo '<div class="bien-card card mb-3" 
                    data-id="'.$bien['id_bien'].'"
                    data-type="'.$bien['type'].'"
                    data-superficie="'.$bien['superficie'].'"
                    data-quartier="'.$bien['quartier'].'"
                    data-prix="'.$prix.'"
                    data-statut="'.$bien['statut'].'"
                    data-proprietaire="'.$proprietaire.'">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <div class="placeholder-img" style="background-color: #eee; height: 120px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-home fa-3x text-muted"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="bien-title">'.$bien['type'].'</h5>
                                <p class="bien-detail"><i class="fas fa-map-marker-alt me-2"></i>'.$bien['quartier'].'</p>
                                <p class="bien-detail"><i class="fas fa-ruler-combined me-2"></i>'.$bien['superficie'].' m²</p>
                                <p class="bien-price">'.$prix.' FCFA</p>
                                <small class="text-muted">Propriétaire: '.$proprietaire.'</small>
                            </div>
                        </div>
                    </div>
                </div>';
        }
    } else {
        echo '<div class="no-results">
                <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                <p>Aucun bien disponible ne correspond à votre recherche</p>
              </div>';
    }
} else {
    // Ne rien afficher quand le terme de recherche est vide
    // Le message initial est géré par la page principale
    echo '';
}