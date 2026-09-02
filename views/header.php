<?php
// header.php
// Placed at the top of every page on the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit; ?><!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <title><?php echo $config["forumName"]; ?></title>
 <link rel="stylesheet" href="/themes/<?php echo($config["theme"]); ?>/style.css">
</head>
<body>
	<div id="wrapper">
	 <div id="header">
	  <h1><?php echo(htmlspecialchars($config["forumName"], ENT_NOQUOTES)); ?></h1>
	  </div>
	<div id="menu">
		<?php
			echo '<a class="item" href="/">Home</a> ';
			echo '<a class="item" href="/userlist/">Userlist</a> ';
		
			echo '<a class="item" href="/settings/">Settings</a> ';
			echo '<a class="item" href="/newthread/">Create a thread</a> ';
		
		if ($_SESSION['signed_in'] and ($_SESSION["role"] == "Administrator"))
		{
			echo '<a class="item" href="/newcategory/">Create a category</a> ';
			echo '<a class="item" href="/panel/">Admin Panel</a> ';
		}
		

		if ($_SESSION['signed_in'])
		{
			echo '<a class="item" href="/logout/">Log out</a> ';
		}
		
		echo '<div id="userbar">';
 			if ($_SESSION['signed_in'])
 			{
 	 			echo "Hello <a href='/user/" . $_SESSION["userid"] . "/'>" . htmlspecialchars($_SESSION["username"], ENT_NOQUOTES) . "</a>.";
 			}
 			else
 			{
 				echo '<a href="/login/">Log in</a> or <a href="/signup/">Sign up</a>.';
 			}
		echo "</div>";
		?>
	</div>
		<div id="content">
