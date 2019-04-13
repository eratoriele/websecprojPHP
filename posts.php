<?php
    //stored XSS attack

    session_start();
    if(!isset($_SESSION["name"]))
		exit();
    include "database.php";
	include "include.php";
    gen_header();
	LoggedIn(2);
	
	if(isset($_POST["header"]) && isset($_POST["body"]))
	{

		if (!isset($_POST["csrf_token"]) || $_SESSION["csrf_token"]!=$_POST["csrf_token"])
		{
			echo "Security Error";
			exit();
		}

		$sql_r="INSERT INTO websecproj.posts (PostedBy, GroupID, PostHeader, PostBody) VALUES (:username, '1111', :header, :body)";
		//      echo $sql_r;
		$sth=$dbh->prepare($sql_r);

		$sth->bindParam(":header", $_POST["header"]);
		$sth->bindParam(":body", $_POST["body"]);
		$sth->bindParam(":username", $_SESSION["name"]);
		$sth->execute();
		//		var_dump($sth->errorInfo());
	}

	$sth=$dbh->query("SELECT * FROM websecproj.posts ORDER BY PostedOn DESC");
	while($row = $sth->fetch( PDO::FETCH_ASSOC )){
		echo "Posted By:  ". htmlentities($row['PostedBy']) . "<br>\n" . htmlentities($row['PostHeader']) ."<br>".htmlentities($row['PostBody']). "<br><hr>";
	}
	$_SESSION["csrf_token"]=hash("sha256",rand().rand()."secret");
?>
<form method="post">
	<br>
    Create a new post:<br>
	Post Header:<br>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"] ?>">
    <input name="header" style="width: 700px;height: 35px" maxlength="100" minlength="10"></textarea>
	<br>Post Body:<br>
	<textarea name="body" style="width: 700px;height: 80px" maxlength="2000"></textarea><br>
    <input type="submit" value="Make a post">
</form>