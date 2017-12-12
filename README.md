[TOC]

# Station Météo
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\p1.png)</p>

Projet TX de l'année 2016 en automne
Cycle d'ingénieur de l'UTBM 

`Département Génie Mécanique et Conception`

`Filière Conception des Systèmes Mécatroniques`


#1. Introduction
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\cap_20171212_155318.png)</p>

Nous réalisons dans le cadre de ce projet une station météo à base Arduino pour le club de voile du bassin de Champagney. Cette station météo doit mesurer différents paramètres au bord du lac et les rendre disponibles via internet aux membres du club, et ce afin que ces derniers puissent juger des conditions de navigations avant de se rendre sur place.
http://www.station-meteo-csm.nhvvs.fr

- Connexion physique
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\p2.png)</p>

#2.Arduino

~~~c
//librairies nécessaires
#include <Process.h>

//librairies pour le développement
#include <Console.h>

//définition de define (paramétrage des entrés/sorties)
#define DO_BUZ 7 //pour implantation d'un buzzer (dev)
#define DI_ANEMOMETRE_INT 2
#define AI_GIROUETTE 0
#define AI_TEMPERATURE 1
#define DUREE_LECTURE_CAPTEUR 30

#define HEURE_DEBUT 8
#define HEURE_FIN 20

//variables générales
int direction_analog_in[] = {786,405,460,84,92,65,184,127,286,243,630,599,945,827,978,702};
char path_photo_dragino[] = "/mnt/sda1/meteo.png"; //photo_meteo_2
char path_login_photo_web[] = "ftp://stationm:utbmcsm2015@station-meteo-csm.nhvvs.fr/httpdocs/";
char path_donnes_web_csv2[] = "http://station-meteo-csm.nhvvs.fr/putdata.php";
int date_heure[6]; //contient annee-mois-jour-heure-minute-seconde
volatile int compteur_vent;
float donnees[3]; //contient température, vitesse du vent et direction
int numero_boucle; //numero d'iteration de la boucle principale
unsigned long tempo = 0;
int derniere_mesure = 0;

//déclarations fonctions
void prendrePhoto();
void envoiPhoto();
void envoiCSV();
void majTime();
void incrementationCompteurVent();
void lectureCapteurs(int duree);
int trouverDirectionProche(int analogiqueGirouette);
String ecrireNombre(int in);
String ecrireTimeComplet();
String ecrireTimeDonneesCSV();
void logC(String txt, int nb);
void cycleMesure();


// SETUP SETUP SETUP SETUP SETUP SETUP SETUP SETUP SETUP
void setup()
{
  Bridge.begin();
  Console.begin();
  //while(!Console);
  logC("Fin Bridge et Console setup", 0);
  
  logC("Debut setup", 0);
  numero_boucle = 0;
  tempo = 0;
  
  //pinModes
  pinMode(DO_BUZ, OUTPUT);
  pinMode(DI_ANEMOMETRE_INT, INPUT_PULLUP);
  attachInterrupt(0,incrementationCompteurVent, FALLING);

  tone(DO_BUZ,500,500);
  logC("Fin du setup", 0);
}

// LOOP LOOP LOOP LOOP LOOP LOOP LOOP LOOP LOOP LOOP LOOP
void loop()
{
  logC("Loop Debut numero",numero_boucle);
  majTime(); //mise à jour de l heure et la date.

  if(constrain(date_heure[3],HEURE_DEBUT,HEURE_FIN) == date_heure[3])//si l'on est dans la période d'activation
  {
    logC("Prise de mesures", 0);
    cycleMesure();
  }
  else if(date_heure[3] == HEURE_DEBUT-1) //à moins d une heure 
  {
    //on attend un nombre de minutes jusqu'à l'heure pile
    tempo = 60*(60-date_heure[4]+1);
    tempo = tempo * 1000;
    Console.print("Pause partielle :");
    Console.println(tempo);
    delay(tempo);
  }
  else
  {
    //on attend une heure
    tempo = 60*60000;
    Console.print("Pause heure :");
    delay(tempo);
  }
  tone(DO_BUZ,1000,50);
  logC("Loop Fin",0);
}

