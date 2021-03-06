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
    LoggedIn(2);
?>

<?php

    if (isset($_POST["comment"]) && $_POST["comment"] != NULL && strlen($_POST["comment"]) <= 2000 && checkLastPost()) {                 // If a comment is made


        if (!isset($_POST["csrf_token"]) || $_SESSION["csrf_token"]!=$_POST["csrf_token"]) {     // Token is for XSS attacks, together with captcha
            echo "Security Error";
            session_destroy();
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

        $_SESSION["lastPost"] = date("Y-m-d h:i:s");

    }

    $sql_r = ("SELECT * FROM websecproj.posts WHERE PostID = :postid AND Deleted = false");

    $sth=$dbh->prepare($sql_r);

    $sth->bindParam(":postid", $_GET["PostID"]);

    $sth->execute();

    if($row = $sth->fetch( PDO::FETCH_ASSOC)) {

        /*
        if ($row['GroupID'] !== $_SESSION["groups"]){           // If the post is outside of user's group
            echo "<h1>This is not a post in your group</h1>";
            echo '<a href="./posts.php">Go back</a><br>';
            exit();
        }*/
        echo "<div class=\"jumbotron text-center\">";

        echo "<h1>" . htmlentities($row['PostHeader']) . "</h1>";
        echo "<p style=\"font-size: 12px;color: #3B4D45\"> Posted by: <a href=\"./user_profile.php?User=" .
            $row['PostedBy'] . "\">" . htmlentities($row['PostedBy']) .
             "</a> on " . htmlentities($row['PostedOn']) . "</p>";
        if ($row['Image'] != NULL)
            echo "<img height=\"600\" src=" . htmlentities($row['Image']) . "><br><br>";
        echo " <p style=\"font-size: 25px\">" . htmlentities($row['PostBody']) . "</p>";

        echo "</div>";
    }
    else {
        echo "<h1>Post you are looking for is deleted, or does not exist</h1>";
        echo '<a href="./posts.php">Go back</a><br>';
        exit();
    }

    $sql_r = "SELECT comments.CommentID, comments.PostedBy, comments.CommentBody, comments.Image, comments.PostedOn, users.Groups" .
            " FROM websecproj.comments INNER JOIN websecproj.users ON comments.PostedBy = users.Username" .
            " WHERE PostID = :postid AND Deleted = false ORDER BY PostedOn ASC";
    /* Get all the comments from that post,
     * Also get the group info of the user from
     * users table, as the groups are not stored in the comments.
     * TODO: Do this for posts as well for less duplicate data
     */    

    $sth = $dbh->prepare($sql_r);
    $sth->bindParam(":postid", $_GET["PostID"]);
    $sth->execute();

    echo "<div class=\"container\">";
    echo "<div class=\"row\">";
  

	while($row = $sth->fetch( PDO::FETCH_ASSOC)) {

        echo "<div class=\"col-sm-6\">";

        echo "<div class=\"card\">"; 
            if ($row['Image'] != NULL)
                echo "<img  src=\"" . htmlentities($row['Image']) . "\" alt=\"Image\" style=\"width:100%\">";
            echo "<div class=\"container\">";
                echo "<h4><b>" . htmlentities($row['CommentBody']) . "</b></h4>";
                echo "<p style=\"font-size: 11px;color: #3B4D45\"> Posted by: <a href=\"./user_profile.php?User=" .
				    $row['PostedBy'] . "\">" . htmlentities($row['PostedBy']) . 	
                    "</a> on " . htmlentities($row['PostedOn']) .
                    " on group " . htmlentities($row['Groups']) .  "</p>";		// Hyper text on name to user's profile
                if ($_SESSION["name"] === $row['PostedBy'] || $_SESSION["admin"]) {		    // Only the post owner or an admin can delete posts
                    echo "<form action=\"./delete.php\" method=\"post\">";					// If a post is deleted, so are all the comments. This will be handled at database
                                                                                            // ^ This may be redundant as you can't see comments without post anyway
                                                                                            // TODO: you can still see them in your profile tho
                        echo "<br><button type=\"submit\" class=\"btn btn-danger\">Delete</button>";
                        echo "<input type=\"hidden\" name=\"delete_comment\" value=\"" . $row['CommentID'] . "\"></form>";      // TODO make this less retarded
                }
        echo "</div></div><br><br></div>";
/*
		echo "<p style=\"font-size: 11px;color: #3B4D45\"> Posted by: <a href=\"./user_profile.php?User=" .
				$row['PostedBy'] . "\">" . htmlentities($row['PostedBy']) . 	
                "</a> on " . htmlentities($row['PostedOn']) .
                " on group " . htmlentities($row['Groups']) .  "</p>";		// Hyper text on name to user's profile
        
                1
        if ($row['Image'] != NULL)
            echo "<img height=\"400\" src=" . htmlentities($row['Image']) . "><br><br>";

            1
        echo htmlentities($row['CommentBody']);

        if ($_SESSION["name"] === $row['PostedBy'] || $_SESSION["admin"]) {		    // Only the post owner or an admin can delete posts
            echo "<form action=\"./delete.php\" method=\"post\">";					// If a post is deleted, so are all the comments. This will be handled at database
                                                                                    // ^ This may be redundant as you can't see comments without post anyway
                                                                                    // TODO: you can still see them in your profile tho
                echo "<br><button type=\"submit\" class=\"btn btn-danger\">Delete</button>";
                echo "<input type=\"hidden\" name=\"delete_comment\" value=\"" . $row['CommentID'] . "\"></form>";      // TODO make this less retarded
        }*/

        //echo "<hr></div>";
    }
    echo "</div></div>";

    $_SESSION["csrf_token"]=hash("sha256",rand().rand());
    $action_url = "./comments.php?PostID=" . $_GET["PostID"];
?>

<form action="<?php echo $action_url ?>" method="post" enctype="multipart/form-data">
    <br> <br>
    <h1>Create a new comment:</h1>

    <textarea name="comment" style="width: 700px;height: 80px" maxlength="2000"></textarea> <br>

    Select image to upload: (JPG, JPEG, PNG) (Optional) (File name shouldn't be longer than 200 chars)
	<input type="file" name="fileToUpload" id="fileToUpload">

    <input type="submit" value="Make a Comment"/>

    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"] ?>">
</form>
</body>