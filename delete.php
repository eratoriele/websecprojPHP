<?php
    include "database.php";
	if (isset($_POST["delete_post"])) {             // if a post is being deleted,

        $sql_r="UPDATE websecproj.posts SET Deleted = 1 WHERE PostID = :postid";
		$sth=$dbh->prepare($sql_r);

		$sth->bindParam(":postid", $_POST["delete_post"]);
        $sth->execute();
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);        // TODO: Get a better way of doing this,
                                                                // If have to, delete this file and add functions to other files
                                                                // TODO: Just delete this and use a require
    }

    if (isset($_POST["delete_comment"])) {          // if a comment is being deleted

        $sql_r="UPDATE websecproj.comments SET Deleted = 1 WHERE CommentID = :commentid";
		$sth=$dbh->prepare($sql_r);

		$sth->bindParam(":commentid", $_POST["delete_comment"]);
        $sth->execute();
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);        // TODO: Get a better way of doing this,
                                                                // If have to, delete this file and add functions to other files
                                                                // TODO: Just delete this and use a require
    }
?>