void cycleMesure()
{
  lectureCapteurs(DUREE_LECTURE_CAPTEUR);
  envoiCSV();
  prendrePhoto();
  envoiPhoto();
  
  Console.println(ecrireTimeDonneesCSV());
}

//fonctions dragino
void prendrePhoto()
{
  logC("prendrePhoto Process Debut",0);
  Process photo;
  photo.begin("fswebcam");
  photo.addParameter(path_photo_dragino);
  photo.addParameter("-S 20");
  photo.addParameter("-r 1280x720");
  photo.run();
  while (photo.available()>0) {
    char c = photo.read();
    Console.print(c);
  }
  logC("prendrePhoto Process Fin",0);
}

void envoiPhoto()
{
  logC("envoiPhoto Process Debut",0);
  Process envoi;
  envoi.begin("curl");
  envoi.addParameter("-T");
  envoi.addParameter(path_photo_dragino);
  envoi.addParameter(path_login_photo_web);
  envoi.run();
  while (envoi.available()>0) {
    char c = envoi.read();
    Console.print(c);
  }
  logC("envoiPhoto Process Fin",0);
}


void envoiCSV()
{
  logC("envoiLiDonneesCSV Process Debut",0);
  Process envoi;
  envoi.begin("curl");
  String adresse = path_donnes_web_csv2;
  adresse = String(adresse + "?ligne=");
  //Console.println(adresse);
  adresse += ecrireTimeDonneesCSV();
  Console.println(adresse);
  envoi.addParameter(adresse);
  adresse = "";
  envoi.run();
  logC("envoiLiDonneesCSV Process Fin",0);
}

void majTime() //mise à jours des infos contenues dans 
{
  logC("majTime Process Debut",0);
  Process pTime;
  pTime.begin("date");
  pTime.addParameter("+%T-%D");
  pTime.run();
  while(pTime.available() > 0)
  {
    String getText = pTime.readString();
    date_heure[3] = getText.substring(0,2).toInt(); //heure
    date_heure[4] = getText.substring(3,5).toInt(); //minute
    date_heure[5] = getText.substring(6,8).toInt(); //seconde
    date_heure[1] = getText.substring(9,11).toInt(); //mois
    date_heure[2] = getText.substring(12,14).toInt(); //jour
    date_heure[0] = getText.substring(15,17).toInt(); //annee
  }
  logC("majTime Process Fin",0);
}

//fonctions capteurs
void incrementationCompteurVent()
{
  compteur_vent = compteur_vent +1;
}

