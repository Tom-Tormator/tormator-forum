<?php
// index.php
// Initializes the forum and loads the requested page.

// Define a constant to ensure pages are only loaded through this index file.
define("INDEXED", "1");

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

if (!isset($_SESSION["signed_in"])) $_SESSION["signed_in"] = false;
if (!isset($_SESSION["role"])) $_SESSION["role"] = "Guest";

// Check the user's role and ensure their session reflects it accordingly.
if ($config["installed"] and ($_SESSION["signed_in"] == true)) {
    $rolecheck = $db->query("SELECT role FROM users WHERE userid='" . $_SESSION["userid"] . "'");
    while ($r = $rolecheck->fetch_assoc()) {
        if (!$r["role"] == $_SESSION["role"]) {
            $_SESSION["role"] == $r["role"];
        }
    }
}

// Process the URL.
$url = explode("/", ($_GET["url"] ?? ""));

$pages = array("signup", "login", "newcategory", "newthread", "category", "thread", "userlist", "user", "settings", "panel");

// Based on the URL, serve the user with a corresponding page.
if (!$config["installed"]) require "core/install.php";
elseif (!$url[0]) require "controllers/homepage.php";
elseif (in_array($url[0], $pages)) require "controllers/{$url[0]}.php";
elseif ($url[0] == "logout") logout();
else {
    http_response_code(404);
    message("Error: requested page not found.");
    require "controllers/homepage.php";
}

?>
