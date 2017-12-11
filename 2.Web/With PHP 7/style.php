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