<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// categories.php
// Allows the admin to manage categories.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Manage categories";

// Disallow non-admins from using this page.
if ($_SESSION["role"] != "Administrator") {
    message("This page is unavailable to non-admins.", "error");
    require "views/error.php";
    exit();
}

// Check how many categories there are. If the limit has been reached show a message.
$catcheck = $db->query("SELECT 1 FROM `categories`");
if ($catcheck->num_rows >= $config["maxCats"]) {
    message("Sorry, no more new categories can be created at this time.", "error");
    require "views/error.php";
    exit();
}

if (validateToken()) {
    if (isset($_POST["newcategory"])) {
        $_POST["cat_name"] = $_POST["cat_name"] ?? "";
        $_POST["cat_description"] = $_POST["cat_description"] ?? "";
        
        // Ensure the name and description aren't empty.
        if (!$_POST["cat_name"]) {
            message("The category name cannot be blank.", "error");
        }
        elseif (!$_POST["cat_description"]) {
            message("The category description cannot be blank.", "error");
        }
        // Category names must be unique.
        elseif ($db->query("SELECT 1 FROM `categories` WHERE `name`='" . $db->real_escape_string($_POST["cat_name"]) . "'")->num_rows > 0) {
            message("There is already a category with that name.", "error");
        }
        else {
            $result = $db->query("INSERT INTO `categories`
            (`name`, `description`, `order`) VALUES
            ('" . $db->real_escape_string($_POST["cat_name"]) . "', '" . $db->real_escape_string($_POST["cat_description"]) . "', '0')");
        
            if (!$result) {
                message("Something went wrong.", "error");
            }
            else {
                message("New category successfully added. Return to the <a href='" . makeURL("") . "'>main page</a>?", "success");
                unset($_POST["cat_name"]);
                unset($_POST["cat_description"]);
            }
        }
    }
    elseif (isset($_POST["delete"])) {
        $cat = $db->query("SELECT `id` FROM `categories` WHERE `id`='" . $db->real_escape_string($_POST["delete"]) . "'");
        if ($cat->num_rows < 1) {
            message("Category not found.", "error");
        }
        else {
            // Just delete the category and let the ON DELETE CASCADE do the rest.
            $success = $db->query("DELETE FROM `categories` WHERE `id`='" . $db->real_escape_string($_POST["delete"]) . "'");
            if (!$success) {
                message("Failed to delete category.", "error");
            }
            else {
                message("Successfully deleted category.", "success");
            }
        }
    }
}

$categoriesQuery = $db->query("SELECT * FROM `categories`");

?>
