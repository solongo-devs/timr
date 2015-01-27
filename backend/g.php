<?php
	require_once "db.php";
	
	$uid = $_GET['uid'];
	$db->exec("INSERT INTO checkpoints (uid, timestamp) VALUES ('".$uid."', DATETIME('now'))");
	$db->close();

	$response = http_get("http://timr.solongo.office/timr/timetracker/web/api/cardreader?id=" . $uid);

	header("Status: 200 1", true, 200);
?>