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
	
    if(isset($_POST["header"]) && $_POST["header"] != NULL && strlen($_POST["header"]) >=5 && strlen($_POST["header"]) <=100) {			// If a post is made


        // Token is for XSS attacks
        if (!isset($_POST["csrf_token"]) || $_SESSION["csrf_token"]!=$_POST["csrf_token"]) {		
            echo "Security Error";
            echo '<a href="./">Go back</a><br>';
            exit();
        }
        // If body is too long
        if (!isset($_POST["body"]) || strlen($_POST["body"]) > 2000){
			echo "Post body is too long";
			echo '<a href="./">Go back</a><br>';
			exit();
		}

        $image_dir = NULL;
        if ($_FILES["fileToUpload"]["name"] != '') {		// upload an image if there is any provided
            require "upload.php";
            $image_dir = $target_file;
        }
        
        // Might delete GroupID from here so that database handles it
        $sql_r="INSERT INTO websecproj.posts (CommunityName, PostedBy, GroupID, PostHeader, PostBody, Image)" .
                    " VALUES (:communityname, :username, :groups, :header, :body, :image)";
        
        $sth=$dbh->prepare($sql_r);

        $sth->bindParam(":communityname", $_GET["Community"]);
        $sth->bindParam(":username", $_SESSION["name"]);
        $sth->bindParam(":groups", $_SESSION["groups"]);
        $sth->bindParam(":header", $_POST["header"]);
        $sth->bindParam(":body", $_POST["body"]);
        $sth->bindParam(":image", $image_dir);
        $sth->execute();
        //		var_dump($sth->errorInfo());

        $_SESSION["lastPost"] = date("Y-m-d h:i:s");
    }

    $sql_r = "SELECT * FROM websecproj.posts" . 
            " WHERE CommunityName = :communityname AND Deleted = false" .
            " AND GroupID = :groupid" . 	// Not a community post, not deleted and in the same group
            " ORDER BY PostedOn DESC" . 
            " LIMIT " . ($page * 5) . " , 5";
            /* Order by newest post on top. Might change it so comments also push posts higher 
                * Get EVERY post that is not deleted and in the same group.
                * TODO: Add neighbouring groups	*/

    $sth = $dbh->prepare($sql_r);
    $sth->bindParam(":communityname", $_GET["Community"]);
    $sth->bindParam(":groupid", $_SESSION["groups"]);
    $sth->execute();

    $i = 0;		// Counts how many entries in the query, and decides to put next page button or not

    while($row = $sth->fetch( PDO::FETCH_ASSOC )){				// I don't know why i bothered with this instead of
                                                                // just closing the php tag, but i will keep it in
        $i++;
        
        echo "<div class=\"jumbotron text-center\">";
		echo "<a href=\"./comments.php?PostID=" .  htmlentities($row['PostID']) . "\">" . 
			"<h2>" . htmlentities($row['PostHeader']) . "</h2></a>";		// Makes the header hyper text as well

		echo "<p style=\"font-size: 11px;color: #3B4D45\"> Posted by: <a href=\"./user_profile.php?User=" .
				$row['PostedBy'] . "\">" . htmlentities($row['PostedBy']) . 	
				"</a> on " . htmlentities($row['PostedOn']) . " on group " . htmlentities($row['GroupID']) . "</p>";		// Hyper text on name to user's profile

        
        if ($row['Image'] != NULL)
            echo "<img height=\"400\" src=" . htmlentities($row['Image']) . "><br><br>";


		// If the body is too long, don't show all the text on the screen at once
		if (strlen($row['PostBody']) > 300)
			echo substr(htmlentities($row['PostBody']), 1, 300) . "...<br><br>";
		else
			echo htmlentities($row['PostBody']) . "<br><br>";
	
		echo "<div class=\"container\">";
		echo "<div class=\"row\">";

		echo "<div class=\"text-center\">";  
		echo "<form action=\"./communitycomments.php\" method=\"get\">";		// ew
			echo "<button type=\"submit\" class=\"btn btn-default\">See Comments</button>";
			echo "<input type=\"hidden\" name=\"PostID\" value=\"" . htmlentities($row['PostID']) . "\"></form>";

		echo "</div><div class=\"text-right\">";  
		if ($_SESSION["name"] === $row['PostedBy'] || $_SESSION["admin"]){		// Only the post owner or an admin can delete posts
		echo "<form action=\"./delete.php\" method=\"post\">";					// If a post is deleted, so are all the comments.
			echo "<br><button type=\"submit\" class=\"btn btn-danger\">Delete</button>";
			echo "<input type=\"hidden\" name=\"delete_post\" value=\"" . htmlentities($row['PostID']) . "\"></form>"; // TODO make this less retarded

        
/*        echo "<a href=\"./communitycomments.php?PostID=" .  htmlentities($row['PostID']) . "\">" . 
            "<h2>" . htmlentities($row['PostHeader']) . "</h2></a>";		// Makes the header hyper text as well

        echo "<p style=\"font-size: 11px;color: #3B4D45\"> Posted by: <a href=\"./user_profile.php?User=" .
                $row['PostedBy'] . "\">" . htmlentities($row['PostedBy']) . 	
                "</a> on " . htmlentities($row['PostedOn']) . "</p>";		// Hyper text on name to user's profile

        if ($row['Image'] != NULL)
            echo "<img height=\"400\" src=" . htmlentities($row['Image']) . "><br>";

        // If the body is too long, don't show all the text on the screen at once
        if (strlen($row['PostBody']) > 100)
            echo substr(htmlentities($row['PostBody']), 1, 100) . "...<br><br>";
        else
            echo htmlentities($row['PostBody']) . "<br><br>";
        
        echo "<form action=\"./communitycomments.php\" method=\"get\">";		// ew
            echo "<input type=\"submit\" value=\"See Comments\">";
            echo "<input type=\"hidden\" name=\"PostID\" value=\"" . htmlentities($row['PostID']) . "\"></form>";

        if ($_SESSION["name"] === $row['PostedBy'] || $_SESSION["admin"]){		// Only the post owner or an admin can delete posts
        echo "<form action=\"./delete.php\" method=\"post\">";					// If a post is deleted, so are all the comments. This is handleed at database
            echo "<input type=\"submit\" value=\"Delete\">";
            echo "<input type=\"hidden\" name=\"delete_post\" value=\"" . $row['PostID'] . "\"></form>"; // TODO make this less retarded
        */  }

        echo "</div></div></div></div>";

    }
    
    echo "<form action=\"./community.php\" method=\"get\">";
        if ($page !== 0)
            echo "<button type=\"submit\" class=\"btn btn-primary\">Previous page</button>";
        echo "<input type=\"hidden\" name=\"page\" value=\"" . ($page - 1) . "\" >";
        echo "<input type=\"hidden\" name=\"Community\" value=\"" . $_GET["Community"] . "\" ></form>";

    echo "<form action=\"./community.php\" method=\"get\">";
        if ($i === 5)
            echo "<button type=\"submit\" class=\"btn btn-primary\">Next page</button>";
        echo "<input type=\"hidden\" name=\"page\" value=\"" . ($page + 1) . "\" >";
        echo "<input type=\"hidden\" name=\"Community\" value=\"" . $_GET["Community"] . "\" ></form>";


    $_SESSION["csrf_token"]=hash("sha256",rand().rand());
?>
    
    
<form method="post" enctype="multipart/form-data">	
    <br>
    Create a new post:<br>
    Post Header:<br>
    <input name="header" style="width: 700px;height: 35px" maxlength="100" minlength="5">

    <br>Post Body:<br>
    <textarea name="body" style="width: 700px;height: 80px" maxlength="2000"></textarea><br>

    Select image to upload: (JPG, JPEG, PNG) (Optional) (File name shouldn't be longer than 200 chars)
    <input type="file" name="fileToUpload" id="fileToUpload">

    <input type="submit" value="Make a post">
    
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"] ?>">
</form>
</div></body>