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

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<form method="post">
    Search a community:
    <input name="search" style="width: 700px;height: 35px" maxlength="100" minlength="1">
    <input type="submit" value="Search">
</form>

<?php

    if (isset($_POST["search"]) && $_POST["search"] != NULL) {              // If a search is made

        $sql_r = "SELECT * FROM websecproj.community" .
                " WHERE CommunityName = :communityname";

        $sth=$dbh->prepare($sql_r);
		$sth->bindParam(":communityname", $_POST["search"]);
        $sth->execute();
        //      var_dump($sth->errorInfo());

        $searchfound = false;
        
        while($row = $sth->fetch( PDO::FETCH_ASSOC )){

            $searchfound = true;

            echo "<a href=\"./community.php?Community=" .  htmlentities($row['CommunityName']) . "\">" . 
            "<h2>" . htmlentities($row['CommunityName']) . "</h2></a>";		// Makes the header hyper text as well
            
            if (strlen($row['CommunityBio']) > 100)
			    echo substr(htmlentities($row['CommunityBio']), 1, 100) . "...<br><br><hr>";
		    else
			    echo htmlentities($row['CommunityBio']) . "<br><br><hr>";

        }

        if (!$searchfound) {            // If no community found in that name, ask to create one

            echo "<form method=\"post\"> ";
                echo "<h3>Create a community named " . $_POST["search"] . "!</h3><br>";
                echo "Community bio:<br>";
                echo "<textarea name=\"bio\" style=\"width: 700px;height: 80px\" maxlength=\"2000\"></textarea><br>";
                echo "<div class=\"g-recaptcha\" data-sitekey=\"". $captcha_public . "\"></div> <br/>";
                echo "<input type=\"submit\" value=\"Create Community!\">";
                echo "<input type=\"hidden\" name=\"createc\" value=\"" . $_POST["search"] . "\"></form>";

        }
    }

    if (isset($_POST["createc"]) && $_POST["createc"] != NULL) {            // If a new community is being created

        require_once('recaptchalib.php');

        $response = $_POST["g-recaptcha-response"];
        $verify = new recaptchalib($captcha_secret, $response);

        if ($verify->isValid()) {             // If captcha is correct

            $sql_r = "INSERT INTO websecproj.community (CommunityName, CreatedBy, CommunityBio)" .
                    " VALUES (:communityname, :createdby, :communitybio)"; 
                    
            $sth=$dbh->prepare($sql_r);

            $sth->bindParam(":communityname", $_POST["createc"]);
            $sth->bindParam(":createdby", $_SESSION["name"]);
            $sth->bindParam(":communitybio", $_POST["bio"]);
            $sth->execute();
            //      var_dump($sth->errorInfo());

            /*echo "<a href=\"./community.php?Community=" .  htmlentities($row['CommunityID']) . "\">" . 
                "<h2>" . htmlentities($row['CommunityName']) . "</h2></a>";	*/                  // Forward to newly created

        }
    }

?>

</body>