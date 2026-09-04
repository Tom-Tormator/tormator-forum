<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// category.php
// Shows all the threads inside a category.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

// Get the category information.
$category = $db->query("SELECT * FROM categories WHERE categoryid='" . $db->real_escape_string($url[1]) . "'");

if ($category->num_rows < 1) {
    http_response_code(404);
    $title = "Not found";
    message("This category does not exist.", "error");
    require "views/error.php";
    exit();
}

$cat = $category->fetch_assoc();

$title = $cat["categoryname"];

// Find out what page we're on.
if (isset($url[2]) and is_numeric($url[2])) {
    $currentPage = $url[2];
    if ($currentPage < 1) $currentPage = 1;
}
// If no valid page is specified then always assume we're on the first page.
else {
	$currentPage = 1;
}

$threads = $db->query("SELECT 1 FROM `threads` WHERE `category`='" . $db->real_escape_string($url[1]) . "'");

// Important details for sorting the threads into pages.
$numThreads = $threads->num_rows;
$pages = ceil($numThreads / $config["threadsPerPage"]);

if ($currentPage > $pages) $currentPage = $pages;

// Calculate the offset for the threads query.
$offset = (($currentPage * $config["threadsPerPage"]) - $config["threadsPerPage"]);
if ($offset < 0) $offset = 0;

$posts = $db->query("SELECT * FROM `threads` WHERE `category`='" . $db->real_escape_string($url[1]) . "' ORDER BY `lastposttime` LIMIT " . $config["postsPerPage"] . " OFFSET " . $offset . "");

$threads = $db->query("SELECT * FROM threads WHERE category='" . $db->real_escape_string($url[1]) . "' ORDER BY sticky DESC, lastposttime DESC LIMIT " . $config["threadsPerPage"] . " OFFSET " . $offset . "");

if ($threads->num_rows < 1) {
    message("There are no threads in this category yet.");
    require "views/error.php";
    exit();
}

require "views/category.php";

?>
