<?php
// userlist.php
// Displays a list of all the users on the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

// Start off by making a query for our list.
$users_query = $db->query("SELECT * FROM users ORDER BY userid ASC");

if ($users_query->num_rows < 1) {
    message("Sadly, there are currently no users on the forum.");
}
else {
    echo("<h2>Userlist</h2>");
    while ($user = $users_query->fetch_assoc()) {
        echo('<div class="userlist" postcolor="' . $user["color"] . '"><b><a href="/user/' . $user["userid"] . '/">' . htmlspecialchars($user["username"], ENT_NOQUOTES) . '</a></b>&nbsp; ' . $user["role"] . '&nbsp; <small>' . $user["lastaction"] . ' (<a title="' . date('m-d-Y h:i:s A', $user["lastactive"]) . '">' . relativeTime($user["lastactive"]) . '</a>)</small></div></br>');
    }
}

require "views/footer.php";

// If the viewing user is logged in, update their last action.
if ($_SESSION["signed_in"])
{
    update_last_action("Viewing: Userlist");
}

?>
