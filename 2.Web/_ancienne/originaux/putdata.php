<?php
$con = mysql_connect("localhost","root","169088@Ql");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db("meteo", $con);

$morceaux = explode(",", $_GET['ligne']);
$time = $morceaux[0];
$tem = $morceaux[1];
$vit = $morceaux[2];
$dire = $morceaux[3];

$annee = substr($time, 0,2);
$mois = substr($time, 2,2);
$jour = substr($time, 4,2);
$heure = substr($time, 6,2);
$minute = substr($time, 8,2);
$seconde = substr($time, 10,2);

$timec='20'.$annee.'-'.$mois.'-'.$jour.' '.$heure.':'.$minute.':'.$seconde;   
echo $timec.'<br/>';

$sql = 'INSERT INTO `meteos`(`id`, `vitesse`, `direction`, `temperature`, `created_at`, `updated_at`) VALUES (NULL, '.$vit.','.$dire.','.$tem.',\''.$timec.'\',NOW() )';  
echo $sql.'<br/>';

mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());

mysql_close();

?>