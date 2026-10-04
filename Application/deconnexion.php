<?php
ob_start();
session_start();
unset($_SESSION['id']);
header("location:connexion.php");
?>