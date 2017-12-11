<?php
$con = mysql_connect("localhost","root","169088@Ql");   //connecter Mysql via php
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db("meteo", $con); //sélectionner la base de donné meteo
$sth = mysql_query("SELECT vitesse FROM `meteos` ORDER BY `created_at` DESC LIMIT 1");
//sélectionner la dernière chiffre de la vitesse dans la base de donné meteo
$row = mysql_fetch_array($sth);
//enregistrer dans une variable row
echo "<font size=\"10rem\" >&#10163 La vitesse du vent: </font>";
echo "<font size=\"10rem\" >".$row['vitesse']."</font>";
echo "<font size=\"10rem\" >  m/s</font>";
mysql_close($con); //quitter le processus
?>