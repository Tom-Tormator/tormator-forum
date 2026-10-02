<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// main.php
// Main panel page.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$cats = $db->query("SELECT COUNT(*) FROM `categories`")->fetch_assoc();
$threads = $db->query("SELECT COUNT(*) FROM `threads`")->fetch_assoc();
$posts = $db->query("SELECT COUNT(*) FROM `posts`")->fetch_assoc();
$users = $db->query("SELECT COUNT(*) FROM `users`")->fetch_assoc();

?><h2>Admin panel</h2>
<h3>Board statistics</h3>
<ul>
 <li>Version: <?php echo($config["version"]); ?></li>
 <li>Categories: <?php echo($cats["COUNT(*)"]); ?></li>
 <li>Threads: <?php echo($threads["COUNT(*)"]); ?></li>
 <li>Posts: <?php echo($posts["COUNT(*)"]); ?></li>
 <li>Users: <?php echo($users["COUNT(*)"]); ?></li>
</ul>
