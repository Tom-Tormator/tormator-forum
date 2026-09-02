<?php
// thread.php
// Shows the inside of a thread and allows users to post.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

// Get the thread's information.
$thread_query = $db->query("SELECT * FROM `threads` WHERE `threadid`='" . $db->real_escape_string($url[1]) . "'");
$thread = $thread_query->fetch_assoc();

if ($thread_query->num_rows < 1) {
    message("The specified thread doesn't exist.");
    require "views/footer.php";
    exit();
}

// Find out what page we're on.
if (isset($url[2]) and is_numeric($url[2])) {
    $currentPage = $url[2];
}
// If no valid page is specified then always assume we're on the first page.
else {
    $currentPage = 1;
}
	
// Important details for sorting the thread into pages.
$numPosts = $thread["posts"];
$pages = ceil($numPosts / $config["postsPerPage"]);

// Calculate the offset for the posts query.
$offset = (($currentPage * $config["postsPerPage"]) - $config["postsPerPage"]);

$posts_query = $db->query("SELECT * FROM `posts` WHERE `thread`='" . $db->real_escape_string($url[1]) . "' ORDER BY `timestamp` LIMIT " . $config["postsPerPage"] . " OFFSET " . $offset . "");

if ($posts_query->num_rows < 1) {
    message("There are no posts in this thread.");
    require "views/footer.php";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$_SESSION["signed_in"]) {
        message("You must be signed in for any action within a thread.");
    }
    else {
		// If the user is posting...
		if (isset($_POST["content"]) and ((!$thread["locked"])
		or (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator")))) {
			// First check and see if the user has made a post too recently according to the post delay.
			$delaycheck = $db->query("SELECT 1 FROM posts WHERE user='" . $_SESSION["userid"] . "' AND timestamp>'" . (time() - $config["postDelay"]) . "'");
			
			if ($delaycheck->num_rows > 0) {
				message("You tried to post too soon after a previous post. The post delay is currently " . $config["postDelay"] . " seconds between posts.");
			}
			else {
				if (strlen($_POST["content"]) < 1) {
					message("Your post cannot be blank.");
				}
				elseif (strlen($_POST["content"]) > $config["maxCharsPerPost"]) {
					message("Your post was too long. The maximum number of characters a post may contain is currently set to " . $config["maxCharsPerPost"] . ".");
				}
				else {
					$result = $db->query("INSERT INTO posts (thread, user, timestamp, content) VALUES ('" . $db->real_escape_string($url[1]) . "', '" . $_SESSION["userid"] . "', '" . time() . "', '" . $db->real_escape_string($_POST["content"]) . "')");
					$update = $db->query("UPDATE threads SET posts=posts+1, lastpostuser='" . $_SESSION["userid"] . "', lastposttime='" . time() . "' WHERE threadid='" . $db->real_escape_string($url[1]) . "'");
						
					if (!$result) {
						echo 'Your reply has not been saved, please try again later.';
					}
					else {
						refresh(0);
					}
				}
			}
		}
		// If the user is requesting to delete a post...
		elseif (($_POST["delete"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("DELETE FROM posts WHERE postid='" . $db->real_escape_string($_POST["delete"]) . "'");
			
			if (!$result) {
				echo "Sorry, post couldn't be deleted.";
			}
			else {
				// Now we need to update the thread's data to be in sync with the remaining posts.
				$lastpost = $db->query("SELECT * FROM posts WHERE thread='" . $db->real_escape_string($url[1]) . "' ORDER BY timestamp DESC LIMIT 1");
				if ((!$lastpost) or ($lastpost->num_rows == 0)) {
					echo "Something went wrong with resyncronizing the conversation. Perhaps there are no posts left?";
				}
				else {
					while ($row = $lastpost->fetch_assoc()) {
						$update = $db->query("UPDATE threads SET posts=posts-1, lastpostuser='" . $row["user"] . "', lastposttime='" . $row["timestamp"] . "' WHERE threadid='" . $db->real_escape_string($url[1]) . "'");
					}
					refresh(0);
				}
			}
		}
		// If the user is requesting to hide a post...
		elseif (($_POST["hide"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("UPDATE posts SET deletedby='" . $_SESSION["userid"] . "' WHERE postid='" . $db->real_escape_string($_POST["hide"]) . "'");
			
			if (!$result) {
				message("Sorry, post couldn't be hidden.");
			}
			
			else {
				refresh(0);
			}
		}
		// If the user is requesting to restore a post...
		elseif (isset($_POST["restore"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("UPDATE posts SET deletedby=NULL WHERE postid='" . $db->real_escape_string($_POST["restore"]) . "'");
			
			if (!$result) {
				message("Sorry, post couldn't be restored.");
			}
			else {
				refresh(0);
			}
		}
		// If the user is requesting to save an edit...
		elseif (isset($_POST["saveedit"])) {
			// First make sure the user has permission to edit the specified post.
			$permission = $db->query("SELECT user FROM posts WHERE postid='" . $db->real_escape_string($_POST["saveeditpostid"]) . "'");
			while ($p = $permission->fetch_assoc()) {
				if ((!$p["user"] == $_SESSION["userid"]) && ((!$_SESSION["role"] == "Moderator") or (!$_SESSION["role"] == "Administrator")))
				{
					message("Hey, you don't have permission to edit that post.");
				}
				
				else {
					$result = $db->query("UPDATE posts SET content='" . $db->real_escape_string($_POST["saveedit"]) . "' WHERE postid='" . $db->real_escape_string($_POST["saveeditpostid"]) . "'");
			
					if (!$result) {
						message("Sorry, post couldn't be restored.");
					}
					else {
						refresh(0);
					}
				}
			}
		}
		// If the user is requesting to delete the thread...
		elseif (isset($_POST["deletethread"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("DELETE FROM threads WHERE threadid='" . $db->real_escape_string($url[1]) . "'");
			
			if (!$result) {
				message("Sorry, thread couldn't be deleted.");
			}
			
			else {
				$result = $db->query("DELETE FROM posts WHERE thread='" . $db->real_escape_string($url[1]) . "'");
				
				if (!$result) {
					message("Sorry, the thread's posts couldn't be deleted.");
				}
				else {
					redirect("category/" . $category . "/");
				}
			}
		}
		// If the user is requesting to lock the thread...
		elseif (isset($_POST["lockthread"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("UPDATE threads SET locked='1' WHERE threadid='" . $db->real_escape_string($url[1]) . "'");
			
			if (!$result) {
				message("Sorry, thread couldn't be locked.");
			}
			else {
				refresh(0);
			}
		}
		// If the user is requesting to sticky the thread...
		elseif (isset($_POST["stickythread"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("UPDATE threads SET sticky='1' WHERE threadid='" . $db->real_escape_string($url[1]) . "'");
			
			if (!$result) {
				message("Sorry, thread couldn't be stickied.");
			}
			
			else {
				refresh(0);
			}
		}
		// If the user is requesting to unlock the thread...
		elseif (isset($_POST["unlockthread"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("UPDATE threads SET locked='0' WHERE threadid='" . $db->real_escape_string($url[1]) . "'");
			
			if (!$result) {
				message("Sorry, thread couldn't be unlocked.");
			}
			else {
				refresh(0);
			}
		}
		// If the user is requesting to unsticky the thread...
		elseif (isset($_POST["unstickythread"]) and (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
			$result = $db->query("UPDATE threads SET sticky='0' WHERE threadid='" . $db->real_escape_string($url[1]) . "'");
			
			if (!$result) {
				message("Sorry, thread couldn't be unstickied.");
			}
			else {
				refresh(0);
			}
		}
	}
}

echo '</br><a class="item" href="/category/' . $thread["category"] . '/">Back to category</a>';
if (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator")) {
    echo '<form style="display:inline;" action="" method="post"><button name="deletethread" class="threadbutton" value="' . $url[1] . '">Delete thread</button></form>';
    if ($thread["locked"]) {
        echo '<form style="display:inline;" action="" method="post"><button name="unlockthread" class="threadbutton" value="' . $url[1] . '">Unlock thread</button></form>';
    }
    else {
        echo '<form style="display:inline;" action="" method="post"><button name="lockthread" class="threadbutton" value="' . $url[1] . '">Lock thread</button></form>';
    }
    if ($thread["sticky"]) {
        echo '<form style="display:inline;" action="" method="post"><button name="unstickythread" class="threadbutton" value="' . $url[1] . '">Unsticky thread</button></form>';
    }
    else {
        echo '<form style="display:inline;" action="" method="post"><button name="stickythread" class="threadbutton" value="' . $url[1] . '">Sticky thread</button></form>';
    }
}
echo '<h2>Posts in ' . htmlspecialchars($thread["title"]) . '</h2>';

if ($thread["locked"] and $thread["sticky"]) {
    echo '<font class="sticky">Sticky</font> <font class="locked">Locked</font></br></br>';
}
elseif ($thread["locked"]) {
    echo '<font class="locked">Locked</font></br></br>';
}
elseif ($thread["sticky"]) {
    echo '<font class="sticky">Sticky</font></br></br>';
}

echo "<div class='paginationleft'>";
	
if ($currentPage == 1) {
    echo "<font color=white>First page</font> <font color=white>Previous page</font>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/1/'>First page</a> <a href='/thread/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}
	
echo "</div><div class='paginationright'>";
	
if ($currentPage == $pages) {
    echo "<font color=white>Next page</font> <font color=white>Last page</font>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/thread/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo "</div></br>";
				
while ($row = $posts_query->fetch_assoc()) {
    $userinfo = $db->query("SELECT * FROM users WHERE userid='" . $row["user"] . "'");
		
	while($u = $userinfo->fetch_assoc())
	{
		if (isset($row["deletedby"]))
		{
			$hider = $db->query("SELECT * FROM users WHERE userid='" . $row["deletedby"] . "'");
			
			while ($h = $hider->fetch_assoc())
			{
				echo '<div class="hiddenpost"><b><a href="/user/' . $u["userid"] . '/">' . htmlspecialchars($u["username"]) . "</a></b> <a title='" . date('m-d-Y h:i:s A', $row["timestamp"]) . "'>" . relativeTime($row["timestamp"]) . '</a> (hidden by <a href="/user/' . $row["deletedby"] . '/">' . $h["username"] . '</a>)';
				if (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator") or ($u["userid"] == $_SESSION["userid"]) && (!($_SESSION["role"] == "Suspended")) && ($_SESSION['signed_in'] == true))
				{
					echo '<form class="postc" action="" method="post"><button name="restore" value="' . $row["postid"] . '">Restore</button></form>';
				}
				echo '</div></br>';
			}
		}
		
		else {
			echo '<div postcolor="' . $u["color"] . '" class="thread">';
			echo '<b><a href="/user/' . $u["userid"] . '/">' . htmlspecialchars($u["username"]) . "</a></b> <a title='" . date('m-d-Y h:i:s A', $row["timestamp"]) . "'>" . relativeTime($row["timestamp"]) . "</a>";
			if ($_SESSION['signed_in'] and ((($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))
			or ($u["userid"] == $_SESSION["userid"]))) {
				echo '<form class="postc" action="" method="post"><button name="delete" value="' . $row["postid"] . '">Delete</button></form>';
				echo '<form class="postc" action="" method="post"><button name="hide" value="' . $row["postid"] . '">Hide</button></form>';
				echo '<form class="postc" action="" method="post"><button name="edit" value="' . $row["postid"] . '">Edit</button></form>';
			}
			
			if (isset($_POST["edit"]) && ($_POST["edit"] == $row["postid"]) && (!($_SESSION["role"] == "Suspended")) && ($_SESSION['signed_in'] == true)) {
				echo '</div><form method="post" action="">';				
				echo '<textarea name="saveedit" />' . ($row["content"]) . '</textarea><textarea style="display:none;" name="saveeditpostid">' . $row["postid"] . '</textarea></br><input type="submit" value="Save edit"></form></br>';
			}
			
			else {
				echo '</div><div class="threadcontent">' . htmlspecialchars($row["content"]) . '</div></br>';
			}
		}
	}
}

echo "<div class='paginationleft'>";
	
if ($currentPage == 1) {
    echo "<font color=white>First page</font> <font color=white>Previous page</font>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/1/'>First page</a> <a href='/thread/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}
	
echo "</div><div class='paginationright'>";
	
if ($currentPage == $pages) {
    echo "<font color=white>Next page</font> <font color=white>Last page</font>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/thread/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo "</div></br>";

if (!$_SESSION["signed_in"]) {
    message("You must be signed in to post.");
}
elseif ((!$thread["locked"]) or (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
    echo("<form method='post'>
    Content:<br><textarea name='content'>" . htmlspecialchars($_POST["content"] ?? "", ENT_NOQUOTES) . "</textarea></br>
    <input type='submit' value='Submit post'>
    </form>");
}
else {
    message("Sorry, this thread is locked. Only moderators and administrators can post in it.");
}

require "views/footer.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
    $action = "Viewing: <a href='/thread/" . $thread["threadid"] . "/'>" . $thread["title"] . "</a>";
    update_last_action($action);
}

?>
