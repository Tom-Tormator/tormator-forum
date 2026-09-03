<?php
// functions.php
// Contains functions for use throughout the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

// Redirects the user to the specified page. If blank it defaults to the homepage.
function redirect($text) {
    global $config;
    if (($_SERVER["HTTPS"] ?? "") == "on") $proto = "https://";
    else $proto = "http://";
    if ($config["folder"]) $folder = $config["folder"] . "/";
    else $folder = "";
	header("Location: " . $proto . $_SERVER["HTTP_HOST"] . "/" . $folder . $text);
	exit();
}

// Refreshes the current page.
function refresh($time) {
	header("Refresh:" . $time . "");
	exit();
}

// Logs the user out.
function logout() {
	session_unset();
	session_destroy();
	redirect("");
}

// Update the user's last action and set their last active time to now.
function update_last_action($action) {
	global $db;
	$result = $db->query("UPDATE `users` SET `lastactive`='" . time() . "', `lastaction`='" . $db->real_escape_string($action) . "' WHERE `userid`='" . $_SESSION["userid"] . "'");
}

// Display a nice message.
function message($content) {
    global $messages;
	$messages[] = "<div class='message'>" . $content . "</div>";
}

// Convert a unix timestamp into a human readable time format.
function relativeTime($timestamp) {
	$diff = time() - $timestamp;
	
	if ($diff <= 0) {
		return "Just now";
	}
	
	elseif ($diff == 1) {
		return "1 second ago";
	}
	
	elseif (($diff > 1) and ($diff < 60)) {
		return $diff . " seconds ago";
	}
	
	elseif (($diff >= 60) and ($diff <= 120)) {
		return "1 minute ago";
	}
	
	elseif (($diff > 120) and ($diff < 3600)) {
		return round($diff / 60) . " minutes ago";
	}
	
	elseif (($diff >= 3600) and ($diff <= 7200)) {
		return "1 hour ago";
	}
	
	elseif (($diff > 7200) and ($diff < 86400)) {
		return round(($diff / 60) / 60) . " hours ago";
	}
	
	elseif (($diff >= 86400) and ($diff <= 172800)) {
		return "1 day ago";
	}
	
	elseif (($diff > 172800) and ($diff < 604800)) {
		return round((($diff / 60) / 60) / 24) . " days ago";
	}
	
	elseif (($diff >= 604800) and ($diff <= 1209600)) {
		return "1 week ago";
	}
	
	elseif (($diff > 1209600) and ($diff < 2419200)) {
		return round(((($diff / 60) / 60) / 24) / 7) . " weeks ago";
	}
	
	elseif (($diff >= 2419200) and ($diff <= 4838400)) {
		return "1 month ago";
	}
	
	elseif (($diff > 4838400) and ($diff < 29030400)) {
		return round((((($diff / 60) / 60) / 24) / 7) / 4) . " months ago";
	}
	
	elseif (($diff >= 29030400) and ($diff <= 58060800)) {
		return "1 year ago";
	}
	
	elseif ($diff > 58060800) {
		return round(((((($diff / 60) / 60) / 24) / 7) / 4) / 12) . " years ago";
	}
}

function flushConfig() {
    global $config;
    return file_put_contents("config/config.php", "<?php\n\n if (!defined(\"INDEXED\")) exit;\n\n\$config = " . var_export($config, true) . "\n\n?>");
}

function title() {
    global $title, $config;
    if ($title) {
        echo(htmlspecialchars($title, ENT_NOQUOTES) . " - " . htmlspecialchars($config["forumName"], ENT_NOQUOTES));
    }
    else {
        echo(htmlspecialchars($config["forumName"], ENT_NOQUOTES));
    }
}

// Returns true if the current user is a Moderator or an Administrator.
function isMod() {
    if ($_SESSION["signed_in"] and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) return true;
    else return false;
}

?>
