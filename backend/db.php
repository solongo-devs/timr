<?php
	ini_set ('display_errors', 'On');
	$DBNAME = 'timr.db';
	
	$db = new SQLite3($DBNAME);
	$db -> exec("CREATE TABLE IF NOT EXISTS checkpoints(
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					uid TEXT NOT NULL DEFAULT '',
					timestamp TEXT NOT NULL DEFAULT '')");
?>