<?php
// category.php
// Category view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if (($category->num_rows > 0) and ($threads->num_rows > 0)):

echo("<h2>" . htmlspecialchars($cat["categoryname"], ENT_NOQUOTES) . "</h2>");

if ($numThreads > $config["threadsPerPage"]) {
    renderPagination("category", $currentPage, $pages);
}

echo("<table>
 <tr>
  <th>Thread</th>
  <th>Posts</th>
  <th>Started by</th>
  <th>Last post</th>
 </tr>");
				
while ($row = $threads->fetch_assoc()) {				
    echo("<tr>
     <td class='leftpart'>
     <b><a href='" . makeURL("thread/{$row['threadid']}") . "'>" . htmlspecialchars($row["title"], ENT_NOQUOTES) . "</a></b>");
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
    
    echo("</td><td class='centered'>{$posts}</td><td>");
				
    $uinfo = $db->query("SELECT `username` FROM `users` WHERE `userid`='" . $row["startuser"] . "'");
    $u = $uinfo->fetch_assoc();
    
    echo("<a href='" . makeURL("user/{$row["startuser"]}") . "'>" . htmlspecialchars($u["username"], ENT_NOQUOTES) . "</a>");

    echo("<br><abbr title='" . date('m-d-Y h:i:s A', $row['starttime']) . "'>" . relativeTime($row["starttime"]) . "</abbr>");
				
    echo("</td><td>");
				
    $uinfo_query = $db->query("SELECT `username` FROM `users` WHERE `userid`='" . $row["lastpostuser"] . "'");		
    $uinfo = $uinfo_query->fetch_assoc();
    
    echo("<a href='" . makeURL("user/{$row["lastpostuser"]}") . "'>" . htmlspecialchars($uinfo["username"], ENT_NOQUOTES) . "</a>");
				
    echo '<br><abbr title="' . date('m-d-Y h:i:s A', $row['lastposttime']) . '">' . relativeTime($row["lastposttime"]) . '</abbr></td></tr>';
}	
echo "</table>";

if ($numThreads > $config["threadsPerPage"]) {
    renderPagination("category", $currentPage, $pages);
}

endif;

require "views/footer.php";

?>
