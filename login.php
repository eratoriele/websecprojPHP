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
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="inputUser" class="sr-only">Email address</label>
        <input type="text" id="inputUser" name="username" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>

<?php
}

function DoLogin()
{
    global $dbh;
    $sql_query="select * from websecproj.users where username=:user";

    $sth=$dbh->prepare($sql_query);

    $sth->bindParam(":user", $_GET["username"]);
    $sth->execute();
    $result=$sth->fetchAll();

    if(!empty($result) && password_verify($_GET["password"], $result[0]["HashedPassword"])) {
        $_SESSION["name"]=$result[0]["Username"];
        $_SESSION["groups"] = $result[0]["Groups"];
        $_SESSION["admin"] = $result[0]["admin"] == "1" ? true : false;
        LoggedIn(0);
    }
    else {
        ShowLoginForm();
    }

}


?>
</div>

</body>
