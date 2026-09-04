<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// newcategory.php
// New category view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if (!$success):
?>

<h2>Create a category</h2>
<form method='post' class='form'>
 <input type='hidden' name='token' value='<?php echo($_SESSION["token"]); ?>'>
 <label>Category name:</label>
 <input type='text' name='cat_name' value='<?php echo(htmlspecialchars($_POST["cat_name"] ?? "")); ?>'>
 <label>Category description:</label>
 <textarea name='cat_description'><?php echo(htmlspecialchars($_POST["cat_description"] ?? "")); ?></textarea>
 <br>
 <input type='submit' class='item' value='Add category'>
</form>

<?php endif; require "views/footer.php"; ?>
