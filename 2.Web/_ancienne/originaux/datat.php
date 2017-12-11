<?php
$con = mysql_connect("localhost","root","169088@Ql");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db("meteo", $con);


$sth = mysql_query("SELECT created_at FROM `meteos` ORDER BY `created_at` DESC LIMIT 1");

$row = mysql_fetch_array($sth);

echo "<font size=\"10rem\" >Le temps: </font>";
//echo $row['created_at'];
echo "<font size=\"10rem\" >".$row['created_at']."</font>";
mysql_close($con);

?>
