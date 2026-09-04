<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// userlist.php
// Userlist view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

?>

<h2>Userlist</h2>

<div class='userlist'>
<?php
while ($user = $users_query->fetch_assoc()) {
    echo("<div class='userlistitem' style='background:#" . $user["color"] . ";'>
    <a href='" . makeURL("user/{$user["userid"]}") . "'>" . htmlspecialchars($user["username"], ENT_NOQUOTES) . "</a>&nbsp; " . $user["role"] . "&nbsp; <small>" . $user["lastaction"] . " (<abbr title='" . date("m-d-Y h:i:s A", $user["lastactive"]) . "'>" . relativeTime($user["lastactive"]) . "</abbr>)</small>
    </div>");
}
?>
</div>
<?php require "views/footer.php"; ?>