void lectureCapteurs(int duree)
{
  logC("lectureCapteurs Debut",0);
  logC("Nombre de lecture des capteurs",duree);
  int boucles =0;
  unsigned long debut_mesure_vent;
  long somme_temperature = 0, somme_girouette = 0;

  //début de la duree de prise de mesures
  debut_mesure_vent = millis();
  compteur_vent = 0;
  for(boucles=0;boucles<duree;boucles++) //si on a encore le temps de prende une mesure
  {
    logC("Prise de mesure numero",boucles);
    //on prend la valeur de température
    int lecture = 0;
    lecture = analogRead(AI_TEMPERATURE);
    int moy_temp = somme_temperature/boucles;
    if(boucles==0) {moy_temp=derniere_mesure;}
    if(lecture > (moy_temp+10))
    {
      logC("Sur-mesure",(lecture - moy_temp));
      lecture = moy_temp+10;
    }
    else if(lecture < (moy_temp-10))
    {
      logC("Sous-mesure",(lecture - moy_temp));
      lecture = moy_temp-10;
    }
    
    logC("Capteur : lecture ai_temperature",lecture);
    //logC("Capteur : calcul ai_temperature", (lecture*(5000/1024.0)));
    somme_temperature = somme_temperature + lecture;
    //on prend la valeur de girouette
    lecture = analogRead(AI_GIROUETTE);
    logC("Capteur : lecture ai_girouette",lecture);
    somme_girouette = somme_girouette + lecture;

    tone(DO_BUZ,500,5);
    delay(950); //atente de moins d une seconde pour ne pas prendre trop de mesures
  }
  //fin de la duree de prise de mesures
  //vitesse du vent
  donnees[1] = (millis() - debut_mesure_vent);
  donnees[1] = (compteur_vent * 2.4 * 1000) / donnees[1];
  logC("Vent, nombre de tics", compteur_vent);
  long difference = (millis() - debut_mesure_vent);
  logC("Vent, duree en ms (approx)", difference);
  logC("Vent, vitesse en km/h", donnees[1]);
  //température
  somme_temperature = somme_temperature / duree;
  derniere_mesure = somme_temperature;
  double temp_temperature = somme_temperature*(5000/1024.0);
  donnees[0] = ( temp_temperature - 500)/10.0;
  logC("Temperature lue (moyenne)",somme_temperature);
  logC("Temperature (etalonnee)", donnees[0]);
  //direction
  donnees[2] = trouverDirectionProche(somme_girouette / duree)*22.5;
  logC("Direction (degres)", donnees[2]);
  
  logC("lectureCapteurs Fin",0);
}

int trouverDirectionProche(int analogiqueGirouette)
{
  int p, rtr=-1, difference_min = 1024;
  for(p=0;p<16;p++)
  {
    //on compare la valeur lue avec celle du tableau et si est est plus petite que
    //celle enregistrée alors c est la nouvelle direction
    if( abs(analogiqueGirouette - direction_analog_in[p])<difference_min)
    {
      difference_min = abs(analogiqueGirouette - direction_analog_in[p]);
      rtr = p;
    }
  }
  return rtr;
}

//fonction annexes
String ecrireNombre(int in) //écrit un nombre de deux chiffres ou plus
{
  String rtr = "";
  if(in<10)
  {
    rtr = "0";
  }
  rtr = rtr+in;
  return rtr;
}

String ecrireTimeComplet()
{
  String rtr = "";
  int i;
  for(i=0;i<6;i++)
  {
    rtr = rtr+ecrireNombre(date_heure[i]);
  }
  return rtr;
}

String ecrireTimeDonneesCSV()
{
  String rtr = ecrireTimeComplet();
  int i;
  for(i=0;i<3;i++)
  {
    rtr = rtr+","+donnees[i];
  }
  //rtr = rtr + ";";
  return rtr;
}

void logC(String txt, int nb)
{
  Console.print(millis()/1000.0);
  Console.print(" >=> ");
  Console.print(txt);
  Console.print(" :: ");
  Console.println(nb);
}

~~~


#2.Web
On suppose que l'espace de website est tout neuve. Rien n'est paramètré. Les étapes suivantes nous permettent pour les configurer pas à pas.

## 2.1 les environments
Le Website est une page Web de sort `dynamique`, c'est à dire quand il y a des requêtes de navigateur, le web page consulte la base donné pour puis composer la vue de page web. s'il n'a pas de requête, il n'y a pas de page web. Cette notion est opposée à celle de `Page web statique` (html)

Faut qu'il donc existe les environments nécessaires pour fonctionner la programme.

Le système de website est basé sur PHP7.

### 2.1.1 Base donnée

On prend MYSQL(Le système de Base donnée populaire) comme exemple
système:`UBUNTU`(linux)

- Installations

~~~python
sudo apt-get install  mysql-client-core-5.6
sudo apt-get install mariadb-client-core-10.0
sudo apt-get install mysql-server
~~~
il faut entrer le mot de passe pendant l'installations

- connexion de MYSQL

~~~shell
mysql -u root -p
~~~
-u : user
-p : mot de passe
-h : hôte


