<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');


$sql ="SELECT id FROM mesures ORDER BY `id` DESC LIMIT 1;";
//echo $sql.'<br/>';
$req = $bdd->prepare($sql);
$req->execute();
//sélectionner la dernière ligne dans la base de donné meteo
$n= $req->fetch();
//enregistrer dans une variable n
$c=$n['id'];
$a=$c-152;
if($a<=0)
{
	$a=1;
}
$b=$c-2;

$sql = "SELECT created_at FROM mesures ORDER BY `created_at` ASC LIMIT  {$a}, {$b}";
//echo $sql.'<br/>';
$req = $bdd->prepare($sql);
$req->execute();
//sélectionner la dernière chiffre du temps dans la base de donné meteo
$rows = array();
$rows['name'] = 'Created_at';
while($r = $req->fetch()) {
    $rows['data'][] = $r['created_at'];
	//echo $r['created_at'].'<br/>';
}
//réformer dans un groupe de donné
$sql = "SELECT vitesse FROM mesures ORDER BY `created_at` ASC LIMIT  {$a}, {$b}";
//echo $sql.'<br/>';
$req = $bdd->prepare($sql);
$req->execute();
//sélectionner la dernière chiffre de la vitesse dans la base de donné meteo
$rows1 = array();
$rows1['name'] = 'Vitesse (km/h)';
while($rr = $req->fetch()) {
    $rows1['data'][] = $rr['vitesse'];
	//echo $rr['vitesse'].'<br/>';
}
		 
$sql = "SELECT direction FROM mesures ORDER BY `created_at` ASC LIMIT  {$a}, {$b}";
//echo $sql.'<br/>';
$req = $bdd->prepare($sql);
$req->execute();
//sélectionner la dernière chiffre de la direction dans la base de donné meteo
$rows2 = array();
$rows2['name'] = 'Direction (Degree)';
while($rrr = $req->fetch()) {
    $rows2['data'][] = $rrr['direction'];
}	  
//réformer dans un groupe de donné
$sql = "SELECT temperature FROM mesures ORDER BY `created_at` ASC LIMIT  {$a}, {$b}";
//echo $sql.'<br/>';
$req = $bdd->prepare($sql);
$req->execute();
$rows3 = array();
$rows3['name'] = 'Temperature (Celsius degree)';
while($rrrr = $req->fetch()) {
    $rows3['data'][] = $rrrr['temperature'];
}


    $result = array();
    //réformer dans un groupe de donné
array_push($result,$rows);
array_push($result,$rows1);
array_push($result,$rows2);
array_push($result,$rows3);
//réformer tous ensemble dans un tableau
echo json_encode($result,JSON_NUMERIC_CHECK);
//réformer le tableau sous la forme de Json

$req->closeCursor();

?>