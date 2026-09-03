<?php
// category.php
// Category view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

echo '<h2>Threads in ' . htmlspecialchars($cat["categoryname"]) . '</h2>';

if ($threads->num_rows > 0):

echo("<div class='pagination'>");
echo("<div class='paginationleft'>");
		
if ($currentPage == 1) {
    echo "<span>First page</span> <span>Previous page</span>";
}
else {
    echo "<a href='/category/" . $url[1] . "/1/'>First page</a> <a href='/category/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}
	
echo "</div><div class='paginationright'>";
	
if ($currentPage == $pages) {
    echo "<span>Next page</span> <span>Last page</span>";
}
else {
    echo "<a href='/category/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/category/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo("</div>
</div>");

echo '<table><tr><th>Thread</th><th>Posts</th><th>Created by</th><th>Last post</th></tr>';
				
while ($row = $threads->fetch_assoc()) {				
    echo '<tr><td class="leftpart"><b><a href="/thread/' . $row['threadid'] . '/">' . htmlspecialchars($row['title'], ENT_NOQUOTES) . "</a></b>";
    echo(" <div class='labels'>");
    if ($row["locked"]) {
        echo '<span class="label locked">Locked</span>';
    }
    if ($row["sticky"]) {
        echo '<span class="label sticky">Sticky</span>';
    }
    echo("</div>");
    
    $posts_query = $db->query("SELECT 1 FROM `posts` WHERE `thread`='" . $row["threadid"] . "'");
    $posts = $posts_query->num_rows;
    
    echo '</td><td><center>' . $posts . '</center></td><td>';
				
    $uinfo = $db->query("SELECT * FROM users WHERE userid='" . $row["startuser"] . "'");
				
    while ($u = $uinfo->fetch_assoc()) {
        echo '<a href="/user/' . $row['startuser'] . '/">' . htmlspecialchars($u['username'], ENT_NOQUOTES) . '</a>';
    }

    echo "</br><small><a title='" . date('m-d-Y h:i:s A', $row['starttime']) . "'>" . relativeTime($row["starttime"]) . "</a></small>";
				
    echo("</td><td>");
				
    $uinfo_query = $db->query("SELECT * FROM `users` WHERE `userid`='" . $row["lastpostuser"] . "'");
				
    $uinfo = $uinfo_query->fetch_assoc();
    echo("<a href='/user/" . $row["lastpostuser"] . "/'>" . htmlspecialchars($uinfo["username"], ENT_NOQUOTES) . "</a>");
				
    echo '</br><small><a title="' . date('m-d-Y h:i:s A', $row['lastposttime']) . '">' . relativeTime($row["lastposttime"]) . '</a></small></td></tr>';
}	
echo "</table>";

echo("<div class='pagination'>");
echo("<div class='paginationleft'>");
	
if ($currentPage == 1) {
    echo "<span>First page</span> <span>Previous page</span>";
}
else {
    echo "<a href='/category/" . $url[1] . "/1/'>First page</a> <a href='/category/" . $url[1] . "/" . ($currentPage - 1) . "/'>Previous page</a>";
}
	
echo "</div><div class='paginationright'>";
	
if ($currentPage == $pages) {
    echo "<span>Next page</span> <span>Last page</span>";
}
else {
    echo "<a href='/category/" . $url[1] . "/" . ($currentPage + 1) . "/'>Next page</a> <a href='/category/" . $url[1] . "/" . $pages ."/'>Last page</a>";
}
	
echo("</div>
</div>");

endif;

require "views/footer.php";

?>
