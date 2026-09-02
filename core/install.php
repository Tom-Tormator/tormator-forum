<?php
// install.php
// Installs the software.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Install";

if (!is_writable("config/")) {
    message("Config directory isn't writable.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    
    if (!is_writable("config/")) {
        $errors[] = "Config directory isn't writable.";
    }
    
    try {
        $db = mysqli_connect($_POST["SQLHost"], $_POST["SQLUser"],  $_POST["SQLPass"], $_POST["SQLDB"]);
    }
    catch (Exception $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
    
    if (count($errors) != 0) {
        foreach ($errors as $error) {
            message($error);
        }
    }
    else {
        $db->query("CREATE TABLE IF NOT EXISTS `categories` (
            `categoryid` int unsigned NOT NULL AUTO_INCREMENT,
            `categoryname` varchar(255) NOT NULL,
            `categorydescription` varchar(255) NOT NULL,
            PRIMARY KEY (`categoryid`),
            UNIQUE KEY `category_name` (`categoryname`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->query("CREATE TABLE IF NOT EXISTS `posts` (
            `postid` int unsigned NOT NULL AUTO_INCREMENT,
            `thread` int unsigned NOT NULL,
            `user` int unsigned NOT NULL,
            `timestamp` int unsigned NOT NULL,
            `editedby` int unsigned DEFAULT NULL,
            `edittime` int unsigned DEFAULT NULL,
            `deletedby` int unsigned DEFAULT NULL,
            `content` text NOT NULL,
            PRIMARY KEY (`postid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->query("CREATE TABLE IF NOT EXISTS `threads` (
            `threadid` int unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `sticky` tinyint(1) NOT NULL DEFAULT '0',
            `locked` tinyint(1) NOT NULL DEFAULT '0',
            `pinned` tinyint(1) NOT NULL DEFAULT '0',
            `draft` tinyint(1) NOT NULL DEFAULT '0',
            `startuser` int unsigned NOT NULL,
            `starttime` bigint NOT NULL,
            `lastpostuser` int unsigned NOT NULL,
            `lastposttime` bigint NOT NULL,
            `category` int unsigned NOT NULL,
            PRIMARY KEY (`threadid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->query("CREATE TABLE IF NOT EXISTS `users` (
            `userid` int unsigned NOT NULL AUTO_INCREMENT,
            `username` varchar(26) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` varchar(128) NOT NULL,
            `role` enum('Administrator','Moderator','Member','Suspended') NOT NULL DEFAULT 'Member',
            `jointime` bigint NOT NULL,
            `lastactive` bigint DEFAULT NULL,
            `lastaction` varchar(255) DEFAULT NULL,
            `color` char(6) NOT NULL DEFAULT 'add8e6',
            `joinip` char(64) NOT NULL,
            `ip` char(64) NOT NULL,
            `verified` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`userid`),
            UNIQUE KEY `user_name` (`username`),
            UNIQUE KEY `user_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        message("Database successfully written.");
        $config["installed"] = true;
        $config["MySQLServer"] = $_POST["SQLHost"];
        $config["MySQLUser"] = $_POST["SQLUser"];
        $config["MySQLPass"] = $_POST["SQLPass"];
        $config["MySQLDatabase"] = $_POST["SQLDB"];
        flushConfig();
    }
}

require "views/header.php";

if (!$config["installed"]) {
    echo("<h2>Install</h2>
    <form method='post' class='form'>
        <label for='SQLHost'>SQL Host:</label>
        <input type='text' name='SQLHost' id='SQLHost' value='" . htmlspecialchars($_POST["SQLHost"] ?? "") . "' placeholder='localhost'>
        <label for='SQLUser'>SQL User:</label>
        <input type='text' name='SQLUser' id='SQLUser' value='" . htmlspecialchars($_POST["SQLUser"] ?? "") . "'>
        <label for='SQLPass'>SQL Password:</label>
        <input type='password' name='SQLPass' id='SQLPass' value='" . htmlspecialchars($_POST["SQLPass"] ?? "") . "'>
        <label for='SQLDB'>SQL Database:</label>
        <input type='text' name='SQLDB' id='SQLDB' value='" . htmlspecialchars($_POST["SQLDB"] ?? "") . "'>
        <br>
        <input type='submit' value='Install'>
    </form>");
}

require "views/footer.php";

?>
