<?php
// thread.php
// Shows the posts in a thread and allows users to post.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$display = true;

// Get the thread's information.
$thread_query = $db->query("SELECT * FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
$thread = $thread_query->fetch_assoc();

if ($thread_query->num_rows < 1) {
    message("The specified thread doesn't exist.");
    $display = false;
    require "views/thread.php";
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
    $display = false;
    require "views/thread.php";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$_SESSION["signed_in"]) {
        message("You must be signed in for any action within a thread.");
    }
    else {
        // If the user is posting...
        if (isset($_POST["content"]) and ((!$thread["locked"])
        or idMod())) {
            // First check and see if the user has made a post too recently according to the post delay.
            $delaycheck = $db->query("SELECT 1 FROM `posts` WHERE `user`='" . $_SESSION["userid"] . "' AND `timestamp`>'" . (time() - $config["postDelay"]) . "'");
            
            if ($delaycheck->num_rows > 0) {
                message("You tried to post too soon after a previous post. The post delay is currently " . $config["postDelay"] . " seconds between posts.");
            }
            elseif (strlen($_POST["content"]) < 1) {
                message("Your post cannot be blank.");
            }
            elseif (strlen($_POST["content"]) > $config["maxCharsPerPost"]) {
                message("Your post was too long. The maximum number of characters a post may contain is currently set to " . $config["maxCharsPerPost"] . ".");
            }
            else {
                $result = $db->query("INSERT INTO `posts` (`thread`, `user`, `timestamp`, `content`) VALUES ('" . $db->real_escape_string($url[1]) . "', '" . $_SESSION["userid"] . "', '" . time() . "', '" . $db->real_escape_string($_POST["content"]) . "')");
                $update = $db->query("UPDATE `threads` SET `lastpostuser`='" . $_SESSION["userid"] . "', `lastposttime`='" . time() . "' WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
                    
                if (!$result) {
                    echo 'Your reply has not been saved, please try again later.';
                }
                else {
                    refresh(0);
                }
            }
        }
        // If the user is requesting to delete a post...
        elseif (isset($_POST["delete"])) {
            $perm_check = $db->query("SELECT `user` FROM `posts` WHERE `postid`='" . $db->real_escape_string($_POST["delete"]) . "'");
            if ($perm_check->num_rows < 1) {
                message("Post does not exist.");
            }
            elseif (!isMod() and ($_SESSION["userid"] != $perm_check->fetch_assoc()["user"])) {
                message("You don't have permission to do this.");
            }
            else {
                $result = $db->query("DELETE FROM `posts` WHERE `postid`='" . $db->real_escape_string($_POST["delete"]) . "'");
            
                if (!$result) {
                    echo "Sorry, post couldn't be deleted.";
                }
                else {
                    // Now we need to update the thread's data to be in sync with the remaining posts.
                    $lastpost = $db->query("SELECT * FROM `posts` WHERE `thread`='" . $db->real_escape_string($url[1]) . "' ORDER BY `timestamp` DESC LIMIT 1");
                    // If there are no more posts, delete the thread.
                    if ($lastpost->num_rows < 1) {
                        $result = $db->query("DELETE FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
                        redirect("category/" . $thread["category"] . "/");
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
                message("You don't have permission to do this.");
            }
            else {
                $result = $db->query("UPDATE posts SET deletedby='" . $_SESSION["userid"] . "' WHERE postid='" . $db->real_escape_string($_POST["hide"]) . "'");
            
                if (!$result) {
                    message("Sorry, post couldn't be hidden.");
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
                message("Post does not exist.");
            }
            elseif (!isMod() and (($_SESSION["userid"] != $pc["user"]) or ($_SESSION["userid"] != $pc["deletedby"]))) {
                message("You don't have permission to do this.");
            }
            else {
                $result = $db->query("UPDATE `posts` SET `deletedby`=NULL WHERE `postid`='" . $db->real_escape_string($_POST["restore"]) . "'");
            
                if (!$result) {
                    message("Sorry, post couldn't be restored.");
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
            if ($perm_check->num_rows < 1) {
                message("Post does not exist.");
            }
            elseif (($p["user"] != $_SESSION["userid"]) and !isMod()) {
                message("You don't have permission to edit this post.");
            }
            else {
                $result = $db->query("UPDATE `posts` SET `content`='" . $db->real_escape_string($_POST["saveedit"]) . "' WHERE `postid`='" . $db->real_escape_string($_POST["saveeditpostid"]) . "'");
        
                if (!$result) {
                    message("Sorry, post couldn't be restored.");
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
                message("You don't have permission to do this.");
            }
            else {
                $result = $db->query("DELETE FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
            
                if (!$result) {
                    message("Sorry, thread couldn't be deleted.");
                }
                else {
                    $result = $db->query("DELETE FROM `posts` WHERE `thread`='" . $db->real_escape_string($url[1]) . "'");
                    
                    if (!$result) {
                        message("Sorry, the thread's posts couldn't be deleted.");
                    }
                    else {
                        redirect("category/" . $thread["category"] . "/");
                    }
                }
            }
        }
        elseif (isset($_POST["togglelock"]) and isMod()) {
            // We do bitwise wizardry with xor to flip the bit.
            $result = $db->query("UPDATE `threads` SET `locked`=(`locked` ^ 1) WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
            
            if (!$result) {
                message("Sorry, couldn't toggle locked status.");
            }
            else {
                refresh(0);
            }
        }
        elseif (isset($_POST["togglesticky"]) and isMod()) {
            // We do bitwise wizardry with xor to flip the bit.
            $result = $db->query("UPDATE `threads` SET `sticky`=(`sticky` ^ 1) WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
            
            if (!$result) {
                message("Sorry, couldn't toggle sticky status.");
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
    $action = "Viewing: <a href='/thread/" . $thread["threadid"] . "/'>" . $thread["title"] . "</a>";
    update_last_action($action);
}

?>
