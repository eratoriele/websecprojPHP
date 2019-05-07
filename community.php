<?php
    session_start();
	if(!isset($_SESSION["name"])) {
		echo "Please log in<br>";						// If tried to be accesed without logging in
		echo '<a href="./">Go back</a><br>';
		exit();
	}
    include "database.php";
	include "include.php";
    gen_header();
	LoggedIn(2);
?>
<?php

	$page = 0;
	if (isset($_GET["page"]))
		$page = (int) $_GET["page"];		// If a non-integer is given, turns it into 0
	if ($page < 0) 
		$page = 0;
	

?>