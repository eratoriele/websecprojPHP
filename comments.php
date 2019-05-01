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
        
        $image_dir = NULL;
		if ($_FILES["fileToUpload"]["name"] != '') {		// upload an image if there is any provided
			require "upload.php";
			$image_dir = $target_file;
		}

        $sql_r="INSERT INTO websecproj.comments (PostID, PostedBy, CommentBody, Image) VALUES (:postid, :username, :body, :image)";
		
		$sth=$dbh->prepare($sql_r);

        $sth->bindParam(":postid", $_GET["PostID"]);
		$sth->bindParam(":username", $_SESSION["name"]);
        $sth->bindParam(":body", $_POST["comment"]);
        $sth->bindParam(":image", $image_dir);
        $sth->execute();
        //		var_dump($sth->errorInfo());

    }

    $sql_r = ("SELECT * FROM websecproj.posts WHERE PostID = :postid AND Deleted = false");

    $sth=$dbh->prepare($sql_r);

    $sth->bindParam(":postid", $_GET["PostID"]);

    $sth->execute();

    if($row = $sth->fetch( PDO::FETCH_ASSOC)) {

        if ($row['GroupID'] !== $_SESSION["groups"]){           // If the post is outside of user's group
            echo "<h1>This is not a post in your group</h1>";
            echo '<a href="./posts.php">Go back</a><br>';
            exit();
        }
    
        echo "<h1>" . htmlentities($row['PostHeader']) . "</h1>";
        echo "<p style=\"font-size: 12px;color: #3B4D45\"> Posted by: <a href=\"./user_profile.php?User=" .
            $row['PostedBy'] . "\">" . htmlentities($row['PostedBy']) .
             "</a> on " . htmlentities($row['PostedOn']) . "</p>";
        if ($row['Image'] != NULL)
            echo "<img height=\"600\" src=" . htmlentities($row['Image']) . "><br>";
        echo " <p style=\"font-size: 25px\">" . htmlentities($row['PostBody']) . "</p><br><hr>";
    }
    else {
        echo "<h1>Post you are looking for is deleted, or does not exist</h1>";
        echo '<a href="./posts.php">Go back</a><br>';
        exit();
    }
    
    $sth=$dbh->query("SELECT * FROM websecproj.comments WHERE PostID = " . $_GET["PostID"] . " ORDER BY PostedOn ASC");
	while($row = $sth->fetch( PDO::FETCH_ASSOC)) {
		echo "<p style=\"font-size: 11px;color: #3B4D45\"> Posted by: <a href=\"./user_profile.php?User=" .
				$row['PostedBy'] . "\">" . htmlentities($row['PostedBy']) . 	
				"</a> on " . htmlentities($row['PostedOn']) . "</p>";		// Hyper text on name to user's profile
        
        if ($row['Image'] != NULL)
            echo "<img height=\"400\" src=" . htmlentities($row['Image']) . "><br>";

        echo htmlentities($row['CommentBody']) . "<br><br><hr>";
        
    }

    $_SESSION["csrf_token"]=hash("sha256",rand().rand());
    $action_url = "./comments.php?PostID=" . $_GET["PostID"];
?>

<form action="<?php echo $action_url ?>" method="post" enctype="multipart/form-data">
    <br> <br>
    <h1>Create a new comment:</h1>

    <textarea name="comment" style="width: 700px;height: 80px" maxlength="2000"></textarea> <br>

    <div class="g-recaptcha" data-sitekey="6LeuJ54UAAAAAKTGoUPSwBhvH7_6gyM33SFFxSOB"></div> <br/>

    Select image to upload: (JPG, JPEG, PNG) (Optional) (File name shouldn't be longer than 200 chars)
	<input type="file" name="fileToUpload" id="fileToUpload">

    <input type="submit" value="Make a Comment"/>

    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"] ?>">
</form>