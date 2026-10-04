<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "gestion_immo");
$id_pro = $_POST['id_pro'];
$type = $_POST['type'];
$adresse = $_POST['adresse'];
$surface = $_POST['surface'];
$nb_chambre = $_POST['nb_chambre'];
$prix_location = $_POST['prix_location'];
$statut = $_POST['statut'];
$date_operation = $_POST['date_operation'];

$sql = "INSERT INTO bien VALUES(null,'$id_pro', '$type', '$adresse', '$surface', '$nb_chambre', '$prix_location', '$statut', '$date_operation')"; 
if($conn->query($sql) == TRUE){
  echo $type." enregistré avec succés"; 
}else{
  echo "Enregistrement échoué: ".$conn->error; 
}
?>