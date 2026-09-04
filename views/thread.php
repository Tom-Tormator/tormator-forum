<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// thread.php
// Thread view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($thread_query->num_rows > 0):

echo("<div class='threadnavbar'>");
echo("<a class='item' href='" . makeURL("category/{$thread["category"]}") . "'>Back to category</a>");
echo("<div class='threadButtons'>");
if (isMod()) {
    echo "<form method='post'>
     <input type='hidden' name='token' value='{$_SESSION["token"]}'>
     <button name='deletethread' class='item'>Delete thread</button>
     <button name='togglelock' class='item'>" . ($thread["locked"] ? "Unlock" : "Lock") . "</button>
     <button name='togglesticky' class='item'>" . ($thread["sticky"] ? "Unsticky" : "Sticky") . "</button>
    </form>";
}
elseif ($thread["startuser"] == $_SESSION["userid"]) {
    echo "<form method='post'>
     <input type='hidden' name='token' value='{$_SESSION["token"]}'>
     <button name='deletethread' class='item'>Delete thread</button>
    </form>";
}
echo("</div>
</div>");

echo("<h2>" . htmlspecialchars($thread["title"], ENT_NOQUOTES) . "</h2>");

// *** Labels. ***
echo("<div class='labels'>");
if ($thread["locked"]) {
    echo '<div class="label locked">Locked</div>';
}
if ($thread["sticky"]) {
    echo '<div class="label sticky">Sticky</div>';
}
echo("</div>");

if ($posts_query->num_rows > 0):

if ($numPosts > $config["postsPerPage"]) {
    renderPagination("thread", $currentPage, $pages);
}

echo("<div class='posts'>");			
while ($row = $posts_query->fetch_assoc()) {
    echo("<div class='post'>");
    $userinfo = $db->query("SELECT * FROM `users` WHERE `userid`='" . $row["user"] . "'");
		
	$u = $userinfo->fetch_assoc();
	if (isset($row["deletedby"])) {
		$hider = $db->query("SELECT * FROM `users` WHERE `userid`='" . $row["deletedby"] . "'");
		
		$h = $hider->fetch_assoc();
        echo("<div class='postheader hiddenpost'>
        <a href='" . makeURL("user/{$u["userid"]}") . "'>" . htmlspecialchars($u["username"], ENT_NOQUOTES) . "</a>&nbsp;
        <abbr title='" . date('m-d-Y h:i:s A', $row["timestamp"]) . "'>" . relativeTime($row["timestamp"]) . "</abbr>
        &nbsp;(hidden by&nbsp;<a href='" . makeURL("user/{$row["deletedby"]}") . "'>" . htmlspecialchars($h["username"], ENT_NOQUOTES) . "</a>)");
        if ($_SESSION["signed_in"]
        and (($row["deletedby"] == $_SESSION["userid"]) or isMod())) {
            echo("<div class='postbuttons'>
            <form class='postc' method='post'>
             <input type='hidden' name='token' value='{$_SESSION["token"]}'>
             <button class='item' name='restore' value='{$row["postid"]}'>Restore</button>
            </form>
            </div>");
        }
        echo("</div>");
	}
	else {
		echo("<div style='background-color: #" . $u["color"] . ";' class='postheader'>");
		echo("<a href='" . makeURL("user/{$u["userid"]}") . "'>" . htmlspecialchars($u["username"]) . "</a>&nbsp;
		<abbr title='" . date('m-d-Y h:i:s A', $row["timestamp"]) . "'>" . relativeTime($row["timestamp"]) . "</abbr>");
		if ($_SESSION["signed_in"]
		and (isMod() or ($u["userid"] == $_SESSION["userid"]))) {
		    echo("<div class='postbuttons'>
		    <form class='postc' method='post'>
		     <input type='hidden' name='token' value='{$_SESSION["token"]}'>
		     <button class='item' name='delete' value='{$row["postid"]}'>Delete</button>
		     <button class='item' name='hide' value='{$row["postid"]}'>Hide</button>
		     <button class='item' name='edit' value='{$row["postid"]}'>Edit</button>
		    </form>
		    </div>");
		}
		
		if (isset($_POST["edit"])
		and ($_POST["edit"] == $row["postid"])
		and $_SESSION["signed_in"]
		and (isMod() or ($row["user"] == $_SESSION["userid"]))) {
			echo "</div>
			<form method='post'>
			 <input type='hidden' name='token' value='{$_SESSION["token"]}'>
			 <textarea name='saveedit' class='postTextbox'>" . htmlspecialchars($row["content"], ENT_NOQUOTES) . "</textarea>
			 <input type='hidden' name='saveeditpostid' value='{$row["postid"]}'>
			 <div class='editbuttons'>
			 <input type='submit' class='item' value='Save edit'>
			</form>
			<br>
			<form method='post'>
			 <input type='hidden' name='token' value='{$_SESSION["token"]}'>
			 <button class='item' name='canceledit'>Cancel edit</button>
			</form>
			</div>";
		}
		
		else {
			echo '</div><div class="postcontent">' . htmlspecialchars($row["content"]) . '</div>';
		}
	}
	echo("</div>");
}
echo("</div>");

if ($numPosts > $config["postsPerPage"]) {
    renderPagination("thread", $currentPage, $pages);
}

if (!$_SESSION["signed_in"]) {
    message("You must be signed in to post.");
}
elseif ((!$thread["locked"]) or (($_SESSION["role"] == "Moderator") or ($_SESSION["role"] == "Administrator"))) {
    echo("<form method='post'>
     <input type='hidden' name='token' value='{$_SESSION["token"]}'>
     <label for='newpost'>New post:</label>
     <textarea name='content' class='postTextbox' id='newpost'>" . htmlspecialchars($_POST["content"] ?? "", ENT_NOQUOTES) . "</textarea>
     <input class='item' type='submit' value='Submit post'>
    </form>");
}
else {
    message("Sorry, this thread is locked. Only moderators and administrators can post in it.");
}

endif;
endif;

require "views/footer.php";

?>
