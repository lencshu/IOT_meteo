<?php
$con = mysql_connect("localhost","root","169088@Ql");   //connecter Mysql via php
if (!$con) {
  die('Could not connect: ' . mysql_error()); 
}
mysql_select_db("meteo", $con); //sélectionner la base de donné meteo
$sth = mysql_query("SELECT temperature FROM `meteos` ORDER BY `created_at` DESC LIMIT 1");
//sélectionner la dernière chiffre de la temperature dans la base de donné meteo
$row = mysql_fetch_array($sth);
//enregistrer dans une variable row
echo "<font size=\"10rem\" >&#9784 La température: </font>";
echo "<font size=\"10rem\" >".$row['temperature']."</font>";
echo "<font size=\"10rem\" >  &#8451</font>";
//affricher la donné avec des paramétres de HTML
mysql_close($con); //quitter le processus
?>