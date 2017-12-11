<?php
$con = mysql_connect("localhost","root","169088@Ql");   //connecter Mysql via php
if (!$con) {
  die('Could not connect: ' . mysql_error()); 
}
mysql_select_db("meteo", $con); //sélectionner la base de donné meteo
$sth = mysql_query("SELECT created_at FROM `meteos` ORDER BY `created_at` DESC LIMIT 1");
//sélectionner la dernière chiffre du temps dans la base de donné meteo
$row = mysql_fetch_array($sth);
//enregistrer dans une variable row
echo "<font size=\"10rem\" >&#9775 Le temps: </font>";
echo "<font size=\"10rem\" >".$row['created_at']."</font>";
//affricher la donné avec des paramétres de HTML
mysql_close($con); //quitter le processus
?>