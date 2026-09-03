<?php
// newcategory.php
// New category view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if (($_SESSION["role"] == "Administrator") and !$success and ($catcheck->num_rows < $config["maxCats"])):
?>

<h2>Create a category</h2>
<form method='post' class='form'>
 <label>Category name:</label>
 <input type='text' name='cat_name' value='<?php echo(htmlspecialchars($_POST["cat_name"] ?? "")); ?>'>
 <label>Category description:</label>
 <textarea name='cat_description'><?php echo(htmlspecialchars($_POST["cat_description"] ?? "")); ?></textarea>
 <br>
 <input type='submit' class='item' value='Add category'>
</form>

<?php endif; require "views/footer.php"; ?>
