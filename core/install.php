<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// install.php
// Installs the software.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Install";

if (!is_writable("config/")) {
    message("Config directory isn't writable.");
}

if (validateToken()) {
    $errors = array();
    
    if (!is_writable("config/")) {
        $errors[] = "Config directory isn't writable.";
    }
    
    $uinvalid = validateUsername($_POST["username"] ?? "");
    if ($uinvalid) $errors[] = $uinvalid;
    
    $einvalid = validateEmail($_POST["email"] ?? "");
    if ($einvalid) $errors[] = $einvalid;
    
    $pinvalid = validatePassword($_POST["password"] ?? "", $_POST["confirmpassword"] ?? "");
    if ($pinvalid) $errors[] = $pinvalid;
    
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
        if (($_POST["overwrite"] ?? "") == "on") {
            $db->query("DROP TABLE IF EXISTS `categories`");
            $db->query("DROP TABLE IF EXISTS `posts`");
            $db->query("DROP TABLE IF EXISTS `threads`");
            $db->query("DROP TABLE IF EXISTS `users`");
        }
        
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
        
        // Make a default category.
        $db->query("REPLACE INTO `categories` (`categoryname`, `categorydescription`) VALUES ('General', 'Discuss general topics here.')");
        
        $now = time();
        $ip = $db->real_escape_string(hash("sha256", $_SERVER["REMOTE_ADDR"]));
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        
        // Create the admin's account.
        $db->query("REPLACE INTO `users` (`username`, `email`, `password`, `role`, `jointime`, `lastactive`, `joinip`, `ip`, `verified`) VALUES ('" . $db->real_escape_string($_POST["username"]) . "', '" . $db->real_escape_string($_POST["email"]) . "', '" . $db->real_escape_string($password) ."', 'Administrator', '{$now}', '{$now}', '{$ip}', '{$ip}' ,'1')");
        $config["mainAdmin"] = $db->insert_id;
        
        // Figure out if we are installing to a subdirectory.
        $querystart = strpos($_SERVER["REQUEST_URI"], "?");
        if ($querystart === false) $querystart = null;
        $dirtest = explode("/", substr($_SERVER["REQUEST_URI"], 0, $querystart));
        $lasti = count($dirtest)-1;
        if (str_starts_with($dirtest[$lasti], "index.php")) array_pop($dirtest);
        $dir = trim(implode("/", $dirtest), "/");
        $config["folder"] = $dir;
        
        if (function_exists("apache_get_modules") and in_array("mod_rewrite", apache_get_modules())) {
            $config["modRewrite"] = true;
        }

        message("Database successfully written.");
        $config["installed"] = true;
        $config["MySQLServer"] = $_POST["SQLHost"];
        $config["MySQLUser"] = $_POST["SQLUser"];
        $config["MySQLPass"] = $_POST["SQLPass"];
        $config["MySQLDatabase"] = $_POST["SQLDB"];
        flushConfig();
        
        // Log the admin in.
        $_SESSION["signed_in"] = true;
        $_SESSION["userid"] = $config["mainAdmin"];
        $_SESSION["username"] = $_POST["username"];
        $_SESSION["role"] = "Administrator";
    }
}

require "views/header.php";

if (!$config["installed"]) {
    echo("<form method='post' class='form'>
        <input type='hidden' name='token' value='{$_SESSION["token"]}'>
        <br><h2>Install</h2>
        <br><h3>SQL Details</h3>
        <label for='SQLHost'>SQL Host:</label>
        <input type='text' name='SQLHost' id='SQLHost' value='"
        . htmlspecialchars($_POST["SQLHost"] ?? "") . "' placeholder='localhost'>
        <label for='SQLUser'>SQL User:</label>
        <input type='text' name='SQLUser' id='SQLUser' value='"
        . htmlspecialchars($_POST["SQLUser"] ?? "") . "'>
        <label for='SQLPass'>SQL Password:</label>
        <input type='password' name='SQLPass' id='SQLPass' value='"
        . htmlspecialchars($_POST["SQLPass"] ?? "") . "'>
        <label for='SQLDB'>SQL Database:</label>
        <input type='text' name='SQLDB' id='SQLDB' value='"
        . htmlspecialchars($_POST["SQLDB"] ?? "") . "'>
        <br><h3>Admin Account</h3>
        <label for='username'>Username:</label>
        <input type='username' name='username' id='username' value='"
        . htmlspecialchars($_POST["username"] ?? "") . "'>
        <label for='email'>Email:</label>
        <input type='email' name='email' id='email' value='"
        . htmlspecialchars($_POST["email"] ?? "") . "'>
        <label for='password'>Password:</label>
        <input type='password' name='password' id='password' value='"
        . htmlspecialchars($_POST["password"] ?? "") . "'>
        <label for='confirmpassword'>Confirm password:</label>
        <input type='password' name='confirmpassword' id='confirmpassword' value='"
        . htmlspecialchars($_POST["confirmpassword"] ?? "") . "'>
        <br><h3>Advanced</h3>
        <label for='overwrite'>Overwrite old database:</label>
        <input type='checkbox' name='overwrite' id='overwrite'"
        . (($_POST["overwrite"] ?? "") == "on" ? " checked" : "") . ">
        <br>
        <input type='submit' class='item' value='Install'>
    </form>");
}

require "views/footer.php";

?>
