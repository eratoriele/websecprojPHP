<?php

//function uploadimage() {


    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);         // Name of file
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));         // holds the file extension of the file
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
    } 
    else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    if (strlen($target_file) >= 200) {
        echo "Name of the image should be below 200 chars";
        $uploadOk = 0;
    }

    if (strpos($target_file, " ")) {
        $tmpOldStrLength = strlen(" ");
        while (($offset = strpos($target_file, " ")) !== false) {
            $target_file = substr_replace($target_file, "_", $offset, $tmpOldStrLength);
          }
    }

    if (strpos($target_file, "/")) {
        $tmpOldStrLength = strlen("/");
        while (($offset = strpos($target_file, "/")) !== false) {
            $target_file = substr_replace($target_file, "_", $offset, $tmpOldStrLength);
          }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        for(;file_exists($target_file);){       // Adds random char to end and check again
            $target_file = $target_dir . rand() . basename($_FILES["fileToUpload"]["name"]);
        }
    } 

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 2000000) {         // 2 mb
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    } 

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
        echo "Sorry, only JPG, JPEG, PNG files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        exit();
    // if everything is ok, try to upload file
    } 
    else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        } 
        else {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }
    }
//}
?>
