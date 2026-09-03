<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// homepage.php
// Initializes the home page.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$category_query = $db->query("SELECT * FROM categories");

if ($category_query->num_rows < 1) {
    message("No categories to display.");
}

require "views/homepage.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
	update_last_action("Viewing: Homepage");
}

?>
