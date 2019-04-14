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
        <h2 class="form-signin-heading">Please Register</h2>
        <label for="inputUser" class="sr-only">Username</label>
        <input type="text" id="inputUser" name="username" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
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

        $hpw = password_hash($_POST["password"], PASSWORD_DEFAULT);

        global $dbh;
        $sql_query="INSERT INTO websecproj.users (Username, HashedPassword, Groups) VALUES (:user, :hpw, '1111')";

        $sth=$dbh->prepare($sql_query);

        $sth->bindParam(":user", $_POST["username"]);
        $sth->bindParam(":hpw", $hpw);
        $sth->execute();
        
        $_SESSION["name"]=$_POST["username"];
        LoggedIn(0);

    }
}


?>
</div>

</body>