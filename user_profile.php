<?php
    session_start();
	if(!isset($_SESSION["name"])) {
		echo "Please log in<br>";						// If tried to be accesed without logging in
		echo '<a href="./">Go back</a><br>';            // TODO Maybe delete this later?
		exit();
	}
    include "database.php";
	include "include.php";
    gen_header();
	LoggedIn(-1);
?>

<?php

    $sql_r="SELECT * FROM websecproj.users" . 
            " WHERE Username = :user"; 

    $sth=$dbh->prepare($sql_r);
    $sth->bindParam(":user", $_GET["User"]);
    $sth->execute();
    //      var_dump($sth->errorInfo());
    
    if($row = $sth->fetch( PDO::FETCH_ASSOC)) {

        echo "<h1> User " . htmlentities($row["Username"]) . "</h1>";
        echo "<h3> Group: " . htmlentities($row["Groups"]) . "</h3>";
        if ($row["admin"]) {
            echo "<p style=\"font-size: 20px; color: blue\">This user is an admin</p>";
        }

        // List the posts this user has made

        echo "<hr><p style=\"font-size: 25px;color: blue\">Last 5 posts made by user:</p>";                  
        $sql_r_posts="SELECT * FROM websecproj.posts" . 
                    " WHERE PostedBy = :user AND Deleted = false" . 
                    " ORDER BY PostedOn DESC" .
                    " LIMIT 5"; 

        $sth_posts=$dbh->prepare($sql_r_posts);
        $sth_posts->bindParam(":user", $_GET["User"]);
        $sth_posts->execute();

        echo "<hr>";

        $flag_user_made_post = false;

        while($posts_row = $sth_posts->fetch( PDO::FETCH_ASSOC)) {

            $flag_user_made_post = true;

            echo "<a href=\"./comments.php?PostID=" .  htmlentities($posts_row['PostID']) . "\">" . 
			    "<h3>" . htmlentities($posts_row['PostHeader']) . "</h3></a>";		// Makes the header hyper text as well
            if ($posts_row['Image'] != NULL)
                echo "<img height=\"200\" src=" . htmlentities($posts_row['Image']) . "><br>";
    
            // If the body is too long, don't show all the text on the screen at once
            if (strlen($posts_row['PostBody']) > 100)
                echo  substr(htmlentities($posts_row['PostBody']), 1, 100) . "...<br><br>";
            else
                echo htmlentities($posts_row['PostBody']) . "<br><br>";


            echo "<hr>";
        }
        if (!$flag_user_made_post) {        // If they haven't made any posts yet
            echo "<h2>This user hasn't made any posts yet :(</h2>";
        }

        // List the comments this user has made
        
        echo "<hr><p style=\"font-size: 25px;color: blue\">Last 5 comments made by user:</p>";                  
        $sql_r_comments="SELECT * FROM websecproj.comments" . 
                        " WHERE PostedBy = :user AND Deleted = false" . 
                        " ORDER BY PostedOn DESC" .
                        " LIMIT 5"; 

        $sth_comments=$dbh->prepare($sql_r_comments);
        $sth_comments->bindParam(":user", $_GET["User"]);
        $sth_comments->execute();

        echo "<hr>";

        $flag_user_made_comments = false;

        while($comments_row = $sth_comments->fetch( PDO::FETCH_ASSOC)) {

            $flag_user_made_comments = true;

            //Show the post the comment is made in
            $sql_r_comment_post="SELECT * FROM websecproj.posts" . 
                        " WHERE PostId = :postid";

            $sth_comment_post=$dbh->prepare($sql_r_comment_post);
            $sth_comment_post->bindParam(":postid", $comments_row['PostID']);
            $sth_comment_post->execute();

            // list the post
            if ($comment_post_row = $sth_comment_post->fetch( PDO::FETCH_ASSOC)) {

                echo "Post: <a href=\"./comments.php?PostID=" .  htmlentities($comment_post_row['PostID']) . "\">" . 
			         htmlentities($comment_post_row['PostHeader']) . "</a><br>";		// Makes the header hyper text as well

            }
    
            // If the body is too long, don't show all the text on the screen at once
            if (strlen($comments_row['CommentBody']) > 100)
                echo "Comment: " . substr(htmlentities($comments_row['CommentBody']), 1, 100) . "...<br><br>";
            else
                echo "Comment: " . htmlentities($comments_row['CommentBody']) . "<br><br>";


            echo "<hr>";
        }        
        if (!$flag_user_made_comments) {        // If they haven't made any posts yet
            echo "<h2>This user hasn't made any comments yet :(</h2>";
        }

    }
    else {
        echo "<h1> User you are looking for does not exist </h1>";
    }


?>