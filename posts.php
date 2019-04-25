<?php
    //stored XSS attack

    session_start();
    if(!isset($_SESSION["name"]))
		exit();
    include "database.php";
	include "include.php";
    gen_header();
	LoggedIn(2);
?>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php	
	if(isset($_POST["header"]) && isset($_POST["body"]))
	{
		require_once('recaptchalib.php');

   		$response = $_POST["g-recaptcha-response"];
		$verify = new recaptchalib("6LeuJ54UAAAAAO58XWYTLN8iSBVM1HzD5YH0FNac", $response);
		
		if (!isset($_POST["csrf_token"]) || $_SESSION["csrf_token"]!=$_POST["csrf_token"] || !$verify->isValid()) {
			echo "Security Error";
			exit();
		}

		$sql_r="INSERT INTO websecproj.posts (PostedBy, GroupID, PostHeader, PostBody) VALUES (:username, :groups, :header, :body)";
		//      echo $sql_r;
		$sth=$dbh->prepare($sql_r);

		$sth->bindParam(":username", $_SESSION["name"]);
		$sth->bindParam(":groups", $_SESSION["groups"]);
		$sth->bindParam(":header", $_POST["header"]);
		$sth->bindParam(":body", $_POST["body"]);
		$sth->execute();
		//		var_dump($sth->errorInfo());
	}

	$sth=$dbh->query("SELECT * FROM websecproj.posts WHERE Deleted = false AND GroupID =" . $_SESSION["groups"] . " ORDER BY PostedOn DESC");
	while($row = $sth->fetch( PDO::FETCH_ASSOC )){
		echo "<h2>" . htmlentities($row['PostHeader']) . "</h2>";
		echo "<p style=\"font-size: 11px;color: blue\"> Posted by: " . htmlentities($row['PostedBy']) . 
				" on " . htmlentities($row['PostedOn']) . "</p>";
		echo htmlentities($row['PostBody']) . "<br><br>";
		
		echo "<form action=\"./comments.php\" method=\"post\">";
			echo "<input type=\"submit\" value=\"See Comments\">";
			echo "<input type=\"hidden\" name=\"PostID\" value=\"" . htmlentities($row['PostID']) . "\"></form>";

		echo "<hr>";
	}
	$_SESSION["csrf_token"]=hash("sha256",rand().rand()."secret");
?>
<form method="post">
	<br>
    Create a new post:<br>
	Post Header:<br>
    <input name="header" style="width: 700px;height: 35px" maxlength="100" minlength="10"></textarea>

	<br>Post Body:<br>
	<textarea name="body" style="width: 700px;height: 80px" maxlength="2000"></textarea><br>

	<div class="g-recaptcha" data-sitekey="6LeuJ54UAAAAAKTGoUPSwBhvH7_6gyM33SFFxSOB"></div> <br/>

    <input type="submit" value="Make a post">
	
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"] ?>">
</form>