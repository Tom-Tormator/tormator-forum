<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// login.php
// Allows a user to log in.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Log in";

if ($_SESSION["signed_in"]) {
    message("You are already signed in, you can <a href='" . makeURL("logout") . "'>log out</a> if you want.");
    require("views/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST["user_name"] = $_POST["user_name"] ?? "";
    $_POST["user_pass"] = $_POST["user_pass"] ?? "";
    
    $password_query = $db->query("SELECT `userid`, `username`, `role`, `password` FROM `users` WHERE `username`='" . $db->real_escape_string($_POST["user_name"]) . "'");
    $user_info = $password_query->fetch_assoc();
		
    if (strlen($_POST["user_name"]) < 1) {
        message("Your username cannot be blank.");
    }
    elseif (!$password_query->num_rows) {
        message("The specified user doesn't exist.");
    }
    elseif (strlen($_POST["user_pass"]) < 1) {
        message("Your password cannot be blank.");
    }
    // Now check if the password is correct.
    elseif (!password_verify($_POST["user_pass"] ?? "", $user_info["password"])) {
        message("Incorrect password.");
    }
    else {
        $_SESSION["signed_in"] = true;
        $_SESSION["userid"] = $user_info["userid"];
        $_SESSION["username"] = $user_info["username"];
        $_SESSION["role"] = $user_info["role"];
        
        $db->query("UPDATE `users` SET `lastactive`='" . time() . "', `ip`='" . hash("sha256", $_SERVER["REMOTE_ADDR"]) . "' WHERE `userid`='" . $_SESSION["userid"] . "'");

        message("Welcome, " . htmlspecialchars($_SESSION["username"], ENT_NOQUOTES) . ". <a href='" . makeURL("") . "'>Proceed to the forum overview</a>.");
    }
}

require "views/login.php";

?>
