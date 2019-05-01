<?php

    session_start();
    if(!isset($_SESSION["name"])) {
        echo "Please log in<br>";
        echo '<a href="./">Go back</a><br>';
        exit();
    }
    include "database.php";
    include "include.php";
    gen_header();
    LoggedIn(-1);
?>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php

    if (isset($_POST["comment"])) {                 // If a comment is made

        require_once('recaptchalib.php');

        $response = $_POST["g-recaptcha-response"];
        $verify = new recaptchalib("6LeuJ54UAAAAAO58XWYTLN8iSBVM1HzD5YH0FNac", $response);

        if (!isset($_POST["csrf_token"]) || $_SESSION["csrf_token"]!=$_POST["csrf_token"] || !$verify->isValid()) {     // Token is for XSS attacks, together with captcha
            echo "Security Error";
            echo '<a href="./">Go back</a><br>';
			exit();
		}

        $sql_r="INSERT INTO websecproj.comments (PostID, PostedBy, CommentBody) VALUES (:postid, :username, :body)";
		
		$sth=$dbh->prepare($sql_r);

        $sth->bindParam(":postid", $_GET["PostID"]);
		$sth->bindParam(":body", $_POST["comment"]);
		$sth->bindParam(":username", $_SESSION["name"]);
        $sth->execute();
        //		var_dump($sth->errorInfo());

    }

    $sth=$dbh->query("SELECT * FROM websecproj.posts WHERE PostID = " . $_GET["PostID"] . " AND deleted = false");

    if($row = $sth->fetch( PDO::FETCH_ASSOC)) {
        echo "<h1>" . htmlentities($row['PostHeader']) . "</h1>";
        echo "<p style=\"font-size: 12px;color: blue\"> Posted by: " . htmlentities($row['PostedBy']). " on " . htmlentities($row['PostedOn']) . "</p>";
        echo " <p style=\"font-size: 25px\">" . htmlentities($row['PostBody']) . "</p><br><hr>";
    }
    
    $sth=$dbh->query("SELECT * FROM websecproj.comments WHERE PostID = " . $_GET["PostID"] . " ORDER BY PostedOn ASC");
	while($row = $sth->fetch( PDO::FETCH_ASSOC)) {
		echo "<p style=\"font-size: 11px;color: blue\"> Posted by: " . htmlentities($row['PostedBy']) . " on " . htmlentities($row['PostedOn']) . "</p>";
		echo htmlentities($row['CommentBody']) . "<br><br><hr>";
    }

    $_SESSION["csrf_token"]=hash("sha256",rand().rand());
    $action_url = "./comments.php?PostID=" . $_GET["PostID"];
?>

<form action="<?php echo $action_url ?>" method="post">
    <br> <br>
    <h1>Create a new comment:</h1>

    <textarea name="comment" style="width: 700px;height: 80px" maxlength="2000"></textarea> <br>

    <div class="g-recaptcha" data-sitekey="6LeuJ54UAAAAAKTGoUPSwBhvH7_6gyM33SFFxSOB"></div> <br/>

    <input type="submit" value="Make a Comment"/>

    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"] ?>">
</form>