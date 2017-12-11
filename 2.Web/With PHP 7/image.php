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