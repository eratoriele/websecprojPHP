<?php
session_start();
include "database.php";
include "include.php";
gen_header();
?>
<?php
if(isset($_SESSION["name"]))
{
    LoggedIn(0);
}
else
{
    if(isset($_GET["username"]) and isset($_GET["password"]))
        DoLogin();
    else
        ShowLoginForm();
}
function ShowLoginForm()
{
    ?>
    <form class="form-signin">
        <h2 class="form-signin-heading">Please Register</h2>
        <label for="inputUser" class="sr-only">Username</label>
        <input type="text" id="inputUser" name="username" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>

<?php
}

function DoLogin()
{
    $hpw = password_hash($_GET["password"], PASSWORD_DEFAULT);

    global $dbh;
    $sql_query="INSERT INTO websecproj.users (Username, HashedPassword, Groups) VALUES (:user, :hpw, '1111')";

    $sth=$dbh->prepare($sql_query);

    $sth->bindParam(":user", $_GET["username"]);
    $sth->bindParam(":hpw", $hpw);
    $sth->execute();
    
    $_SESSION["name"]=$_GET["username"];
    LoggedIn(0);
}


?>
</div>

</body>