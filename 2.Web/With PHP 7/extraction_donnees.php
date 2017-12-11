<?php
$bdd = new PDO('mysql:host=sql-10.proxgroup.fr:3306;dbname=stationm_donnees;charset=utf8', 'stati_stationm', 'utbmcsm2015');


$req = $bdd->prepare("SELECT * FROM `mesures` ORDER BY `created_at` ASC");
$req->execute();
$dreq = $req->fetch();
$debut = $dreq['created_at'];

$req = $bdd->prepare("SELECT * FROM `mesures` ORDER BY `created_at` DESC");
$req->execute();
$dreq = $req->fetch();
$fin = $dreq['created_at'];

$req = $bdd->prepare("SELECT * FROM `mesures` ORDER BY `id`");
$req->execute();

if(isset($_GET['t']))
{
	?>
	<table border="1" width="100%">
		<caption>Données enregistrées entre <?php echo $debut; ?> et <?php echo $fin; ?></caption>
		<tr>
			<th>id</th>
			<th>vitesse</th>
			<th>direction</th>
			<th>temperature</th>
			<th>created_at</th>
			<th>updated_at</th>
		</tr>
	<?php
}

while($dreq = $req->fetch())
{
	if(isset($_GET['t']))
	{
		?>
		<tr>
			<td><?php echo $dreq['id']; ?></td>
			<td><?php echo $dreq['vitesse']; ?></td>
			<td><?php echo $dreq['direction']; ?></td>
			<td><?php echo $dreq['temperature']; ?></td>
			<td><?php echo $dreq['created_at']; ?></td>
			<td><?php echo $dreq['updated_at']; ?></td>
		</tr>
		<?php
	}
	else
	{
		echo $dreq['id'].','.$dreq['vitesse'].','.$dreq['direction'].','.$dreq['temperature'].','.$dreq['created_at'].','.$dreq['updated_at'].'<br/>';
	}
}

if(isset($_GET['t']))
{
	?>
	</table>
	<?php
}
$req->closeCursor();

?>