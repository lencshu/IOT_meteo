<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');

$sql = "SELECT vitesse FROM `mesures` ORDER BY `created_at` DESC LIMIT 1";

$req = $bdd->prepare($sql);
$req->execute();

$row = $req->fetch();
//enregistrer dans une variable row
echo "<font size=\"8rem\" >&#10163 La vitesse du vent: </font>";
//vitesse en m/s puis convertie
echo "<font size=\"10rem\" >".number_format($row['vitesse'],1,","," ")." km/h ou </font>";
echo "<font size=\"10rem\" >".number_format(($row['vitesse']*0.539957),1,","," ")." nds </font>";


$req->closeCursor();

?>