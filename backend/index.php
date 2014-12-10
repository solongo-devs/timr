<html>
  <head>
    <title>Timr</title>
  <head>
  <body>
    <table>
	  <tr>
	    <td>ID</td>
		<td>UID</td>
		<td>Zeitstempel</td>
	  </tr>
<?php
	require_once "db.php";
	
	$checkpoints = $db->query("SELECT * FROM checkpoints");
	while ($point = $checkpoints->fetchArray()) {
		echo '<tr><td>'.$point['id'].'</td>';
		echo '<td>'.$point['uid'].'</td>';
		echo '<td>'.$point['timestamp'].'</td></tr>';
	}
	$db->close();
?>
	</table>
  </body>
</html>