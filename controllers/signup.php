<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// signup.php
// Allows users to create accounts.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Sign up";
$success = false;

if ($_SESSION["signed_in"]) {
    message("You are already signed in, you can <a href='" . makeURL("logout") . "'>log out</a> if you want.", "error");
    require("views/signup.php");
    exit();
}

if (validateToken()) {
    $errors = array();

    $uiv = validateUsername($_POST["user_name"] ?? "");
    if ($uiv) $errors[] = $uiv;
    $eiv = validateEmail($_POST["user_email"] ?? "");
    if ($eiv) $errors[] = $eiv;
    $piv = validatePassword($_POST["user_pass"] ?? "", $_POST["user_pass_check"] ?? "");
    if ($piv) $errors[] = $piv;

    if (count($errors) != 0) {
        foreach($errors as $error) {
            message($error, "error");
        }
    }
    else {
        // Construct the query.
        $password = $db->real_escape_string(password_hash($_POST["user_pass"], PASSWORD_DEFAULT));
        $role = "Member";
        $ip = $db->real_escape_string(hash("sha256", $_SERVER["REMOTE_ADDR"]));
        $verified = '1';
        $now = time();
		
        $result = $db->query("INSERT INTO `users` (`username`, `email`, `password`, `role`, `jointime`, `lastactive`, `joinip`, `ip`, `verified`) VALUES('" . $db->real_escape_string($_POST["user_name"]) . "', '" . $db->real_escape_string($_POST["user_email"]) . "', '{$password}', '{$role}', '{$now}', '{$now}', '{$ip}', '{$ip}', '{$verified}')");
						
        if (!$result) {
            // Something went wrong, display an error.
            message("Something went wrong while signing up. Please try again later.", "error");
        }
        else {
            message("Successfully signed up. You can now <a href='" . makeURL("login") . "'>log in</a> and start posting! :-)", "success");
            $success = true;
        }
    }
}

require "views/signup.php";

?>
