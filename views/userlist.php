<?php
// userlist.php
// Userlist view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($users_query->num_rows > 0):
?>

<h2>Userlist</h2>

<?php
while ($user = $users_query->fetch_assoc()) {
    echo('<div class="userlist" style="background:#' . $user["color"] . ';"><b><a href="/user/' . $user["userid"] . '/">' . htmlspecialchars($user["username"], ENT_NOQUOTES) . '</a></b>&nbsp; ' . $user["role"] . '&nbsp; <small>' . $user["lastaction"] . ' (<a title="' . date('m-d-Y h:i:s A', $user["lastactive"]) . '">' . relativeTime($user["lastactive"]) . '</a>)</small></div></br>');
}

endif; require "views/footer.php"; ?>
