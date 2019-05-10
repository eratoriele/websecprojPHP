<?php
    include "database.php";

    // Check if inputs are given
    if (!isset($_GET["email"]) || !isset($_GET["code"])) {
        echo "This page is not for you";
        echo '<a href="./">Go back</a><br>';
        exit();
    }

    // Check if e-mail is legit
    if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_GET["email"]) || strlen($_GET["email"] > 320)) {
        echo "E-mail you entered is not valid, please try another mail address";
        echo '<a href="./">Go back</a><br>';
        exit();
    }

    global $dbh;

    $sql_r = "SELECT * FROM websecproj.users WHERE Email=:email AND emailverificationcode=:code";
    $sth=$dbh->prepare($sql_r);
    $sth->bindParam(":email", $_GET["email"]);
    $sth->bindParam(":code", $_GET["code"]);
    $sth->execute();
    $row = $sth->fetch( PDO::FETCH_ASSOC);

    if ($row) {

        // Check if more than 2 days passed since account created
        $datetime1 = date_create($row["LastPost"]);
        $datetime2 = date_create(date("Y-m-d h:i:s"));

        // calculates the difference between DateTime objects 
        $interval = date_diff($datetime1, $datetime2); 

        // years * 365 + months * 30 + days
        $difference = $interval->format('%Y') * 365 + $interval->format('%m') * 30 + $interval->format('%d');

        if ($difference > 2) {
            echo "You took more than 2 days to verify your email. Please create a new account";
            echo '<a href="./">Go back</a><br>';
            exit();
        }

        $sql_r = "UPDATE websecporj.users SET Email_verified=true WHERE Email=:email AND emailverificationcode=:code";
        $sth=$dbh->prepare($sql_r);
        $sth->bindParam(":email", $_GET["email"]);
        $sth->bindParam(":code", $_GET["code"]);
        $sth->execute();

        echo "Account verified. Please login<br>";
        echo '<a href="./">Go back</a><br>';
        exit();
    }

?>