<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// extensions.php
// Allows admins to manage extensions.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Extensions";

// Disallow non-admins from using this page.
if ($_SESSION["role"] != "Administrator") {
    message("This page is unavailable to non-admins.", "error");
    require "views/error.php";
    exit();
}

if (validateToken()) {
    if (isset($_POST["Enable"])) {
        if (array_key_exists($_POST["Enable"], $extensions) and (!in_array($_POST["Enable"], $loaded_extensions))) {
            $loaded_extensions[] = $_POST["Enable"];
            flushExtensionConfig();
            refresh(0);
        }
        else {
            message("Invalid extension.", "error");
        }
    }
    elseif (isset($_POST["Disable"])) {
        if (array_key_exists($_POST["Disable"], $extensions) and in_array($_POST["Disable"], $loaded_extensions)) {
            unset($loaded_extensions[array_search($_POST["Disable"], $loaded_extensions)]);
            flushExtensionConfig();
            refresh(0);
        }
        else {
            message("Invalid extension.", "error");
        }
    }
}

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"]) {
	update_last_action("Managing extensions");
}

?>
