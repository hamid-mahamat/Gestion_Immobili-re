<?php
session_start(); // Point-virgule ajouté
$conn = mysqli_connect("127.0.0.1", "root", "", "gestion_stock");
if(!isset($_SESSION['id'])){
    header("location:connexion.php"); // Correction de "hearder" en "header"
}
$id_user = $_SESSION['id'];

$req_user = "SELECT * FROM utilisateurs WHERE id='$id_user'";
$execution_user = mysqli_query($conn, $req_user);
$ligne = mysqli_fetch_array($execution_user);
$nom_user = $ligne['nom'];
$id_user = $ligne['id'];

$output = '';

if(isset($_POST['query'])){
    $search = mysqli_real_escape_string($conn, $_POST["query"]);
    $query = "SELECT * FROM produit WHERE produit LIKE '%".$search."%'";
}
else
{
    $query = "SELECT * FROM produit WHERE id='a'";
}

$result = mysqli_query($conn, $query);
if(mysqli_num_rows($result) > 0)
{
    $output .= '<div class="row">';
    while($row = mysqli_fetch_array($result)){
       
        $output .= '
        <div class="col-md-3">
        <form method="post" action="vente.php">
        <div style="border:1px solid blue;
        background-color:white;
        border-radius:5px;
        padding:16px;" align="center">
        <h4 class="text-info" style="font-weight:bold;">'
        .$row["produit"].'</h4>

        <input type="text" name="quantity" value="" required="true" 
        autocomplete="off" placeholder="Saississez la quantité " class="form-control" />

        Prix <input type="text"
        style="text-align:center;" name="p_vente"
        required="true" autocomplete="off"
        value="'.$row["p_vente"].'"
        class="form-control" />

        Quantité actuelle <input type="text"
        style="text-align:center;" readonly="true" name="quantite"
        required="true" autocomplete="off"
        value="'.$row["quantite"].'"
        class="form-control" />

        <input type="hidden" name="p_achat"
        required="true" autocomplete="off"
        value="'.$row["p_achat"].'"
        class="form-control" />

        <input type="hidden" name="produit"
        value="'.$row["produit"].'" />

        <input type="hidden" name="id_produit" 
        value="'.$row["id"].'" />

        <input type="hidden" name="num"
        value="'.$id_user.'" />';

        $output .= '<input type="submit" name="add_to_cart" style="margin-top:5px;"
        class="btn btn-primary" value="Ajouter"/>
        </div>
        </form>
        <br><br></div>';
    }
   echo '</div>';
   echo $output;
}
else
{
   
}

?>

