<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// newcategory.php
// Creates a new category.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "New category";
$success = false;

// Disallow non-admins from using this page.
if ($_SESSION["role"] != "Administrator") {
    message("This page is unavailable to non-admins.");
    require "views/newcategory.php";
}

// Check how many categories there are. If the limit has been reached show a message.
$catcheck = $db->query("SELECT 1 FROM categories");
if ($catcheck->num_rows >= $config["maxCats"]) {
    message("Sorry, no more new categories can be created at this time.");
    require "views/newcategory.php";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST["cat_name"] = $_POST["cat_name"] ?? "";
    $_POST["cat_description"] = $_POST["cat_description"] ?? "";
    
    // Ensure the name and description aren't empty.
    if (!$_POST["cat_name"]) {
        message("The category name cannot be blank.");
    }
    elseif (!$_POST["cat_description"]) {
        message("The category description cannot be blank.");
    }
    // Category names must be unique.
    elseif ($db->query("SELECT 1 FROM `categories` WHERE `categoryname`='" . $db->real_escape_string($_POST["cat_name"]) . "'")->num_rows > 0) {
        message("There is already a category with that name.");
    }
    else {
        $result = $db->query("INSERT INTO `categories` (`categoryname`, `categorydescription`) VALUES ('" . $db->real_escape_string($_POST['cat_name']) . "', '" . $db->real_escape_string($_POST['cat_description']) . "')");
    
        if (!$result) {
            message("Something went wrong.");
        }
        else {
            message("New category successfully added. Return to the <a href='/'>main page</a>?");
            $success = true;
        }
    }
}

require "views/newcategory.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
	update_last_action("Creating a category");
}

?>
