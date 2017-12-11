<?php
$con = mysql_connect("localhost","root","169088@Ql");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db("meteo", $con);


$m=mysql_query("SELECT id FROM meteos ORDER BY `id` DESC LIMIT 1;");
$n=mysql_fetch_assoc($m);
$c=$n['id'];
$a=$c-52;
$b=$c-2;


$sth = mysql_query("SELECT created_at FROM meteos ORDER BY `created_at` ASC LIMIT  {$a}, {$b}");
$rows = array();
$rows['name'] = 'Created_at';
while($r = mysql_fetch_assoc($sth)) {
    $rows['data'][] = $r['created_at'];
}

$sth = mysql_query("SELECT vitesse FROM meteos ORDER BY `created_at` ASC LIMIT  {$a}, {$b}");
$rows1 = array();
$rows1['name'] = 'Vitesse (m/s)';
while($rr = mysql_fetch_array($sth)) {
    $rows1['data'][] = $rr['vitesse'];
}

$sth = mysql_query("SELECT direction FROM meteos ORDER BY `created_at` ASC LIMIT  {$a}, {$b}");
$rows2 = array();
$rows2['name'] = 'Direction (Degree)';
while($rrr = mysql_fetch_assoc($sth)) {
    $rows2['data'][] = $rrr['direction'];
}

$sth = mysql_query("SELECT temperature FROM meteos ORDER BY `created_at` ASC LIMIT  {$a}, {$b}");
$rows3 = array();
$rows3['name'] = 'Temperature (Celsius degree)';
while($rrrr = mysql_fetch_assoc($sth)) {
    $rows3['data'][] = $rrrr['temperature'];
}



    $result = array();
array_push($result,$rows);
array_push($result,$rows1);
array_push($result,$rows2);
array_push($result,$rows3);
echo json_encode($result,JSON_NUMERIC_CHECK);

mysql_close($con);

?>


