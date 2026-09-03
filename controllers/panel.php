<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

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

require "views/panel.php";

?>
