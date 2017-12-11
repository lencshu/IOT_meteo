<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');

$sql = "SELECT created_at FROM `mesures` ORDER BY `created_at` DESC LIMIT 1";

$req = $bdd->prepare($sql);
$req->execute();

$row = $req->fetch();

//traitement de la date
$tim = $row['created_at'];
//echo substr($tim, 11,2).'*'.substr($tim, 14,2).'*'.substr($tim, 17,2).'*'.substr($tim,5,2).'*'.substr($tim, 8,2).'*'.substr($tim, 0,4);
$dat = mktime(substr($tim, 11,2),substr($tim, 14,2),substr($tim, 17,2),substr($tim,5,2),substr($tim, 8,2),substr($tim, 0,4));

$hr = date("H:i",$dat);
$jr = date("d/m/Y",$dat);



//enregistrer dans une variable row
echo "<font size=\"10rem\" >&#9775 Le temps </font>";
echo "<font size=\"10rem\" > à ".$hr." le ".$jr."</font>";
//affricher la donné avec des paramétres de HTML

$req->closeCursor();
?>