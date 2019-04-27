<?php
    session_start();
    include "database.php";
	if (isset($_POST["delete_post"])) {             // if a post is being deleted,

        $sql_r="UPDATE websecproj.posts SET Deleted = 1 WHERE PostID = :postid";
		$sth=$dbh->prepare($sql_r);

		$sth->bindParam(":postid", $_POST["delete_post"]);
        $sth->execute();
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);        // TODO: Get a better way of doing this,
                                                                // If have to, delete this file and add functions to other files
    }
?>