<?php
// homepage.php
// Homepage view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($category_query->num_rows > 0):

?><div class='categories'>
<?php
while ($category = $category_query->fetch_assoc()) {
	$numthreads = $db->query("SELECT 1 FROM `threads` WHERE `category`='" . $category["categoryid"] . "'");
	echo("<div class='category'>
	<h3><a href='/category/" . $category["categoryid"] . "/'>" . htmlspecialchars($category["categoryname"], ENT_NOQUOTES) . '</a></h3>'
	. htmlspecialchars($category["categorydescription"], ENT_NOQUOTES) . "
	<br>
	Threads: {$numthreads->num_rows}
	</div>");
}
?>
</div>

<?php endif; require "views/footer.php"; ?>
