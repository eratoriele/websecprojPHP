<?php
	$dbh = new PDO('mysql:host:localhost','websecpro','');

	$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
?>
