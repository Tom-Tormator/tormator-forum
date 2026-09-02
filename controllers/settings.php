<?php
// settings.php
// Allows the user to change their settings.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Settings";

if (!$_SESSION["signed_in"]) {
    message("You must be logged in to change user settings.");
    require "views/settings.php";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["newusername"])) {
        $password_query = $db->query("SELECT `password` FROM `users` WHERE `userid`='" . $_SESSION["userid"] . "'");
        $p = $password_query->fetch_assoc();
        
        if (strlen($_POST["newusername"]) < 1) {
            message("New username cannot be blank.");
        }
        elseif ($_POST["newusername"] == $_SESSION["username"]) {
            message("New username cannot be the same as old username.");
        }
        elseif (strlen($_POST["newusername"]) > 24) {
            message("New username cannot be longer than 24 characters.");
        }
        elseif (!ctype_alnum($_POST["newusername"])) {
            message("New username cannot contain non-alphanumeric characters.");
        }
        elseif ($db->query("SELECT 1 FROM `users` WHERE `username`='" . $db->real_escape_string($_POST["newusername"]) . "'")->num_rows > 0) {
            message("New username is already taken.");
        }
        elseif (!password_verify($_POST["confirmpass"] ?? "", $p["password"])) {
            message("Incorrect password.");
        }
        else {
            $result = $db->query("UPDATE `users` SET `username`='" . $db->real_escape_string($_POST["newusername"]) . "' WHERE `userid`='" . $_SESSION["userid"] . "'");
            if (!$result) {
                message("Unable to change username.");
            }
            else {
                $_SESSION["username"] = $_POST["newusername"];
                $_POST["newusername"] = "";
                message("Successfully changed username.");
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
            message("Successfully changed color.");
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
