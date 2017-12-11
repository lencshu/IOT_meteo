<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');



$morceaux = explode(",", $_GET['ligne']); //récupérer le chiffre entré dans un tableau selon le semble <<,>>
$time = $morceaux[0]; 
//mettre le premier morceau, le temps dans la variable time
$tem = $morceaux[1]; 
//mettre le deuxième morceau, le temps dans la variable tem
$vit = $morceaux[2];  
//mettre le troisième morceau, le temps dans la variable vit
$dire = $morceaux[3];  
//mettre le quarantième morceau, le temps dans la variable dire
$annee = substr($time, 0,2);  
//mettre le morceau de 0 à 1 de la variable time dans une autre variable annee
$mois = substr($time, 2,2);  
//mettre le morceau de 2 à 3 de la variable time dans une autre variable mois
$jour = substr($time, 4,2);  
//mettre le morceau de 4 à 5 de la variable time dans une autre variable jour
$heure = substr($time, 6,2);  
//mettre le morceau de 6 à 7 de la variable time dans une autre variable heure
$minute = substr($time, 8,2);  
//mettre le morceau de 8 à 9 de la variable time dans une autre variable minute
$seconde = substr($time, 10,2);  
//mettre le morceau de 10 à 11 de la variable time dans une autre variable seconde
$timec='20'.$annee.'-'.$mois.'-'.$jour.' '.$heure.':'.$minute.':'.$seconde;
//réformer l'affichage du temps
echo $timec.'<br/>'; //afficher le temps
$sql = 'INSERT INTO `mesures`(`id`, `vitesse`, `direction`, `temperature`, `created_at`, `updated_at`) VALUES (NULL, '.$vit.','.$dire.','.$tem.',\''.$timec.'\',NOW() )';
echo $sql.'<br/>'; //enregistrer dans la base de donné


$req = $bdd->prepare($sql);
$req->execute();
?>