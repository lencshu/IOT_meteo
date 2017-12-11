<html>
  <head>
    <title>Essai One !</title>
  </head>
  <body>
	  <p>Salut, est ce que php marche ?</p>
	  <?php
		echo 'oui !';

		$sql = "SELECT * FROM `mesures` ORDER BY `created_at` DESC LIMIT 1";
		echo $sql.'<br/>';
		
		$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');
		//$con = mysql_connect("sql-10.proxgroup.fr:3306","stati_stationm","utbmcsm2015");
				
		echo 'next';

		//mysql_select_db("stationm_donnees", $con);

		//$sth = mysql_query($sql);
		$rd = $bdd->prepare($sql);
		$rd->execute();

		$drd = $rd->fetch();
		//$row = mysql_fetch_array($sth);

		echo $drd['id'];
		mysql_close($con);
		
		?>
  </body>
</html>
