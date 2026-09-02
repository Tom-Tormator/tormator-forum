<?php
// category.php
// Category view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

echo '<h2>Threads in ' . htmlspecialchars($cat["categoryname"]) . '</h2>';

if ($threads->num_rows > 0):

echo "<div class='paginationleft'>";
		
if ($currentPage == 1) {
    echo "<font color=white>First page</font> <font color=white>Previous page</font>";
}
else {
    echo "<a href='/category/" . $url[1] . "/1/'>First page</a> <a href='/category/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}
	
echo "</div><div class='paginationright'>";
	
if ($currentPage == $pages) {
    echo "<font color=white>Next page</font> <font color=white>Last page</font>";
}
else {
    echo "<a href='/category/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/category/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo "</div></br>";

echo '<table><tr><th>Thread</th><th>Posts</th><th>Created by</th><th>Last post</th></tr>';
				
while ($row = $threads->fetch_assoc()) {				
    echo '<tr><td class="leftpart"><b><a href="/thread/' . $row['threadid'] . '/">' . htmlspecialchars($row['title'], ENT_NOQUOTES) . "</a></b>";
    if ($row["locked"]) {
        echo ' <small><font class="locked">Locked</font></small></br></br>';
    }
    if ($row["sticky"]) {
        echo ' <small><font class="sticky">Sticky</font></small></br></br>';
    }
    
    $posts_query = $db->query("SELECT 1 FROM `posts` WHERE `thread`='" . $row["threadid"] . "'");
    $posts = $posts_query->num_rows;
    
    echo '</td><td><center>' . $posts . '</center></td><td>';
				
    $uinfo = $db->query("SELECT * FROM users WHERE userid='" . $row["startuser"] . "'");
				
    while ($u = $uinfo->fetch_assoc()) {
        echo '<a href="/user/' . $row['startuser'] . '/">' . $u['username'] . '</a>';
    }
				
    echo "</br><small><a title='" . date('m-d-Y h:i:s A', $row['starttime']) . "'>" . relativeTime($row["starttime"]) . "</a></small>";
				
    echo("</td><td>");
				
    $uinfo_query = $db->query("SELECT * FROM `users` WHERE `userid`='" . $row["lastpostuser"] . "'");
				
    $uinfo = $uinfo_query->fetch_assoc();
    echo("<a href='/user/" . $row["lastpostuser"] . "/'>" . htmlspecialchars($uinfo["username"], ENT_NOQUOTES) . "</a>");
				
    echo '</br><small><a title="' . date('m-d-Y h:i:s A', $row['lastposttime']) . '">' . relativeTime($row["lastposttime"]) . '</a></small></td></tr>';
}	
echo "</table></br>";
			
echo "<div class='paginationleft'>";
	
if ($currentPage == 1) {
    echo "<font color=white>First page</font> <font color=white>Previous page</font>";
}
else {
    echo "<a href='/category/" . $url[1] . "/1/'>First page</a> <a href='/category/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}
	
echo "</div><div class='paginationright'>";
	
if ($currentPage == $pages) {
    echo "<font color=white>Next page</font> <font color=white>Last page</font>";
}
else {
    echo "<a href='/category/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/category/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo "</div>";

endif;

require "views/footer.php";

?>
