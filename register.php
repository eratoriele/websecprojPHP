<?php
session_start();
include "database.php";
include "include.php";
gen_header();
?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php
if(isset($_SESSION["name"]))
{
    LoggedIn(0);
}
else
{
    if(isset($_POST["username"]) && isset($_POST["password"]))
        DoLogin();
    else
        ShowLoginForm();
}
function ShowLoginForm()
{
    ?>
    <form class="form-signin" method="post">
        <h2 class="form-signin-heading">Register</h2>
        <label for="inputUser" class="sr-only">Username</label>
        <input type="text" id="inputUser" name="username" class="form-control" placeholder="Username" pattern="^[a-zA-Z0-9]+$" required autofocus>
        Only alphanumeric characters <br>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" pattern="^[a-zA-Z0-9]+$" required>
        Only alphanumeric characters <br>
        <h1>How do you view an object?</h1>
        <input type="radio" name="q1" value="0" required> Tree rather than a forest <br>
        <input type="radio" name="q1" value="1" required> Forest rather than a tree <br>
        <h1>When making decisions, what is your train of thought?</h1>
        <input type="radio" name="q2" value="0" required> Logical <br>
        <input type="radio" name="q2" value="1" required> Emotional <br>
        <h1>Where does the energy flow?</h1>
        <input type="radio" name="q3" value="0" required> Outwards <br>
        <input type="radio" name="q3" value="1" required> Inwards <br>
        <h1>How is your life style?</h1>
        <input type="radio" name="q4" value="0" required> Straightforward <br>
        <input type="radio" name="q4" value="1" required> Embracing <br>
        <div class="g-recaptcha" data-sitekey="6LeuJ54UAAAAAKTGoUPSwBhvH7_6gyM33SFFxSOB"></div> <br/>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>

<?php
}

function DoLogin()
{
    require_once('recaptchalib.php');

    $response = $_POST["g-recaptcha-response"];
    $verify = new recaptchalib("6LeuJ54UAAAAAO58XWYTLN8iSBVM1HzD5YH0FNac", $response);

    if ($verify->isValid()){

        if (($_POST["q1"] == "0" || $_POST["q1"] == "1") && ($_POST["q2"] == "0" || $_POST["q2"] == "1") &&
            ($_POST["q3"] == "0" || $_POST["q3"] == "1") && ($_POST["q4"] == "0" || $_POST["q4"] == "1") &&
            ctype_alnum($_POST["username"] && ctype_alnum($_POST["password"]))) {

            $groups = $_POST["q1"] . $_POST["q2"] . $_POST["q3"] . $_POST["q4"];
        }
        else{
            echo "Please input correctly<br>";
            echo '<a href="./">Go back</a><br>';
            exit();
        }

        $hpw = password_hash($_POST["password"], PASSWORD_DEFAULT);

        global $dbh;
        $sql_query="INSERT INTO websecproj.users (Username, HashedPassword, Groups) VALUES (:user, :hpw, :groups)";

        $sth=$dbh->prepare($sql_query);

        $sth->bindParam(":user", $_POST["username"]);
        $sth->bindParam(":hpw", $hpw);
        $sth->bindParam(":groups", $groups);
        $sth->execute();
        
        $_SESSION["name"]=$_POST["username"];
        $_SESSION["groups"] = $groups;
        LoggedIn(0);

    }
}


?>
</div>

</body>