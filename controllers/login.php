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
    message("You are already logged in, you can <a href='" . makeURL("logout") . "'>log out</a> if you want.", "error");
    require("views/error.php");
    exit();
}

if (validateToken()) {
    $_POST["user_name"] = $_POST["user_name"] ?? "";
    $_POST["user_pass"] = $_POST["user_pass"] ?? "";
    
    $password_query = $db->query("SELECT `userid`, `username`, `role`, `password` FROM `users` WHERE `username`='" . $db->real_escape_string($_POST["user_name"]) . "'");
    $user_info = $password_query->fetch_assoc();
    
    $iphash = hash("sha256", $_SERVER["REMOTE_ADDR"]);
    
    $errors = array();
		
    if (strlen($_POST["user_name"]) < 1) {
        $errors[] = "Your username cannot be blank.";
    }
    elseif (strlen($_POST["user_pass"]) < 1) {
        $errors[] = "Your password cannot be blank.";
    }
    elseif (!$password_query->num_rows) {
        $errors[] = "The specified user doesn't exist.";
    }
    else {
        // Rate limit check.
        $rlcheck = $db->query("SELECT 1 FROM `logs` WHERE `action`='login_fail' AND `ip`='{$iphash}' AND `victim`='{$user_info["userid"]}' AND `timestamp`>" . (time()-3600));
        if ($rlcheck->num_rows >= $config["loginsPerHour"]) {
            $errors[] = "Too many failed login attemps. Try again later.";
        }
        // Now check if the password is correct.
        elseif (!password_verify($_POST["user_pass"] ?? "", $user_info["password"])) {
            $errors[] = "Incorrect password.";
            // Log failed login.
            $db->query("INSERT INTO `logs` (`action`, `victim`, `ip`, `useragent`, `timestamp`) VALUES ('login_fail', '{$user_info["userid"]}', '{$iphash}', '" . $db->real_escape_string(substr($_SERVER["HTTP_USER_AGENT"], 0, 255)) . "', '" . time() . "')");
        }
    }
    
    if (count($errors)) {
        foreach ($errors as $error) {
            message($error, "error");
        }
    }
    else {
        session_regenerate_id(true);
        $_SESSION["signed_in"] = true;
        $_SESSION["userid"] = $user_info["userid"];
        $_SESSION["username"] = $user_info["username"];
        $_SESSION["role"] = $user_info["role"];
        
        $db->query("UPDATE `users` SET `lastactive`='" . time() . "', `ip`='{$iphash}' WHERE `userid`='" . $_SESSION["userid"] . "'");
        
        // Log successful login.
        $db->query("INSERT INTO `logs` (`action`, `victim`, `ip`, `useragent`, `timestamp`) VALUES ('login_success', '{$_SESSION["userid"]}', '{$iphash}', '" . $db->real_escape_string(substr($_SERVER["HTTP_USER_AGENT"], 0, 255)) . "', '" . time() . "')");

        // Just redirect the user to the forum homepage.
        redirect("");
    }
}

require "views/login.php";

?>
