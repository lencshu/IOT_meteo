<?php
session_start();

//on r¨¦cup¨¦rer les deux mdp
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
    // echo $_POST['mdp'].'<br/>';
    // echo $amt.'<br/>';
    // echo $inv.'<br/>';
    // echo strncmp($_POST['mdp'],$amt,4);
    // echo strncmp($_POST['mdp'],$inv,4);
    // on traite le POST
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
    //si le mot de passe enregistr¨¦ ne correspond ¨¤ aucun des deux enregistr¨¦es
    $_SESSION['connect'] = "non";
}

if(isset($_SESSION['connect']) && $_SESSION['connect']=="oui")
   {
?>

<!DOCTYPE HTML>
<html>
    <head>
        <!--initialisation de HTML-->
        <meta http-equiv="refresh" content="100">
        <meta http-equiv="Content-Type" content="text/html; charset=GB2312" />
        <meta name="description" content="meteo" /> 
        <meta http-equiv="Content-Type" content="text/html; charset=GB2312" />
        <title>Station - Meteo</title>
        <link rel="stylesheet" type="text/css" href="style.php" />
        <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

        <!--d¨¦finir la taille de mot-->
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

        <!-- configurer les deux graphs pour afficher la vitesse du vent et la Temp¨¦rature dans une p¨¦riode -->
        <script src="http://code.highcharts.com/highcharts.js"></script>
        <script src="http://code.highcharts.com/modules/exporting.js"></script>
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
                        text: 'Degree Celsius'
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

            //obtenir les datas sous la forme de JSON
            $.getJSON("data.php", function(json) {
                optionsa.xAxis.categories = json[0]['data'];
                optionsb.xAxis.categories = json[0]['data'];
                //la vitesse du vent
                optionsa.series[0] = json[1];
                //la temp¨¦rature
                optionsb.series[0] = json[3];
                chart1 = new Highcharts.Chart(optionsa);
                chart2 = new Highcharts.Chart(optionsb);
            });

            
  });
        </script>
    </head>
    <body>
        <!-- afficher la titre du projet -->
        <div style="text-align:center;background-color:#0000;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">M&eacute;t&eacute;o</div>
        <br/>
        <!--afficher le temps-->
        <div style="text-align:center;background-color:#bfbfbf;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
        <iframe src="data_temps.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="1000rem" ></iframe></div>

        <!--afficher la temp¨¦rature-->
        <div style="text-align:center;background-color:#B0B0BF;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
        <iframe src="data_temperature.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="1000rem" ></iframe></div>

        <!--afficher la direction du vent-->
        <div style="text-align:center;background-color:#C8F7C5;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
        <iframe src="data_direction.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="1000rem" ></iframe></div>

        <!--afficher la vitesse du vent-->
        <div style="text-align:center;background-color:#86E2D5;width:auto;height:1.5rem;overflow:hidden;margin:0 auto;">
        <iframe src="data_vitesse.php" style="position:relative;top:0.3rem;font-weight:bold;" frameborder="no" height="100rem" width="1000rem" ></iframe></div>
        <br />

        <!--afficher la boussole pour indiquer la direction du vent-->
        <div class="compass">
        <div class="direction"><p>Direction du Vent<span> </span></p></div>
        <div class="arrow ne"></div>
        </div>

        <!-- afficher le dernier photo captur¨¦ par le cam¨¦ra-->
        <div style="text-align:center;background-color:#0000;width:auto;height:7rem;overflow:hidden;margin:0 auto;">
        <a href="javascript:location.reload();"><img src="meteo.png" alt="Cam&eacute;ra" style="position:relative;top:1rem;" height="600rem" width="auto"></img></a>
        </div>
        <br />

        <!--afficher deux tableaux pour indiquer la variation de temp¨¦rature et la vitesse du vent-->
        <!-- afficher la tire du graphe de la vitesse du vent-->
        <div style="font-size: 0.5rem; text-align:center;background-color:#0000;width:auto;margin:0 auto;"> Vitesse du vent</div>
        <div id="grapha" style="min-width: 10.0rem; height: 6.0rem; margin: 0 auto"></div>
        <br />
        <!-- afficher la tire du graphe de la temp¨¦rature-->
        <div style="font-size: 0.5rem; text-align:center;background-color:#0000;width:auto;margin:0 auto;">Temp&eacute;rature</div>
        <div id="graphb" style="min-width: 10.0rem; height: 6.0rem; margin: 0 auto"></div>
    </body>
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
else //si l'on n'est pas connecte
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