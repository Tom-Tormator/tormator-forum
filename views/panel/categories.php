<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// categories.php
// Category management view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

?>
<h2>Manage categories</h2>
<div class='categoryTiles'>
<?php
if ($categoriesQuery->num_rows > 0) {
    while ($category = $categoriesQuery->fetch_assoc()) {
        echo("<div class='categoryTile'>
         <div>
          <span class='categoryName'>" . htmlspecialchars($category["name"]) . "</span>
          <br>
          <span class='categoryDescription'>" . htmlspecialchars($category["description"]) . "</span>
         </div>
         <div class='categoryTileRight'>
          <form method='post' onsubmit='return confirm(\"Are you sure you want to delete this category and all of its threads?\");'>
           <input type='hidden' name='token' value='{$_SESSION["token"]}'>
           <button type='submit' class='item' value='{$category["id"]}' name='delete'>Delete</button>
          </form>
         </div>
        </div>");
    }
}
else {
    echo(htmlMessage("No categories to display.", "info"));
}
?>
</div>
<h3>Create a category</h3>
<form method='post' class='form'>
 <input type='hidden' name='token' value='<?php echo($_SESSION["token"]); ?>'>
 <label>Category name:</label>
 <input type='text' name='cat_name' value='<?php echo(htmlspecialchars($_POST["cat_name"] ?? "")); ?>'>
 <label>Category description:</label>
 <textarea name='cat_description'><?php echo(htmlspecialchars($_POST["cat_description"] ?? "")); ?></textarea>
 <br>
 <input type='submit' class='item' value='Add category' name='newcategory'>
</form>
