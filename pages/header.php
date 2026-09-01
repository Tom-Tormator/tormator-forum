<?php
// header.php
// Placed at the top of every page on the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $config["forumName"]; ?></title>
	<link rel="stylesheet" href="/../themes/Blue/style.css" type="text/css">
</head>
<body>
<h1><?php echo $config["forumName"]; ?></h1>
	<div id="wrapper">
	<div id="menu">
		<?php
			echo '<a class="item" href="/">Home</a> ';
			echo '<a class="item" href="/userlist/">Userlist</a> ';
		
			echo '<a class="item" href="/settings/">Settings</a> ';
			echo '<a class="item" href="/newthread/">Create a thread</a> ';
		
		if(isset($_SESSION['signed_in']) && $_SESSION['signed_in'] == true && $_SESSION["role"] == "Administrator")
		{
			echo '<a class="item" href="/newcategory/">Create a category</a> ';
			echo '<a class="item" href="/panel/">Admin Panel</a> ';
		}
		

		if(isset($_SESSION['signed_in']) && $_SESSION['signed_in'] == true)
		{
			echo '<a class="item" href="/logout/">Log out</a> ';
		}
		
		echo '<div id="userbar">';
 			if(isset($_SESSION['signed_in']) && $_SESSION['signed_in'] == true)
 			{
 	 			echo "Hello <a href='/user/" . $_SESSION["userid"] . "/'>" . $_SESSION["username"] . "</a>.";
 			}
 			else
 			{
 				echo '<a href="/login/">Log in</a> or <a href="/signup/">Sign up</a>.';
 			}
		echo "</div>";
		?>
	</div>
		<div id="content">
