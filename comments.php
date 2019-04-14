<?php

    session_start();
    if(!isset($_SESSION["name"]))
        exit();
    include "database.php";
    include "include.php";
    gen_header();
    LoggedIn(-1);

    if (isset($_POST["comment"])) {

        if (!isset($_POST["csrf_token"]) || $_SESSION["csrf_token"]!=$_POST["csrf_token"]) {
			echo "Security Error";
			exit();
		}

        $sql_r="INSERT INTO websecproj.comments (PostID, PostedBy, CommentBody) VALUES (:postid, :username, :body)";
		//      echo $sql_r;
		$sth=$dbh->prepare($sql_r);

        $sth->bindParam(":postid", $_POST["PostID"]);
		$sth->bindParam(":body", $_POST["comment"]);
		$sth->bindParam(":username", $_SESSION["name"]);
        $sth->execute();
        //		var_dump($sth->errorInfo());

    }

    $sth=$dbh->query("SELECT * FROM websecproj.posts WHERE PostID = " . $_POST["PostID"]);

    if($row = $sth->fetch( PDO::FETCH_ASSOC)) {
        echo "<h1>" . htmlentities($row['PostHeader']) . "</h1>";
        echo "<p style=\"font-size: 12px;color: blue\"> Posted by: " . htmlentities($row['PostedBy']) . "</p>";
        echo " <p style=\"font-size: 25px\">" . htmlentities($row['PostBody']) . "</p><br><hr>";
    }
    
    $sth=$dbh->query("SELECT * FROM websecproj.comments ORDER BY PostedOn ASC");
	while($row = $sth->fetch( PDO::FETCH_ASSOC )){
		echo "<p style=\"font-size: 11px;color: blue\"> Posted by: " . htmlentities($row['PostedBy']) . "</p>";
		echo htmlentities($row['CommentBody']) . "<br><br><hr>";
    }

    $_SESSION["csrf_token"]=hash("sha256",rand().rand()."secret");
?>

<form method="post">
    <br> <br>
    <h1>Create a new comment:</h1>

    <textarea name="comment" style="width: 700px;height: 80px" maxlength="2000"></textarea> <br>

    <input type="submit" value="Make a Comment"/>

    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"] ?>">
    <input type="hidden" name="PostID" value="<?php echo $_POST["PostID"]?>">
</form>