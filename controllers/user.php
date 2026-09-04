<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// user.php
// Displays a given user's profile.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

// Start off by making a query using the given userid.
$user_info = $db->query("SELECT * FROM users WHERE userid='" . $db->real_escape_string($url[1]) . "'");

if ($user_info->num_rows < 1) {
    message("No such user.");
    require "views/user.php";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isMod()) {
        message("You don't have permission to do this.");
    }
    elseif ($url[1] == $_SESSION["userid"]) {
        message("You cannot change your own role.");
    }
    elseif ($url[1] == $config["mainAdmin"]) {
        message("You cannot change the main admin's role.");
    }
    else {
        $setrole = $db->query("UPDATE `users` SET `role`='" . $db->real_escape_string($_POST["role"]) . "' WHERE `userid`='" . $db->real_escape_string($url[1]) . "'");

        if (!$setrole) {
            message("Failed to change role.");
        }
        else {
            refresh(0);
        }
    }
}
		
$user = $user_info->fetch_assoc();

if ($user["verified"] == "1") $verified = "Yes";
else $verified = "No";

$posts_query = $db->query("SELECT 1 FROM `posts` WHERE `user`='" . $db->real_escape_string($url[1]) . "'");
				
$posts = $posts_query->num_rows;
				
$threads_query = $db->query("SELECT 1 FROM `threads` WHERE `startuser`='" . $db->real_escape_string($url[1]) . "'");
				
$threads = $threads_query->num_rows;
			
// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
    $action = "Viewing: <a href='" . makeURL("user/{$user["userid"]}") . "'>" . htmlspecialchars($user["username"], ENT_NOQUOTES) . "'s Profile</a>";
    update_last_action($action);
}

require "views/user.php";

?>
