<?php
// config.php
// Contains all the config options essential for the forum to function properly.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$default_config = array(
    "installed" => false,
    // URL for your MySQL server. Set to localhost if it's on the same machine.
    "MySQLServer" => "",
    // Username for native MySQL user.
    "MySQLUser" => "",
    // Password for native MySQL user.
    "MySQLPass" => "",
    // Name of the MySQL database you wish to use.
    "MySQLDatabase" => "",
    // The folder the forum is installed in if applicable.
    "folder" => "",
    // Userid of the main admin, their role cannot be changed by other admins.
    "mainAdmin" => 1,
    // The prefix of cookies set by the forum.
    "cookiePrefix" => "Tormator_Forum_",
    "theme" => "Blue",
    // The forum's name.
    "forumName" => "Tormator Forum",
    // The text that appears on the footer.
    "footer" => "Powered by Tormator Forum",
    // Number of posts to display on a page in a thread.
    "postsPerPage" => 10,
    // Number of threads to display on a page in a category.
    "threadsPerPage" => 20,
    // Maximum number of characters allowable in a single post.
    "maxCharsPerPost" => 50000,
    // Number of seconds between posts during which a user can't make another post.
    "postDelay" => 5,
    // Maximum number of categories that can be created.
    "maxCats" => 20,
    // Maximum number of characters allowable in a thread or category title.
    "maxCharsPerTitle" => 35,
    "minPasswordLength" => 12
);

?>
