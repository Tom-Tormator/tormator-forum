<?php
// thread.php
// Thread view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($display):

echo("<div class='threadnavbar'>");
echo '<a class="item" href="/category/' . $thread["category"] . '/">Back to category</a>';
echo("<div class='threadButtons'>");
if (isMod()) {
    echo "<form method='post'><button name='deletethread' class='item'>Delete thread</button></form>";
    echo "<form method='post'><button name='togglelock' class='item'>" . ($thread["locked"] ? "Unlock" : "Lock") . "</button></form>";
    echo "<form method='post'><button name='togglesticky' class='item'>" . ($thread["sticky"] ? "Unsticky" : "Sticky") . "</button></form>";
}
elseif ($thread["startuser"] == $_SESSION["userid"]) {
    echo '<form method="post"><button name="deletethread" class="item">Delete thread</button></form>';
}
echo("</div>
</div>");
echo '<h2>Posts in ' . htmlspecialchars($thread["title"]) . '</h2>';

echo("<div class='labels'>");
if ($thread["locked"]) {
    echo '<div class="label locked">Locked</div>';
}
if ($thread["sticky"]) {
    echo '<div class="label sticky">Sticky</div>';
}
echo("</div>");

echo("<div class='pagination'>");
echo "<div class='paginationleft'>";
	
if ($currentPage == 1) {
    echo "<span>First page</span> <span>Previous page</span>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/1/'>First page</a> <a href='/thread/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}
	
echo "</div><div class='paginationright'>";

if ($currentPage == $pages) {
    echo "<span>Next page</span> <span>Last page</span>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/thread/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo("</div>
</div>");

echo("<div class='posts'>");			
while ($row = $posts_query->fetch_assoc()) {
    echo("<div class='post'>");
    $userinfo = $db->query("SELECT * FROM `users` WHERE `userid`='" . $row["user"] . "'");
		
	$u = $userinfo->fetch_assoc();
	if (isset($row["deletedby"])) {
		$hider = $db->query("SELECT * FROM `users` WHERE `userid`='" . $row["deletedby"] . "'");
		
		$h = $hider->fetch_assoc();
        echo '<div class="postheader hiddenpost"><a href="/user/' . $u["userid"] . '/">' . htmlspecialchars($u["username"], ENT_NOQUOTES) . "</a>&nbsp;<abbr title='" . date('m-d-Y h:i:s A', $row["timestamp"]) . "'>" . relativeTime($row["timestamp"]) . '</abbr>&nbsp;(hidden by&nbsp;<a href="/user/' . $row["deletedby"] . '/">' . htmlspecialchars($h["username"], ENT_NOQUOTES) . '</a>)';
        if ($_SESSION["signed_in"]
        and (($row["deletedby"] == $_SESSION["userid"]) or isMod())) {
            echo("<div class='postbuttons'>");
            echo '<form class="postc" action="" method="post"><button name="restore" value="' . $row["postid"] . '">Restore</button></form>';
            echo("</div>");
        }
        echo "</div>";
	}
	else {
		echo '<div style="background-color: #' . $u["color"] . ';" class="postheader">';
		echo '<a href="/user/' . $u["userid"] . '/">' . htmlspecialchars($u["username"]) . "</a>&nbsp;<abbr title='" . date('m-d-Y h:i:s A', $row["timestamp"]) . "'>" . relativeTime($row["timestamp"]) . "</abbr>";
		if ($_SESSION["signed_in"]
		and (isMod() or ($u["userid"] == $_SESSION["userid"]))) {
		    echo("<div class='postbuttons'>");
			echo '<form class="postc" method="post"><button name="delete" value="' . $row["postid"] . '">Delete</button></form>';
			echo '<form class="postc" method="post"><button name="hide" value="' . $row["postid"] . '">Hide</button></form>';
			echo '<form class="postc" method="post"><button name="edit" value="' . $row["postid"] . '">Edit</button></form>';
			echo("</div>");
		}
		
		if (isset($_POST["edit"])
		and ($_POST["edit"] == $row["postid"])
		and $_SESSION["signed_in"]
		and (isMod() or ($row["user"] == $_SESSION["userid"]))) {
			echo "</div><form method='post'>";
			echo "<textarea name='saveedit' class='postTextbox'>" . htmlspecialchars($row["content"], ENT_NOQUOTES) . "</textarea><input type='hidden' name='saveeditpostid' value='" . $row["postid"] . "'><br><input type='submit' value='Save edit'></form><br>";
		}
		
		else {
			echo '</div><div class="postcontent">' . htmlspecialchars($row["content"]) . '</div>';
		}
	}
	echo("</div>");
}
echo("</div>");

echo("<div class='pagination'>");
echo "<div class='paginationleft'>";
	
if ($currentPage == 1) {
    echo "<span>First page</span> <span>Previous page</span>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/1/'>First page</a> <a href='/thread/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}

echo "</div><div class='paginationright'>";
	
if ($currentPage == $pages) {
    echo "<span>Next page</span> <span>Last page</span>";
}
else {
    echo "<a href='/thread/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/thread/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo("</div>
</div>");

if (!$_SESSION["signed_in"]) {
    message("You must be signed in to post.");
}
elseif ((!$thread["locked"]) or (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
    echo("<form method='post'>
    <label for='newpost'>New post:</label>
    <textarea name='content' class='postTextbox' id='newpost'>" . htmlspecialchars($_POST["content"] ?? "", ENT_NOQUOTES) . "</textarea>
    <input type='submit' value='Submit post'>
    </form>");
}
else {
    message("Sorry, this thread is locked. Only moderators and administrators can post in it.");
}

endif;

require "views/footer.php";

?>
