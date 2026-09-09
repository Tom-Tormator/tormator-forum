<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// panel.php
// Panel view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

?><div class='paneltabs'>
 <a class='paneltab<?php echo(active("main")); ?>' href='<?php echo(makeURL("panel")); ?>'>Panel</a>
 <a class='paneltab<?php echo(active("newcategory")); ?>' href='<?php echo(makeURL("panel/newcategory")); ?>'>New category</a>
 <a class='paneltab<?php echo(active("extensions")); ?>' href='<?php echo(makeURL("panel/extensions")); ?>'>Extensions</a>
</div>

<div class='panelcontent'>
<?php require "views/panel/{$page}.php"; ?>
</div>

<?php require "views/footer.php"; ?>
