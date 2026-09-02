<?php
// thread.php
// Thread view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($display):

echo '</br><a class="item" href="/category/' . $thread["category"] . '/">Back to category</a>';
if (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator")) {
    echo("<div class='threadButtons'>");
    echo '<form method="post"><button name="deletethread" class="item" value="' . $url[1] . '">Delete thread</button></form>';
    if ($thread["locked"]) {
        echo '<form method="post"><button name="unlockthread" class="item" value="' . $url[1] . '">Unlock thread</button></form>';
    }
    else {
        echo '<form method="post"><button name="lockthread" class="item" value="' . $url[1] . '">Lock thread</button></form>';
    }
    if ($thread["sticky"]) {
        echo '<form method="post"><button name="unstickythread" class="item" value="' . $url[1] . '">Unsticky thread</button></form>';
    }
    else {
        echo '<form method="post"><button name="stickythread" class="item" value="' . $url[1] . '">Sticky thread</button></form>';
    }
    echo("</div>");
}
echo '<h2>Posts in ' . htmlspecialchars($thread["title"]) . '</h2>';

if ($thread["locked"]) {
    echo '<font class="locked">Locked</font></br></br>';
}
if ($thread["sticky"]) {
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
			echo '<div style="background: #' . $u["color"] . ';" class="thread">';
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

endif;

require "views/footer.php";

?>
