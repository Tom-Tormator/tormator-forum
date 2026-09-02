<?php
// user.php
// Displays a given user's profile.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

// Start off by making a query using the given userid.
$user_info = $db->query("SELECT * FROM users WHERE userid='" . $db->real_escape_string($url[1]) . "'");

if ($user_info->num_rows == 0) {
    message("No such user.");
    require "views/footer.php";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_SESSION["role"] != "Administrator") {
        message("You don't have permission to do this.");
    }
    elseif ($url[1] == $_SESSION["id"]) {
        message("You cannot change your own role.");
    }
    elseif ($url[1] == $config["mainAdmin"]) {
        message("You cannot change the main admin's role.");
    }
    else {
        $setrole = $db->query("UPDATE users SET role='" . $db->real_escape_string($_POST["role"]) . "' WHERE userid='" . $db->real_escape_string($url[1]) . "'");

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
			
if ($_SESSION["role"] == "Administrator") {
    echo "<div class='usertop' postcolor='" . htmlspecialchars($user["color"]) . "'><b>" . htmlspecialchars($user["username"]) . "</b> <small><form method='post' action=''><select name='role'>";

    if ($user["role"] == "Administrator") {
        echo '<option value="Administrator" selected>Administrator</option>';
        echo '<option value="Moderator">Moderator</option>';
        echo '<option value="Member">Member</option>';
        echo '<option value="Suspended">Suspended</option>';
    }	
    elseif ($user["role"] == "Moderator") {
        echo '<option value="Administrator">Administrator</option>';
        echo '<option value="Moderator" selected>Moderator</option>';
        echo '<option value="Member">Member</option>';
        echo '<option value="Suspended">Suspended</option>';
    }
    elseif ($user["role"] == "Member") {
        echo '<option value="Administrator">Administrator</option>';
        echo '<option value="Moderator">Moderator</option>';
        echo '<option value="Member" selected>Member</option>';
        echo '<option value="Suspended">Suspended</option>';
    }
    elseif ($user["role"] == "Suspended") {
        echo '<option value="Administrator">Administrator</option>';
        echo '<option value="Moderator">Moderator</option>';
        echo '<option value="Member">Member</option>';
        echo '<option value="Suspended" selected>Suspended</option>';
    }
				
    echo "</select><input type='submit' value='Change role'></form></small></div>";
}
else {
    echo "<div class='usertop' postcolor='" . htmlspecialchars($user["color"]) . "'><b>" . htmlspecialchars($user["username"], ENT_NOQUOTES) . "</b> <small>" . $user["role"] . "</small></div>";
}

$posts_query = $db->query("SELECT 1 FROM `posts` WHERE `user`='" . $db->real_escape_string($url[1]) . "'");
				
$posts = $posts_query->num_rows;
				
$threads_query = $db->query("SELECT 1 FROM `threads` WHERE `startuser`='" . $db->real_escape_string($url[1]) . "'");
				
$threads = $threads_query->num_rows;
				
echo("<div class='userbottom'>Joined: <a title='" . date('m-d-Y h:i:s A', $user['jointime']) . "'>" . relativeTime($user["jointime"]) . "</a>" . "</p>Last active: <a title='" . date('m-d-Y h:i:s A', $user["lastactive"]) . "'>" . relativeTime($user["lastactive"]) . "</a>" . "</p>Posts: " . $posts . "</p>Threads: " . $threads . "</p>Verified: " . $verified . "</div>");
			
// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
    $action = "Viewing: <a href='/user/" . $user["userid"] . "/'>" . $user["username"] . "'s Profile</a>";
    update_last_action($action);
}

require "views/footer.php";

?>
