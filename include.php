<?php

$captcha_public = "6LebsaIUAAAAAP_1YftW7_mk2Lz5-XqJMm4BafrO";
$captcha_secret = "6LebsaIUAAAAAIpkUEH5CBrHmflalB-RC4kjxQM3";

function gen_header() {
    ?>
    <head>
        <link rel="stylesheet" href="bootstrap.min.css">
    </head>
    <body>
        <div class="container">

    <?php
}

function footer() {
    echo "</div></body></html>";
}
function LoggedIn($active) {

?>
    <body style="background-color:#dbc6a8">
     <ul class="nav nav-pills" role="tablist">
        <li role="presentation" <?php echo ($active==0?"class=\"active\"":"") ?> ><a href="<?php echo "./user_profile.php?User=" . $_SESSION["name"] ?>">Profile</a></li>
        <li role="presentation" <?php echo ($active==1?"class=\"active\"":"") ?> ><a href="./posts.php">Posts</a></li>
        <li role="presentation" <?php echo ($active==2?"class=\"active\"":"") ?> ><a href="./searchCommunity.php">Communities</a></li>
        <li role="presentation" <?php echo ($active==3?"class=\"active\"":"") ?> ><a href="./logout.php">Logout</a></li>
    </ul>
<?php
        echo "<p>You are now logged in as: ".htmlentities($_SESSION["name"])."<br><br>";

}

function checkLastPost() {

    $datetime1 = date_create($_SESSION["lastPost"]);
    $datetime2 = date_create(date("Y-m-d h:i:s"));

    // calculates the difference between DateTime objects 
    $interval = date_diff($datetime1, $datetime2); 

    // printing result in days format 
    $difference = $interval->format('%d') * 1440 +  $interval->format('%h') * 60 +  $interval->format('%i');
    
    if ($difference < 2) {
        echo "<h1>Please wait at least 2 minutes before making another post</h1>";
        return false;
    }
    else
        return true;
}


?>