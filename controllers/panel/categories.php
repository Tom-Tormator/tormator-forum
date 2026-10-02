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
        if (strlen($_POST["cat_name"]) < 1) {
            message("The category name cannot be blank.", "error");
        }
        elseif (strlen($_POST["cat_description"]) < 1) {
            message("The category description cannot be blank.", "error");
        }
        elseif (strlen($_POST["cat_name"]) > 32) {
            message("The category name cannot be longer than 32 characters.", "error");
        }
        elseif (strlen($_POST["cat_description"]) > 255) {
            message("The category description cannot be longer than 255 characters.", "error");
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
        // Just delete the category and let the ON DELETE CASCADE do the rest.
        $success = $db->query("DELETE FROM `categories` WHERE `id`='" . $db->real_escape_string($_POST["delete"]) . "'");
        if (!$success) {
            message("Failed to delete category.", "error");
        }
        else {
            message("Successfully deleted category.", "success");
        }
    }
    elseif (isset($_POST["saveedit"])) {
        $_POST["name"] = $_POST["name"] ?? "";
        $_POST["desc"] = $_POST["desc"] ?? "";
        $_POST["id"] = $_POST["id"] ?? "";
        
        // Do this so the edit form doesn't go away.
        $_POST["edit"] = $_POST["id"];
        
        $catQuery = $db->query("SELECT * FROM `categories` WHERE `id`='" . $db->real_escape_string($_POST["id"]) . "'");
        $cat = $catQuery->fetch_assoc();
        
        // The category must exist.
        if ($catQuery->num_rows < 1) {
            message("The category does not exist.", "error");
        }
        // Ensure the name and description aren't empty.
        elseif (strlen($_POST["name"]) < 1) {
            message("The category name cannot be blank.", "error");
        }
        elseif (strlen($_POST["desc"]) < 1) {
            message("The category description cannot be blank.", "error");
        }
        // Impose max length constraints.
        elseif (strlen($_POST["name"]) > 32) {
            message("The category name cannot be longer than 32 characters.", "error");
        }
        elseif (strlen($_POST["desc"]) > 255) {
            message("The category description cannot be longer than 255 characters.", "error");
        }
        // Category names must be unique.
        elseif ($db->query("SELECT 1 FROM `categories` WHERE `name`='" . $db->real_escape_string($_POST["name"]) . "' AND `id`<>'" . $db->real_escape_string($_POST["id"]) . "'")->num_rows > 0) {
            message("There is already a category with that name.", "error");
        }
        elseif (($cat["name"] == $_POST["name"]) and ($cat["description"] == $_POST["desc"])) {
            message("Nothing to change.", "info");
        }
        else {
            $result = $db->query("UPDATE `categories` SET `name`='" . $db->real_escape_string($_POST["name"]) . "', `description`='" . $db->real_escape_string($_POST["desc"]) . "' WHERE `id`='" . $db->real_escape_string($_POST["id"]) . "'");
        
            if (!$result) {
                message("Something went wrong.", "error");
            }
            else {
                message("Successfully edited category.", "success");
                unset($_POST["edit"]);
            }
        }
    }
}

$categoriesQuery = $db->query("SELECT * FROM `categories`");

?>
