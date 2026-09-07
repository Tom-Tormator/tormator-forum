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

?><h2>Admin panel</h2>
<h3>Board statistics</h3>
<ul>
 <li>Version: <?php echo($config["version"]); ?></li>
</ul>
