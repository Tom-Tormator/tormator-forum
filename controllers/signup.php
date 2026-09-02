<?php
// signup.php
// Allows users to create accounts.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Sign up";
$success = false;

if ($_SESSION["signed_in"]) {
    message("You are already signed in, you can <a href='/logout/'>log out</a> if you want.");
    require("views/signup.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    
    $_POST["user_name"] = $_POST["user_name"] ?? "";
    $_POST["user_email"] = $_POST["user_email"] ?? "";
    $_POST["user_pass"] = $_POST["user_pass"] ?? "";
    $_POST["user_pass_check"] = $_POST["user_pass_check"] ?? "";

    if (strlen($_POST["user_name"]) < 1) {
        $errors[] = "Your username cannot be blank.";
    }
    elseif (strlen($_POST["user_name"]) > 24) {
        $errors[] = "Your username cannot be longer than 24 characters.";
    }
    elseif (!ctype_alnum($_POST["user_name"])) {
        $errors[] = "Your username can only contain alphanumeric characters.";
    }
    // Make sure the username isnt taken.
    elseif ($db->query("SELECT 1 FROM `users` WHERE `username`='" . $db->real_escape_string($_POST["user_name"]) . "'")->num_rows > 0) {
        $errors[] = "Your username is already taken.";
    }
    
    if (strlen($_POST["user_email"]) < 1) {
        $errors[] = "Your email cannot be blank.";
    }
    elseif (strlen($_POST["user_email"]) > 255) {
        $errors[] = "Your email cannot be more than 255 characters long.";
    }
    elseif (!filter_var($_POST["user_email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Your email is invalid.";
    }
    // Make sure the email isnt taken.
    elseif ($db->query("SELECT 1 FROM `users` WHERE `email`='" . $db->real_escape_string($_POST["user_email"]) . "'")->num_rows > 0) {
        $errors[] = "Your email is already taken.";
    }

    if (strlen($_POST["user_pass"]) < 1) {
        $errors[] = "Your password cannot be blank.";
    }
    elseif (strlen($_POST["user_pass"]) < $config["minPasswordLength"]) {
        $errors[] = "Your password cannot be less than {$config["minPasswordLength"]} characters.";
    }
    elseif ($_POST["user_pass"] != $_POST["user_pass_check"]) {
        $errors[] = "Your passwords do not match.";
    }

    if (count($errors) != 0) {
        foreach($errors as $error) {
            message($error);
        }
    }
    else {
        // Construct the query.
        $password = password_hash($_POST["user_pass"], PASSWORD_DEFAULT);
        $role = "Member";
        $ip = hash("sha256", $_SERVER["REMOTE_ADDR"]);
        $verified = '1';
        $now = time();
		
        $result = $db->query("INSERT INTO `users` (`username`, `email`, `password`, `role`, `jointime`, `lastactive`, `joinip`, `ip`, `verified`) VALUES('" . $db->real_escape_string($_POST["user_name"]) . "', '" . $db->real_escape_string($_POST["user_email"]) . "', '" . $db->real_escape_string($password) . "', '$role', '$now', '$now', '$ip', '$ip', '$verified')");
						
        if (!$result) {
            // Something went wrong, display an error.
            message("Something went wrong while registering. Please try again later.");
        }
        else {
            message('Successfully registered. You can now <a href="login.php">log in</a> and start posting! :-)');
            $success = true;
        }
    }
}

require "views/signup.php";

?>
