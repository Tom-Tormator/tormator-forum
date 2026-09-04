<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// homepage.php
// Homepage view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

?><table class='categories'>
 <tr>
  <th>Category</th>
  <th>Threads</th>
  <th>Posts</th>
  <th>Last post</th>
 </tr>
<?php
while ($category = $category_query->fetch_assoc()) {
	$numthreads = $db->query("SELECT 1 FROM `threads` WHERE `category`='" . $category["categoryid"] . "'");
	// Cleft join to count posts in each category.
	$numPosts = $db->query("SELECT count(*) FROM `threads` LEFT JOIN `posts` ON `posts`.`thread`=`threads`.`threadid` WHERE `threads`.`category`='" . $category["categoryid"] . "'");
	$postCount = $numPosts->fetch_assoc()["count(*)"];
	// Cleft join to get the latest post in each category.
	$lastPostQuery = $db->query("SELECT `posts`.`user`, `posts`.`timestamp` FROM `posts` LEFT JOIN `threads` ON `posts`.`thread`=`threads`.`threadid` WHERE `threads`.`category`='" . $category["categoryid"] . "' ORDER BY
`posts`.`timestamp` DESC LIMIT 1;");
    $lastPost = $lastPostQuery->fetch_assoc();
    if ($lastPost) {
        $lastPostUserQuery = $db->query("SELECT `username` FROM `users` WHERE `userid`='" . $lastPost["user"] . "'");
        $lastPostUser = $lastPostUserQuery->fetch_assoc();
    }
	echo("<tr class='category'>
	<td class='leftpart'>
	<a href='" . makeURL("category/" . $category["categoryid"]) . "' class='categoryname'>" . htmlspecialchars($category["categoryname"], ENT_NOQUOTES) . "</a>
	<br>"
	. htmlspecialchars($category["categorydescription"], ENT_NOQUOTES) . "
	</td>
	<td class='centered'>
	 {$numthreads->num_rows}
	</td>
	<td class='centered'>
	 {$postCount}
	</td>
	<td>");
	 if ($postCount > 0) {
        echo("<a href='" . makeURL("user/{$lastPost["user"]}") . "'>" . htmlspecialchars($lastPostUser["username"], ENT_NOQUOTES) . "</a>
        <br>
        <abbr title='" . date("m-d-Y h:i:s A", $lastPost["timestamp"]) . "'>" . relativeTime($lastPost["timestamp"]) . "</abbr>");
     }
     else {
        echo("Nobody
        <br>
        Never");
     }
	echo("</td>
	</tr>");
}
?>
</table>

<?php require "views/footer.php"; ?>