~~~shell
CREATE DATABASE stationm_donnees character set utf8;
CREATE USER 'stati_stationm'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';
GRANT ALL ON emoncms.* TO 'stati_stationm'@'localhost';
flush privileges;

~~~
Redémarrer MYSQL

~~~sh

sudo /etc/init.d/mysql restart
/etc/init.d/mysql status
~~~


Dans notre cas, la base donnée doit être paramètrée sur  panel de gestion
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\cap_20171212_145410.png)</p>
Les champs qu'il faut entrer pour ajouter une nouvelle base donnée.
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\cap_20171212_145604.png)</p>

### 2.1.2 PHP 7

Installations toute simple

~~~sh
sudo apt-get install php7.0 
~~~

Sur hébergeur `nhvvs.fr`, Il faut aussi utiliser le panel de gestion
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\cap_20171212_150654.png)</p>
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\cap_20171212_150714.png)</p>

## 2.2 Composants principals de système WEB
<p align="center">![](C:\Users\lencs\Desktop\Meteo\Git_stationeo\MultiMedia\cap_20171212_154901.png)</p>

Il y a 12 php programme. Parmi lesquel il y en a 9 qui réalise la fonction principale.
Après la réception des infos envoyée de l'Arduino. `putdata.php` nous aide pour enregistrer des info dans la base donnée.
Une fois c'est fait, il y donc possible d'afficher des infos quand il y a des requêtes de navigateur. la mise-en-page web se fait par :
`data_direction.php`
`data_temperature.php`
`data_temps.php`
`data_vitesse.php`
`image.php`
`data.php`: JSON
`index.php`
`style.php`

!!! hint "Si la Base Donnée est changé..."
	Si la Base Donnée est changé, il est obligatoire de modifier la ligne comme ci-après dans tous les `.php`

	de 
	~~~php
	$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');  //connecter Mysql via php
	~~~
	à
	~~~php
	$bdd = new PDO('mysql:nouveau_nom_hôte;dbname=nouveau_nom_BD;charset=utf8', 'nom_ADM', 'mot_de_passe');
	~~~


### putdata.php


~~~php
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
~~~
### data_direction.php

~~~php
<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');

$sql = "SELECT direction FROM `mesures` ORDER BY `created_at` DESC LIMIT 1";

$req = $bdd->prepare($sql);
$req->execute();

$row = $req->fetch();

echo "<font size=\"10rem\" >&#8635 La direction du vent: </font>";
echo "<font size=\"10rem\" >".$row['direction']."</font>";
echo "<font size=\"10rem\" >  &#176</font>";
//affricher la donné avec des paramétres de HTML

$req->closeCursor();
?>
~~~
### data_temperature.php

~~~php
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
~~~
### data_temps.php

~~~php
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
~~~
### data_vitesse.php

~~~php
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
~~~
### image.php

~~~php
<?php

$boucle=1;
$ledernier=1;
while($boucle==1)
{
	if (file_exists("meteoimage/m".$ledernier.".png")&&$ledernier<10)
	{
		$ledernier++;
	}
	elseif ($ledernier==10) 
	{
			if (file_exists("meteoimage/meteo.png"))
			{
				for ($i=1; $i < 10; $i++) { 
				unlink("meteoimage/m".$i.".png");
				}	
				$ledernier=1;
			}
			else
			{
				for ($i=1; $i < 10; $i++) { 
				unlink("meteoimage/m".$i.".png");
				}
				$boucle=0;	
			}
			

	}
	else
	{
		$boucle=0;
	}

}


if (file_exists("meteoimage/meteo.png"))
	{
		$file_name_old="meteoimage/meteo.png";
		$file_name_new="meteoimage/m".$ledernier.".png";
		rename($file_name_old,$file_name_new);
	}
	else
	{
		$ledernier-=1;
	}

$r='meteoimage/m'.$ledernier.'.png';
echo "$r";
echo '<img src="'.$r.'" />';

?>
~~~
### data.php

