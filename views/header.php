<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// header.php
// Placed at the top of every page on the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;
?><!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <title><?php title(); ?></title>
 <link rel="stylesheet" href="/themes/<?php echo($config["theme"]); ?>/style.css">
</head>
<body>
 <div id="wrapper">
 <div id="header">
  <h1><?php echo(htmlspecialchars($config["forumName"], ENT_NOQUOTES)); ?></h1>
 </div>
 <?php if ($config["installed"]): ?>
 <div id="menu">
  <a class="item" href="<?php echo(makeURL("")); ?>">Home</a>
  <a class="item" href="<?php echo(makeURL("userlist")); ?>">Userlist</a>
  <?php if ($_SESSION["signed_in"]): ?>
  <a class="item" href="<?php echo(makeURL("settings")); ?>">Settings</a>
  <a class="item" href="<?php echo(makeURL("newthread")); ?>">Create a thread</a>
 <?php endif;
		if ($_SESSION["role"] == "Administrator") {
			echo("<a class='item' href='" . makeURL("newcategory") . "'>Create a category</a>
			<a class='item' href='" . makeURL("panel") . "'>Admin Panel</a>");
		}

		if ($_SESSION["signed_in"]) {
			echo("<a class='item' href='" . makeURL("logout") . "'>Log out</a>");
		}

		echo("<div id='userbar'>");
 			if ($_SESSION["signed_in"]) {
 	 			echo("Hello, <a href='" . makeURL("user/{$_SESSION["userid"]}") . "'>" . htmlspecialchars($_SESSION["username"], ENT_NOQUOTES) . "</a>.");
 			}
 			else {
 				echo("<a href='" . makeURL("login") . "'>Log in</a> or <a href='" . makeURL("signup") . "'>Sign up</a>.");
 			}
		echo("</div>");
		?>
 </div>
 <?php endif; ?>
 <div id="content">
  <div id="messages">
   <?php foreach ($messages as $message) echo($message); ?>
  </div>
