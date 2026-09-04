<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// thread.php
// Shows the posts in a thread and allows users to post.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

// Get the thread's information.
$thread_query = $db->query("SELECT * FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
$thread = $thread_query->fetch_assoc();

if ($thread_query->num_rows < 1) {
    http_response_code(404);
    $title = "Not found";
    message("The specified thread doesn't exist.", "error");
    require "views/error.php";
    exit();
}

$title = $thread["title"];

$posts_query = $db->query("SELECT 1 FROM `posts` WHERE `thread`='" . $thread["threadid"] . "'");
$posts = $posts_query->num_rows;

// Find out what page we're on.
if (isset($url[2]) and is_numeric($url[2])) {
    $currentPage = $url[2];
    if ($currentPage < 1) $currentPage = 1;
}
// If no valid page is specified then always assume we're on the first page.
else {
    $currentPage = 1;
}
    
// Important details for sorting the thread into pages.
$numPosts = $posts;
$pages = ceil($numPosts / $config["postsPerPage"]);

if ($currentPage > $pages) $currentPage = $pages;

// Calculate the offset for the posts query.
$offset = (($currentPage * $config["postsPerPage"]) - $config["postsPerPage"]);
if ($offset < 0) $offset = 0;

$posts_query = $db->query("SELECT * FROM `posts` WHERE `thread`='" . $db->real_escape_string($url[1]) . "' ORDER BY `timestamp` LIMIT " . $config["postsPerPage"] . " OFFSET " . $offset . "");

if ($posts_query->num_rows < 1) {
    message("There are no posts in this thread.");
}

if (validateToken()) {
    if (!$_SESSION["signed_in"]) {
        message("You must be signed in for any action within a thread.", "error");
    }
    else {
        // If the user is posting...
        if (isset($_POST["content"]) and ((!$thread["locked"])
        or isMod())) {
            // First check and see if the user has made a post too recently according to the post delay.
            $delaycheck = $db->query("SELECT 1 FROM `posts` WHERE `user`='" . $_SESSION["userid"] . "' AND `timestamp`>'" . (time() - $config["postDelay"]) . "'");
            
            if ($delaycheck->num_rows > 0) {
                message("You tried to post too soon after a previous post. The post delay is currently " . $config["postDelay"] . " seconds between posts.", "error");
            }
            elseif (strlen($_POST["content"]) < 1) {
                message("Your post cannot be blank.", "error");
            }
            elseif (strlen($_POST["content"]) > $config["maxCharsPerPost"]) {
                message("Your post was too long. The maximum number of characters a post may contain is currently set to " . $config["maxCharsPerPost"] . ".", "error");
            }
            else {
                $result = $db->query("INSERT INTO `posts` (`thread`, `user`, `timestamp`, `content`) VALUES ('" . $db->real_escape_string($url[1]) . "', '" . $_SESSION["userid"] . "', '" . time() . "', '" . $db->real_escape_string($_POST["content"]) . "')");
                    
                if (!$result) {
                    message("Your reply has not been saved, please try again later.", "error");
                }
                else {
                    $update = $db->query("UPDATE `threads` SET `lastpostuser`='" . $_SESSION["userid"] . "', `lastposttime`='" . time() . "' WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
                    if ($pages < ceil(($numPosts+1) / $config["postsPerPage"])) {
                        redirect(makeURL("thread/{$url[1]}/" . ($pages + 1)));
                    }
                    else {
                        redirect(makeURL("thread/{$url[1]}/" . $pages));
                    }
                }
            }
        }
        // If the user is requesting to delete a post...
        elseif (isset($_POST["delete"])) {
            $perm_check = $db->query("SELECT `user` FROM `posts` WHERE `postid`='" . $db->real_escape_string($_POST["delete"]) . "'");
            if ($perm_check->num_rows < 1) {
                message("Post does not exist.", "error");
            }
            elseif (!isMod() and ($_SESSION["userid"] != $perm_check->fetch_assoc()["user"])) {
                message("You don't have permission to do this.", "error");
            }
            else {
                $result = $db->query("DELETE FROM `posts` WHERE `postid`='" . $db->real_escape_string($_POST["delete"]) . "'");
            
                if (!$result) {
                    message("Sorry, post couldn't be deleted.", "error");
                }
                else {
                    // Now we need to update the thread's data to be in sync with the remaining posts.
                    $lastpost = $db->query("SELECT * FROM `posts` WHERE `thread`='" . $db->real_escape_string($url[1]) . "' ORDER BY `timestamp` DESC LIMIT 1");
                    // If there are no more posts, delete the thread.
                    if ($lastpost->num_rows < 1) {
                        $result = $db->query("DELETE FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
                        redirect(makeURL("category/" . $thread["category"]));
                    }
                    else {
                        $lp = $lastpost->fetch_assoc();
                        $update = $db->query("UPDATE `threads` SET `lastpostuser`='" . $lp["user"] . "', `lastposttime`='" . $lp["timestamp"] . "' WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
                        refresh(0);
                    }
                }
            }
        }
        // If the user is requesting to hide a post...
        elseif (isset($_POST["hide"])) {
            $perm_check = $db->query("SELECT `user` FROM `posts` WHERE `postid`='" . $db->real_escape_string($_POST["hide"]) . "'");
            if ($perm_check->num_rows < 1) {
                message("Post does not exist.");
            }
            elseif (!isMod() and ($_SESSION["userid"] != $perm_check->fetch_assoc()["user"])) {
                message("You don't have permission to do this.", "error");
            }
            else {
                $result = $db->query("UPDATE posts SET deletedby='" . $_SESSION["userid"] . "' WHERE postid='" . $db->real_escape_string($_POST["hide"]) . "'");
            
                if (!$result) {
                    message("Sorry, post couldn't be hidden.", "error");
                }
                else {
                    refresh(0);
                }
            }
        }
        // If the user is requesting to restore a post...
        elseif (isset($_POST["restore"])) {
            $perm_check = $db->query("SELECT `user`, `deletedby` FROM `posts` WHERE `postid`='" . $db->real_escape_string($_POST["restore"]) . "'");
            $pc = $perm_check->fetch_assoc();
            if ($perm_check->num_rows < 1) {
                message("Post does not exist.", "error");
            }
            elseif (!isMod() and (($_SESSION["userid"] != $pc["user"]) or ($_SESSION["userid"] != $pc["deletedby"]))) {
                message("You don't have permission to do this.", "error");
            }
            else {
                $result = $db->query("UPDATE `posts` SET `deletedby`=NULL WHERE `postid`='" . $db->real_escape_string($_POST["restore"]) . "'");
            
                if (!$result) {
                    message("Sorry, post couldn't be restored.", "error");
                }
                else {
                    refresh(0);
                }
            }
        }
        // If the user is requesting to save an edit...
        elseif (isset($_POST["saveedit"])) {
            // First make sure the user has permission to edit the specified post.
            $permission = $db->query("SELECT `user` FROM `posts` WHERE `postid`='" . $db->real_escape_string($_POST["saveeditpostid"]) . "'");
            $pc = $permission->fetch_assoc();
            if ($permission->num_rows < 1) {
                message("Post does not exist.", "error");
            }
            elseif (($pc["user"] != $_SESSION["userid"]) and !isMod()) {
                message("You don't have permission to edit this post.", "error");
            }
            else {
                $result = $db->query("UPDATE `posts` SET `content`='" . $db->real_escape_string($_POST["saveedit"]) . "', `editedby`='" . $_SESSION["userid"] . "', `edittime`='" . time() . "' WHERE `postid`='" . $db->real_escape_string($_POST["saveeditpostid"]) . "'");
        
                if (!$result) {
                    message("Sorry, post couldn't be edited.", "error");
                }
                else {
                    refresh(0);
                }
            }
        }
        // If the user is requesting to delete the thread...
        elseif (isset($_POST["deletethread"])) {
            $perm_check = $db->query("SELECT `startuser` FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
            $pc = $perm_check->fetch_assoc();
            if (!isMod() and ($_SESSION["userid"] != $pc["startuser"])) {
                message("You don't have permission to do this.", "error");
            }
            else {
                $result = $db->query("DELETE FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
            
                if (!$result) {
                    message("Sorry, thread couldn't be deleted.", "error");
                }
                else {
                    $result = $db->query("DELETE FROM `posts` WHERE `thread`='" . $db->real_escape_string($url[1]) . "'");
                    
                    if (!$result) {
                        message("Sorry, the thread's posts couldn't be deleted.", "error");
                    }
                    else {
                        redirect(makeURL("category/" . $thread["category"]));
                    }
                }
            }
        }
        elseif (isset($_POST["togglelock"]) and isMod()) {
            // We do bitwise wizardry with xor to flip the bit.
            $result = $db->query("UPDATE `threads` SET `locked`=(`locked` ^ 1) WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
            
            if (!$result) {
                message("Sorry, couldn't toggle locked status.", "error");
            }
            else {
                refresh(0);
            }
        }
        elseif (isset($_POST["togglesticky"]) and isMod()) {
            // We do bitwise wizardry with xor to flip the bit.
            $result = $db->query("UPDATE `threads` SET `sticky`=(`sticky` ^ 1) WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
            
            if (!$result) {
                message("Sorry, couldn't toggle sticky status.", "error");
            }
            
            else {
                refresh(0);
            }
        }
    }
}

require "views/thread.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
    $action = "Viewing: <a href='" . makeURL("thread/{$thread["threadid"]}") . "'>" . htmlspecialchars($thread["title"], ENT_NOQUOTES) . "</a>";
    update_last_action($action);
}

?>