~~~python
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
~~~
### index.php

~~~python
<?php
session_start();

//on r??cup??re les deux mdp
$monfichier = fopen('amt.psw', 'r');
$amt = fgets($monfichier);
fclose($monfichier);
$monfichier = fopen('inv.psw', 'r');
$inv = fgets($monfichier);
fclose($monfichier);

$amt_m = explode(".",$amt);
$amt = $amt_m[0];
$inv_m = explode(".",$inv);
$inv = $inv_m[0];

if(isset($_POST['mdp']))
{
	//echo $_POST['mdp'].'<br/>';
	//echo $amt.'<br/>';
	//echo $inv.'<br/>';
	//echo strncmp($_POST['mdp'],$amt,4);
	//echo strncmp($_POST['mdp'],$inv,4);
	
	//on traite le POST
	if(strncmp($_POST['mdp'],$amt,5)==0)
	{
		$_SESSION['connect'] = "oui";
		$_SESSION['admin'] = "oui";
		$_SESSION['mdp'] = $amt;
	}
	if(strncmp($_POST['mdp'],$inv,5)==0)
	{
		$_SESSION['connect'] = "oui";
		$_SESSION['admin'] = "non";
		$_SESSION['mdp'] = $inv;
	}
}

if(strncmp($_SESSION['mdp'],$amt,5)!=0 && strncmp($_SESSION['mdp'],$inv,5)!=0)
{
	//si le mot de passe enregistr?? ne correspond ?? aucun des deux enregistr??s
	$_SESSION['connect'] = "non";
}

