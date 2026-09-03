<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// newthread.php
// Creates a new thread.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "New thread";
$success = false;

if (!$_SESSION["signed_in"]) {
	message("Sorry, you have to be <a href='/login/'>logged in</a> to create a thread.");
	require "views/newthread.php";
	exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST["title"] = $_POST["title"] ?? "";
    $_POST["category"] = $_POST["category"] ?? "";
    $_POST["postcontent"] = $_POST["postcontent"] ?? "";
    
    $cat = $db->query("SELECT 1 FROM `categories` WHERE `categoryid`='" . $db->real_escape_string($_POST["category"]) . "'");
    $delaycheck = $db->query("SELECT 1 FROM `posts` WHERE `user`='" . $_SESSION["userid"] . "' AND `timestamp`>'" . (time() - $config["postDelay"]) . "'");
		
    if (strlen($_POST["title"]) < 1) {
        message("Your title cannot be blank.");
    }	
    elseif (strlen($_POST["title"]) > $config["maxCharsPerTitle"]) {
        message("Your title was too long.");
    }
    elseif (strlen($_POST["postcontent"]) < 1) {
        message("Your post cannot be blank.");
    }	
    elseif (strlen($_POST["postcontent"]) > $config["maxCharsPerPost"]) {
        message("Your post was too long.");
    }
    elseif ($cat->num_rows < 1) {
        message("Invalid category selection.");
    }
    elseif ($delaycheck->num_rows > 0) {
        message("You tried to post too soon after a previous post. You must wait " . $config["postDelay"] . " seconds between posts.");
    }
    else {
        $beginwork = $db->query("BEGIN WORK");
        $justnow = time();
        $userid = $_SESSION["userid"];
		
        $threadresult = $db->query("INSERT INTO `threads` (`title`, `startuser`, `starttime`, `lastpostuser`, `lastposttime`, `category`) VALUES ('" . $db->real_escape_string($_POST["title"]) . "', '$userid', '$justnow', '$userid', '$justnow', '" . $db->real_escape_string($_POST["category"]) . "')");
			
        if (!$threadresult) {
            echo 'An error occured while inserting your thread. Please try again later.';
            $db->query("ROLLBACK");
        }
        else {
            $threadid = $db->insert_id;
					 	
            $result = $db->query("INSERT INTO `posts` (`thread`, `user`, `timestamp`, `content`) VALUES ('$threadid', '$userid', '$justnow', '" . $db->real_escape_string($_POST["postcontent"]) . "')");
				
            if (!$result) {
                echo 'An error occured while inserting your post. Please try again later.';
                $db->query("ROLLBACK");
            }
            else {
                $db->query("COMMIT");
					
                message('You have successfully created <a href="/thread/'. $threadid . '/">your new thread</a>.');
                $success = true;
            }
        }
    }
}
		
$cats = $db->query("SELECT * FROM `categories`");

if ($cats->num_rows < 1) {
    if ($_SESSION["role"] == "Administrator") {
        message('You have not created categories yet.');
    }		
    else {
        message("Before you can post a topic, you must wait for an admin to create some categories.");
    }
}

require "views/newthread.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
	update_last_action("Creating a thread");
}

?>
