[TOC]

# Station Météo
Projet TX de l'année 2016 en automne
Cycle d'ingénieur de l'UTBM 

`Département Génie Mécanique et Conception`

`Filière Conception des Systèmes Mécatroniques`


#1. Introduction

Nous réalisons dans le cadre de ce projet une station météo à base Arduino pour le club de voile du bassin de Champagney. Cette station météo doit mesurer différents paramètres au bord du lac et les rendre disponibles via internet aux membres du club, et ce afin que ces derniers puissent juger des conditions de navigations avant de se rendre sur place.
http://www.station-meteo-csm.nhvvs.fr


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
Il y a 12 php programme. Parmi lesquel il y en a 9 qui réalise la fonction principale.
Après la réception des infos envoyée de l'Arduino. `putdata.php` nous aide pour enregistrer des info dans la base donnée.
Une fois c'est fait, il y donc possible d'afficher des infos quand il y a des requêtes de navigateur. la mise-en-page web se fait par :
`data_direction.php`
`data_temperature.php`
`data_temps.php`
`data_vitesse.php`
`image.php`
`data.php`:JSON
`index.php`
`style.php`

!!! hint "Si la Base Donnée est changé..."
	Si la Base Donnée est changé, il est obligatoire de modifier la ligne comme ci-après de tous les `.php`

	de 
	~~~php
	$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');  //connecter Mysql via php
	~~~
	à
	~~~php
	$bdd = new PDO('mysql:nouveau_nom_hôte;dbname=nouveau_nom_BD;charset=utf8', 'nom_ADM', 'mot_de_passe');
	~~~


###

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
