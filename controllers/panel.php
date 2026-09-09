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
    require "views/error.php";
    exit();
}

function active($tab) {
    global $page;
    if ($tab == $page) return " paneltabactive";
    else return "";
}

$panelpages = array("newcategory", "extensions");
$page = "main";

if (isset($url[1])) {
    if (in_array($url[1], $panelpages)) {
        $page = $url[1];
        require "controllers/panel/{$url[1]}.php";
    }
    else {
        $title = "Not found";
        http_response_code(404);
        message("Panel page not found.");
        require "views/error.php";
        exit();
    }
}

require "views/panel.php";

?>
