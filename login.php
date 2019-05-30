<?php
session_start();
include "database.php";
include "include.php";
gen_header();
?>
<body style="background-color:#dbc6a8">
<?php

if(isset($_SESSION["name"])) {
    LoggedIn(0);                        // If already logged in the current session, i.e. didn't "log out", directly enter
}
else {
    if(isset($_POST["username"]) and isset($_POST["password"]))
        DoLogin();                                                  // instead of creating a new file, logging in is
    else                                                            // in the same file
        ShowLoginForm();
}
function ShowLoginForm() {
    ?>
    <form class="form-signin" method="post">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="inputUser" class="sr-only">Username</label>
        <input type="text" id="inputUser" name="username" maxlength="20" minlength="4" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" maxlength="20" minlength="4" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>

<?php
}

function DoLogin() {

    if (!isset($_SESSION["brute_prevent"]))         // If this is the first time they are logging in, set this to 0
        $_SESSION["brute_prevent"] = 0;             // Aim of this is to prevent brute force attacks
                                                    // by forcing the attacker out

    else if ($_SESSION["brute_prevent"] >= 5) {     // If they have failed 5 times, ban the session for a while
        echo "<h1>Too many wrong logins</h1>";
        $_SESSION["brute_date"] = date_create(date("Y-m-d h:i:s"));
    }
    if (isset($_SESSION["brute_date"]) &&
        date_diff($_SESSION["brute_date"], date_create(date("Y-m-d h:i:s")))->format('%i') < 20) {
        // If the attacker attacked at least 5 times, make them wait for 20 minutes until they can try to login
        // If the attacker can figure how bad this code is, they can just delete their session cookie
        echo "<h1>You have too many wrong inputs. Please try again in 20 minutes.</h1>";
        exit();
    }

    if (strlen($_POST["username"]) < 4 || strlen($_POST["username"]) > 20 ||
        strlen($_POST["password"]) < 4 || strlen($_POST["password"]) > 20) {
            echo "relogin again";
            echo '<a href="./">Go back</a><br>';
            exit();
        }

    global $dbh;
    $sql_query="SELECT * FROM websecproj.users WHERE username=:user";       // username is primary key
    $sth=$dbh->prepare($sql_query);

    $sth->bindParam(":user", $_POST["username"]);
    $sth->execute();
    $result=$sth->fetchAll();

    if(!empty($result) && password_verify($_POST["password"], $result[0]["HashedPassword"]) && $result[0]["Email_Verified"] == "1") {
        $_SESSION["name"] = $result[0]["Username"];
        $_SESSION["groups"] = $result[0]["Groups"];
        $_SESSION["admin"] = $result[0]["admin"] == "1" ? true : false;             // Define variables
        $_SESSION["lastPost"] = $result[0]["LastPost"];

        LoggedIn(-1);
    }
    else {                              // Failed to log in
        $_SESSION["brute_prevent"]++;
        ShowLoginForm();
    }

}


?>
</div>

</body>