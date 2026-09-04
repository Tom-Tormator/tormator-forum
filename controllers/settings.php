<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// settings.php
// Allows the user to change their settings.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Settings";

if (!$_SESSION["signed_in"]) {
    message("You must be logged in to change user settings.", "error");
    require "views/settings.php";
    exit();
}

if (validateToken()) {
    if (isset($_POST["newusername"])) {
        $password_query = $db->query("SELECT `password` FROM `users` WHERE `userid`='" . $_SESSION["userid"] . "'");
        $p = $password_query->fetch_assoc();
        
        $errors = array();
        
        if ($_POST["newusername"] == $_SESSION["username"]) {
            $errors[] = "New username cannot be the same as old username.";
        }
        else {
            $errors[] = validateUsername($_POST["newusername"] ?? "");
        }

        if (!password_verify($_POST["confirmpass"] ?? "", $p["password"])) {
            $errors[] = "Incorrect password.";
        }

        if (count($errors) != 0) {
            foreach($errors as $error) {
                message($error, "error");
            }
        }
        else {
            $result = $db->query("UPDATE `users` SET `username`='" . $db->real_escape_string($_POST["newusername"]) . "' WHERE `userid`='" . $_SESSION["userid"] . "'");
            if (!$result) {
                message("Unable to change username.", "error");
            }
            else {
                $_SESSION["username"] = $_POST["newusername"];
                $_POST["newusername"] = "";
                message("Successfully changed username.", "success");
            }
        }
    }
    elseif (isset($_POST["color"])) {
        $color = substr($_POST["color"], 1);
        $hexdigits = "0123456789abcdef";
        
        // If it isn't 6 hex digits, it is invalid.
        if ((strlen($color) != 6) or (strspn($color, $hexdigits) != 6)) {
            message("Invalid color.");
        }
        else {
            $db->query("UPDATE `users` SET `color`='" . $db->real_escape_string($color) . "' WHERE `userid`='" . $_SESSION["userid"] . "'");
            message("Successfully changed color.", "success");
        }
    }
}

$user_query = $db->query("SELECT `color` FROM `users` WHERE `userid`='" . $_SESSION["userid"] . "'");
$user_info = $user_query->fetch_assoc();

require "views/settings.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
	update_last_action("Viewing: Settings");
}

?>
