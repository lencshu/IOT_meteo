<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');

$sql = "SELECT temperature FROM `mesures` ORDER BY `created_at` DESC LIMIT 1";

$req = $bdd->prepare($sql);
$req->execute();

$row = $req->fetch();
//enregistrer dans une variable row

echo "<font size=\"10rem\" >&#9784 La température: </font>";
echo "<font size=\"10rem\" >".$row['temperature']."</font>";
echo "<font size=\"10rem\" >  &#8451</font>";
//affricher la donné avec des paramétres de HTML


$req->closeCursor();

?>