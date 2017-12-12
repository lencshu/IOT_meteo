<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');

$sql = "SELECT direction FROM `mesures` ORDER BY `created_at` DESC LIMIT 1";

$req = $bdd->prepare($sql);
$req->execute();

$row = $req->fetch();

echo "<font size=\"10rem\" >La direction du vent: </font>";
echo "<font size=\"10rem\" >".$row['direction']."</font>";
echo "<font size=\"10rem\" >  &#176</font>";
//affricher la donné avec des paramétres de HTML

$req->closeCursor();
?>