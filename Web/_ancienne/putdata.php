<?php
$con = mysql_connect("localhost","root","169088@Ql");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db("meteo", $con);

$vit=$_GET['v'];
$dire=$_GET['d'];
$tem=$_GET['t'];

$annee = substr($_GET['time'], 0,2);
$mois = substr($_GET['time'], 2,2);
$jour = substr($_GET['time'], 4,2);
$heure = substr($_GET['time'], 6,2);
$minute = substr($_GET['time'], 8,2);
$seconde = substr($_GET['time'], 10,2);

$timec='20'.$annee.'-'.$mois.'-'.$jour.' '.$heure.':'.$minute.':'.$seconde;   
echo $timec.'<br/>';

$sql = 'INSERT INTO `meteos`(`id`, `vitesse`, `direction`, `temperature`, `created_at`, `updated_at`) VALUES (NULL, '.$vit.','.$dire.','.$tem.',\''.$timec.'\',NOW() )';  
echo $sql.'<br/>';

mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());

mysql_close();

?>