<?php
session_start();
include "database.php";
include "include.php";
gen_header();
?>
<body style="background-color:#dbc6a8">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>         <!-- Required(?) by captcha -->
<?php
if(isset($_SESSION["name"])) {
    LoggedIn(0);                        // If already logged in the current session, i.e. didn't "log out", directly enter
}
else {
    if(isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"]))
        DoLogin();                                              // instead of creating a new file, logging in is
    else                                                        // in the same file
        ShowLoginForm();
}
function ShowLoginForm() {

    ?>
    <form class="form-signin" method="post">
        <h2 class="form-signin-heading">Register</h2>
        <label for="inputUser" class="sr-only">Username</label>
        <input type="text" id="inputUser" name="username" maxlength="20" minlength="4" class="form-control" placeholder="Username" pattern="^[a-zA-Z0-9]+$" required autofocus>
        Only alphanumeric characters <br>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" maxlength="20" minlength="4" class="form-control" placeholder="Password" pattern="^[a-zA-Z0-9]+$" required>
        Only alphanumeric characters <br>
        <label for="inputEmail" class="sr-only">Email Address</label>
        <input type="text" id="inputEmail" name="email" class="form-control" placeholder="Email Address" pattern="^[a-zA-Z0-9@._-]+$" required>
        <h1>How do you view an object?</h1>
        <input type="radio" name="q1" value="0" required> Tree rather than a forest <br>
        <input type="radio" name="q1" value="1" required> Forest rather than a tree <br>
        <h1>When making decisions, what is your train of thought?</h1>
        <input type="radio" name="q2" value="0" required> Logical <br>
        <input type="radio" name="q2" value="1" required> Emotional <br>
        <h1>Where does your energy flow?</h1>
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

function DoLogin() {

    require_once('recaptchalib.php');

    $response = $_POST["g-recaptcha-response"];
    $verify = new recaptchalib("6LeuJ54UAAAAAO58XWYTLN8iSBVM1HzD5YH0FNac", $response);

    if ($verify->isValid()){                // If captcha is succesfull

        if (($_POST["q1"] == "0" || $_POST["q1"] == "1") && ($_POST["q2"] == "0" || $_POST["q2"] == "1") &&
            ($_POST["q3"] == "0" || $_POST["q3"] == "1") && ($_POST["q4"] == "0" || $_POST["q4"] == "1") &&
            ctype_alnum($_POST["username"]) && ctype_alnum($_POST["password"]) &&
            strlen($_POST["username"]) >= 4 && strlen($_POST["username"]) <= 20 &&
            strlen($_POST["password"]) >= 4 && strlen($_POST["password"]) <= 20) {       // If all the question results are 0 or 1,
                                                                                        // also both un and pw are only alphanumeric
                                                                                        // also if inputs are long enough
            $groups = $_POST["q1"] . $_POST["q2"] . $_POST["q3"] . $_POST["q4"];
        }
        else {
            echo "Please input correctly<br>";
            echo '<a href="./">Go back</a><br>';
            exit();
        }
        if(strlen($_POST["email"] > 320)) {
            echo "E-mail you entered is not valid, please try another mail address";
            echo '<a href="./">Go back</a><br>';
            exit();
        }

        global $dbh;
        $sql_r = "SELECT * FROM websecproj.users WHERE Username=:user";
        $sth=$dbh->prepare($sql_r);
        $sth->bindParam(":user", $_POST["username"]);
        $sth->execute();
        $row = $sth->fetch( PDO::FETCH_ASSOC);

        if ($row){
            echo "Username alraedy exists.";
            echo '<a href="./register.php">Go back</a><br>';
            exit();
        }

        $hpw = password_hash($_POST["password"], PASSWORD_DEFAULT);         // No need for pebble, as the code is available online anyway
        $mailcode = rand(1000, 9999);

        $sql_r="INSERT INTO websecproj.users (Username, HashedPassword, Email, emailverificationcode, Groups)" . 
            " VALUES (:user, :hpw, :email, :emailcode, :groups)";
                                                 // Rest of the values are defaulted.

        $sth=$dbh->prepare($sql_r);

        $sth->bindParam(":user", $_POST["username"]);
        $sth->bindParam(":hpw", $hpw);
        $sth->bindParam(":email", $_POST["email"]);
        $sth->bindParam(":emailcode", $mailcode);
        $sth->bindParam(":groups", $groups);
        $sth->execute();

        echo "An email has been sent to your account<br>";
        echo "Currently this function is closed. No email is sent and no verification is needed.<br>";
        echo '<a href="./login.php">Login</a><br>';
/*
        // Send the verification email    

        $subject = 'Signup | Verification'; // Give the email a subject 
        $message = '
        
        Thanks for signing up!
        Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below.
        
        ------------------------
        Username: '.$_POST["username"].'
        ------------------------

        Please verify your e-mail within 2 days.
        
        Please click this link to activate your account:
        http://localhost/websecprojPHP/verifyemail.php?email='.$_POST["email"].'&code='.$mailcode.'
        
        '; // Our message above including the link
                            
        $headers = 'From:websecprojmail@gmail.com' . "\r\n"; // Set from headers
        mail($_POST["email"], $subject, $message, $headers); // Send our email
*/
    }
}


?>
</div>

</body>