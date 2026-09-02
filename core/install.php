<?php
// install.php
// Installs the software.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require 'views/header.php';

// Make sure the MySQL details have been inputted.
if (!$config["MySQLServer"] or !$config["MySQLUser"] or !$config["MySQLPass"] or !$config["MySQLDatabase"]) {
	require "pages/header.php";
	message("Error: Please fill out the config with your MySQL details before trying to install the forum.");
	require "views/footer.php";
	exit();
}

$db = mysqli_connect($config["MySQLServer"], $config["MySQLUser"],  $config["MySQLPass"], $config["MySQLDatabase"]);

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
  	`posts` int unsigned NOT NULL,
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
  	`color` tinyint unsigned NOT NULL DEFAULT '1',
  	`ip` char(64) NOT NULL,
  	`verified` tinyint(1) NOT NULL DEFAULT '0',
  	PRIMARY KEY (`userid`),
  	UNIQUE KEY `user_name` (`username`),
  	UNIQUE KEY `user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

message("Database successfully written.");

$config["installed"] = true;

flushConfig();

require 'views/footer.php';

?>
