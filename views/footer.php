<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// footer.php
// Placed at the bottom of every page on the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

?></div>
<div id="footer"><?php echo(htmlspecialchars($config["footer"], ENT_NOQUOTES)); ?></div>
</div>
</body>
</html>
