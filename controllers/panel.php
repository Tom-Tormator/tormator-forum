<?php
// panel.php
// The admin panel, which is important for forum administration.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Admin panel";

if ($_SESSION["role"] != "Administrator") {
    message("Sorry, this page is unavailable to non-admins.");
}
else {
    message("This page is a work in progress.");
}

require "views/header.php";
require "views/footer.php";

?>
