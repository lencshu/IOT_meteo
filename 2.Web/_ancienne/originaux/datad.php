<?php
$con = mysql_connect("localhost","root","169088@Ql");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db("meteo", $con);


$sth = mysql_query("SELECT direction FROM `meteos` ORDER BY `created_at` DESC LIMIT 1");

$row = mysql_fetch_array($sth);

//echo $row['direction'];

echo "<font size=\"10rem\" >La direction du vent: </font>";
echo "<font size=\"10rem\" >".$row['direction']."</font>";



mysql_close($con);

?>