if(isset($_SESSION['connect']) && $_SESSION['connect']=="oui")
   {
?>
<!DOCTYPE HTML>
<html>
    <head>
		 <meta http-equiv="refresh" content="100">
    <meta http-equiv="Content-Type" content="text/html; charset=GB2312" />
    <meta name="description" content="meteo" /> 
        <meta http-equiv="Content-Type" content="text/html; charset=GB2312" />
        <!--initialisation de HTML-->

        <title>Meteo</title>
        <link rel="stylesheet" type="text/css" href="style.php" />
        <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

        <script type="text/javascript">
        var cssEl = document.createElement('style');
        document.documentElement.firstElementChild.appendChild(cssEl);
        function setPxPerRem(){
            var dpr = 1;
            var pxPerRem = document.documentElement.clientWidth * dpr / 10;
            cssEl.innerHTML = 'html{font-size:' + pxPerRem + 'px!important;}';
        }
        setPxPerRem(); 
    </script> 
    <!--définir la taille de mot-->
<div style="text-align:center;background-color:#0000;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">M&eacute;t&eacute;o</div>
<br/>
<!--afficher le titre sur le page-->
<div style="text-align:center;background-color:#bfbfbf;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
<iframe src="data_temps.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="800rem" ></iframe>
</div><!--afficher le temps d'enregistrement de la dernier donné sur le page-->
<div style="text-align:center;background-color:#B0B0BF;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
<iframe src="data_temperature.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="800rem" ></iframe>
</div><!--afficher la température d'enregistrement de la dernier donné sur le page-->

<div style="text-align:center;background-color:#C8F7C5;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
<iframe src="data_direction.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="800rem" ></iframe>
</div><!--afficher la direction du vent d'enregistrement de la dernier donné sur le page-->
<div style="text-align:center;background-color:#86E2D5;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
<iframe src="data_vitesse.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="900rem" ></iframe>
</div><!--afficher la vitesse du vent d'enregistrement de la dernier donné sur le page-->
<br />
      <div class="compass">
      <div class="direction">
      <p>Direction du Vent<span> </span></p>
      </div>
      <div class="arrow ne"></div>
      </div><!--afficher la compass pour indiquer la direction du vent -->
<div style="text-align:center;background-color:#0000;width:auto;height:7rem;overflow:hidden;margin:0 auto;"><a href="javascript:location.reload();">
<img src="meteo.png" alt="Cam&eacute;ra" style="position:relative;top:1rem;" height="600rem" width="auto"></img></a>
</div>
<!-- afficher le dernier photo capturé par le caméra-->

<br />
        <script type="text/javascript">
        $(document).ready(function() {
            var optionsa = {
                chart: {
                    renderTo: 'grapha',
                    type: 'spline',
                    marginRight: 130,
                    marginBottom: 25
                },
                title: {
                    text: '',
                    x: -20 
                },
                subtitle: {
                    text: '',
                    x: -20
                },
                xAxis: {
                    categories: [],
              labels: {
                step: 10
               }
                },
                yAxis: {
                    title: {
                        text: 'km/h'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {
                    formatter: function() {
                            return '<b>'+this.x +'</b><br/>' + this.series.name +
                            ': '+ this.y;
                    }
                },
                legend: {
            backgroundColor: '#FFFFFF',
            layout: 'vertical',
            floating: true,
            align: 'right',
            verticalAlign: 'top',
            x: 0,
            y: 3,
            shadow: true
                },
                series: []
            }
            var optionsb = {
                 chart: {
                    renderTo: 'graphb',
                    type: 'spline',
                    marginRight: 200,
                    marginBottom: 0
                },
                title: {
                    text: '',
                    x: -20 //center
                },
                subtitle: {
                    text: '',
                    x: -20
                },
                xAxis: {
                    categories: [],
              labels: {
                step: 10
               }
                },
                yAxis: {
                    title: {
                        text: 'Degr? Celsius'
                    },
                    plotLines: [{
                        value: 0,
                        width: 2,
                        color: '#223333'
                    }]
                },
                tooltip: {
                    formatter: function() {
                            return '<b>'+this.x +'</b><br/>' + this.series.name +
                            ': '+ this.y;
                    }
                },
                legend: {
            backgroundColor: '#FFFFFF',
            layout: 'vertical',
            floating: true,
            align: 'right',
            verticalAlign: 'top',
            x: 0,
            y: 3,
            shadow: true
                },
                series: []
            }
            $.getJSON("data.php", function(json) {
                optionsa.xAxis.categories = json[0]['data'];
                optionsb.xAxis.categories = json[0]['data'];
                optionsa.series[0] = json[1];
                //options.series[1] = json[2];
                optionsb.series[0] = json[3];
                chart1 = new Highcharts.Chart(optionsa);
                chart2 = new Highcharts.Chart(optionsb);
            });

            
  });
        </script>
        <script src="http://code.highcharts.com/highcharts.js"></script>
        <script src="http://code.highcharts.com/modules/exporting.js"></script>
    </head>
<div style="font-size: 0.5rem; text-align:center;background-color:#0000;width:auto;margin:0 auto;"> Vitesse du vent</div>
<div id="grapha" style="min-width: 10.0rem; height: 6.0rem; margin: 0 auto"></div>
<br />
<div style="font-size: 0.5rem; text-align:center;background-color:#0000;width:auto;margin:0 auto;">Temp&eacute;rature</div>
<div id="graphb" style="min-width: 10.0rem; height: 6.0rem; margin: 0 auto"></div>
<!--afficher deux tableaux pour indiquer la variation de température et la vitesse du vent-->
</html>
<p style="font-size: 0.2rem"><a href="./deconnection.php" target="_blank">Sortie</a></p>

<?php
	if($_SESSION['admin']=="oui")
	{
	?>
<p style="font-size: 0.2rem"><a href="./modifier_mdp.php" target="_blank">Modifier les mots de passe</a></p>
	<?php
	}
	?>
<?php
}
else //si l'on n'est pas connect?? 
{
	?>
<html>
  <head>
    <title>Station meteo autonome</title>
  </head>
  <body>
	<p>Bonjour, connectez-vous en entrant le mot de passe requis !</p>
		<form action="" method="post">
			<input type="password" name="mdp" size="50" autofocus>
			<input type="submit" value="Entrer">
		</form>
  </body>
</html>

	<?php
}
~~~
### style.php

~~~php
<?php
header("Content-type: text/css; charset: UTF-8");
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');  //connecter Mysql via php

$sql = "SELECT direction FROM `mesures` ORDER BY `created_at` DESC LIMIT 1";

$req = $bdd->prepare($sql);
$req->execute();

$row = $req->fetch();
//sélectionner la dernière chiffre de la direction dans la base de donné meteo
$req->closeCursor();
?>
.img { 
    display: block; 
    width: 33%; 
    height: auto; 
    margin: 0 auto; 
}    
/*la forme d'affichage l'image*/
# grapha
{
height:5.0rem;
margin: auto;  
position: relative;  
top: 0; left: 0;
}
/*la forme d'affichage le graphe 1*/
# graphb
{
height:5.0rem;
margin: auto;  
position: relative;  
top: 0; left: 0;
}
/*la forme d'affichage le graphe 2*/
@import url(http://fonts.googleapis.com/css?family=Dosis:200,400,500,600);
.compass {
  display: block;
  width: 5rem;
  height: 5rem;
  border-radius: 100%;
  box-shadow: 0 0 0.25rem rgba(0, 0, 0, 0.85);
  position: relative;
  font-family: 'Dosis';
  color: #555;
  text-shadow: 1px 1px 1px white;
}

.compass:before {
  font-weight: bold;
  position: absolute;
  text-align: center;
  width: 100%;
  content: "N";
  font-size: 0.5rem;
  top: 0.05rem;
}
.compass .direction {
  height: 100%;
  width: 100%;
  display: block;
  background: #f2f6f5;
  background: -moz-linear-gradient(top, #f2f6f5 0%, #cbd5d6 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #f2f6f5), color-stop(100%, #cbd5d6));
  background: -webkit-linear-gradient(top, #f2f6f5 0%, #cbd5d6 100%);
  background: -o-linear-gradient(top, #f2f6f5 0%, #cbd5d6 100%);
  border-radius: 100%;
}
.compass .direction p {
  text-align: center;
  margin: 0;
  padding: 0;
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 100%;
  line-height: 80px;
  display: block;
  margin-top: -45px;
  font-size: 0.6rem;
  font-weight: bold;
}
.compass .direction p span {
  display: block;
  line-height: normal;
  margin-top: -24px;
  font-size: 0.4rem;
  text-transform: uppercase;
  font-weight: normal;
}
.compass .arrow {
  width: 100%;
  height: 100%;
  display: block;
  position: absolute;
  top: 0;
}
.compass .arrow:after {
  content: "";
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-bottom: 1.5rem solid red;
  position: absolute;
  top: -6px;
  left: 50%;
  margin-left: -5px;
  z-index: 99;
}
.compass .arrow.ne {
  transform: rotate(<?php echo $row['direction'];?>deg);
}
/*la forme d'affichage le compass pour la représentation de la direction du vent*/
~~~


# Annexe
## station-meteo-csm.nhvvs.fr
### Connexion

####Gmail
stationmeteo.csm@gmail.com


#### HTTP
Connexion à votre panel de gestion :
Lien : `https://pg-01.proxgroup.fr:8443`
Identifiant  : `stationm`
Mot de passe : %mdp% utbmcsm2015 %/mdp%

!!! hint ""
	/!\ Vos fichiers et dossiers sont à placer dans le dossier "httpdocs" que vous trouverez lors de votre connexion FTP au serveur. 

#### FTP
Hôte FTP : `ftp.pg-01.proxgroup.fr`
Nom d'utilisateur : `stationm`
Mot de passe : %mdp% utbmcsm2015 %/mdp%

### base de données
Hôte: `sql-10.proxgroup.fr:3306`
Nom de la base de données:`stationm_donnees`
Nom de l'utilisateur:`stati_stationm`
Mot de passe %mdp% utbmcsm2015 %/mdp%
