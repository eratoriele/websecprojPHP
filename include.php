<?php
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
     <ul class="nav nav-pills" role="tablist">
        <li role="presentation" <?php echo ($active==0?"class=\"active\"":"") ?> ><a href="<?php echo "./user_profile.php?User=" . $_SESSION["name"] ?>">Profile</a></li>
        <li role="presentation" <?php echo ($active==1?"class=\"active\"":"") ?> ><a href="./posts.php">Posts</a></li>
        <li role="presentation" <?php echo ($active==2?"class=\"active\"":"") ?> ><a href="./searchCommunity.php">Communities</a></li>
        <li role="presentation" <?php echo ($active==3?"class=\"active\"":"") ?> ><a href="./logout.php">Logout</a></li>
    </ul>
<?php
        echo "<p>You are now logged in as: ".htmlentities($_SESSION["name"])."<br><br>";

}


?>