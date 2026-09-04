<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// user.php
// User view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";
			
if (isMod() and ($user["userid"] != $config["mainAdmin"]) and ($user["userid"] != $_SESSION["userid"]) and canChangeRole($_SESSION["role"], $user["role"], "Suspended")) {
    echo "<div class='usertop' style='background: #" . htmlspecialchars($user["color"]) . ";'>
    <b>" . htmlspecialchars($user["username"]) . "</b> <small>
    <form method='post'>
    <input type='hidden' name='token' value='{$_SESSION["token"]}'>
    <select name='role'>";
    if ($_SESSION["userid"] == $config["mainAdmin"]) {
        $roles = array("Administrator", "Moderator", "Member", "Suspended");
    }
    elseif ($_SESSION["role"] == "Administrator") {
        $roles = array("Moderator", "Member", "Suspended");
    }
    elseif ($_SESSION["role"] == "Moderator") {
        $roles = array("Member", "Suspended");
    }
    foreach ($roles as $role) {
        echo("<option value='{$role}'" . (($role == $user["role"]) ? " selected" : "") . ">{$role}</option>");
    }
				
    echo "</select><input type='submit' value='Change role'></form></small></div>";
}
else {
    echo "<div class='usertop' style='background: #" . $user["color"] . ";'><b>" . htmlspecialchars($user["username"], ENT_NOQUOTES) . "</b> <small>" . $user["role"] . "</small></div>";
}
				
echo("<div class='userbottom'>
    <span>Joined: <abbr title='" . date("m-d-Y h:i:s A", $user["jointime"]) . "'>" . relativeTime($user["jointime"]) . "</abbr></span>
    <span>Last active: <abbr title='" . date("m-d-Y h:i:s A", $user["lastactive"]) . "'>" . relativeTime($user["lastactive"]) . "</abbr></span>
    <span>Posts: {$posts}</span>
    <span>Threads: {$threads}</span>
    <span>Verified: {$verified}</span>
    </div>");

require "views/footer.php";

?>
