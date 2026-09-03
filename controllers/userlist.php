<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// userlist.php
// Displays a list of all the users on the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Userlist";

// Start off by making a query for our list.
$users_query = $db->query("SELECT * FROM users ORDER BY userid ASC");

if ($users_query->num_rows < 1) {
    message("Sadly, there are currently no users on the forum.");
}

require "views/userlist.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
    update_last_action("Viewing: Userlist");
}

?>
