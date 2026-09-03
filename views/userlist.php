<?php
// userlist.php
// Userlist view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($users_query->num_rows > 0):
?>

<h2>Userlist</h2>

<div class='userlist'>
<?php
while ($user = $users_query->fetch_assoc()) {
    echo('<div class="userlistitem" style="background:#' . $user["color"] . ';"><a href="/user/' . $user["userid"] . '/">' . htmlspecialchars($user["username"], ENT_NOQUOTES) . '</a>&nbsp; ' . $user["role"] . '&nbsp; <small>' . $user["lastaction"] . ' (<abbr title="' . date('m-d-Y h:i:s A', $user["lastactive"]) . '">' . relativeTime($user["lastactive"]) . '</abbr>)</small></div>');
}
?>
</div>
<?php endif; require "views/footer.php"; ?>
