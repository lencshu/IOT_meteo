<?php
if(isset($_POST['namt']))
{
	//on traite le post
	if(strlen(substr($_POST['namt'],0,5))==5)
	{
		$monfichier = fopen('amt.psw', 'r+');
		fputs($monfichier, $_POST['namt']);
		fclose($monfichier);
		echo 'Le mot de passe admin  mis à jour';
	}
	else
		echo 'Le mot de passe admin n\'est pas de cinq lettres';
	
	if(strlen(substr($_POST['ninv'],0,5))==5)
	{
		$monfichier = fopen('inv.psw', 'r+');
		fputs($monfichier, $_POST['ninv']);
		fclose($monfichier);
		echo 'Le mot de passe invité  mis à jour';
	}
	else
		echo 'Le mot de passe invité n\'est pas de cinq lettres';
	
}
?>

<html>
  <head>
    <title>Modification des mots de passe</title>
  </head>
  <body>
	  <h1>Modification des mots de passe</h1>
	  
<?php
//on récupère les deux mdp
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

?>
	  <p>Les deux mots de passe doivent impérativement être des mots de cinq lettre, sans accent, ni symbole spéciaux.</p>
	  <form method="post" action="">
		  Mot de passe admin :
		  <input type="text" name="namt" size="20" value="<?php echo $amt; ?>"><br/>
		  Mot de passe invité :
		<input type="text" name="ninv" size="20" value="<?php echo $inv; ?>"><br/>
		<input type="submit" value="Modifier">
	  </form>
		
  </body>
</html>
