<?php
// homepage.php
// Initializes the home page.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

$category_query = $db->query("SELECT * FROM categories");

if ($category_query->num_rows < 1) {
    message("No categories to display.");
}

echo("<div class='categories'>");
while ($category = $category_query->fetch_assoc()) {
	$numthreads = $db->query("SELECT 1 FROM `threads` WHERE `category`='" . $category["categoryid"] . "'");
	echo("<div class='category'>
	<h3><a href='/category/" . $category["categoryid"] . "/'>" . htmlspecialchars($category["categoryname"]) . '</a></h3>'
	. $category["categorydescription"] . "
	<br>
	Threads: {$numthreads->num_rows}
	</div>");
}
echo("</div>");

require "views/footer.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"])
{
	update_last_action("Viewing: Homepage");
}

?>
