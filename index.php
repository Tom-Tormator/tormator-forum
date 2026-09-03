<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// index.php
// Initializes the forum and loads the requested page.

// Define a constant to ensure pages are only loaded through this index file.
define("INDEXED", "1");

// Disable the output buffer to avoid screen tears.
ob_end_clean();

$messages = array();

// Load up the config.
require "core/default_config.php";
if (file_exists("config/config.php")) require "config/config.php";
else $config = array();
$config = array_merge($default_config, $config);

if ($config["installed"]) $db = mysqli_connect($config["MySQLServer"], $config["MySQLUser"],  $config["MySQLPass"], $config["MySQLDatabase"]);

require "core/functions.php";

// If a session doesn't exist, set one.
if (!session_id()) {
    session_name($config["cookiePrefix"] . "Session");
    session_start();
}

// These need to be set explicitly to avoid warnings later on.
if (!isset($_SESSION["signed_in"])) $_SESSION["signed_in"] = false;
if (!isset($_SESSION["role"])) $_SESSION["role"] = "Guest";
if (!isset($_SESSION["userid"])) $_SESSION["userid"] = "0";

// Check the user's role and ensure their session reflects it accordingly.
if ($config["installed"] and $_SESSION["signed_in"]) {
    $rolecheck = $db->query("SELECT `role`, `verified` FROM `users` WHERE `userid`='" . $_SESSION["userid"] . "'");
    $rc = $rolecheck->fetch_assoc();
    // Probably will remove this later TODO
    if ($rc["role"] != $_SESSION["role"]) {
        $_SESSION["role"] = $rc["role"];
    }
    // Suspended and unverified users are not allowed to be logged in.
    if (($rc["role"] == "Suspended") or (!$rc["verified"])) {
        logout();
    }
}

// Log out any users if the forum isn't installed.
if (!$config["installed"] and $_SESSION["signed_in"]) {
    logout();
}

// Process the URL.
$url = explode("/", ($_GET["url"] ?? ""));

$pages = array("signup", "login", "newcategory", "newthread", "category", "thread", "userlist", "user", "settings", "panel");

$title = "";

// Based on the URL, serve the user with a corresponding page.
if (!$config["installed"]) require "core/install.php";
elseif (!$url[0]) require "controllers/homepage.php";
elseif (in_array($url[0], $pages)) require "controllers/{$url[0]}.php";
elseif ($url[0] == "logout") logout();
else {
    http_response_code(404);
    $title = "Not found";
    message("Error: requested page not found.");
    require "controllers/homepage.php";
}

?>
