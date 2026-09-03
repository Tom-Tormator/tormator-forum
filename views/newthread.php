<?php
// newthread.php
// Newthread view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($_SESSION["signed_in"] and ($cats->num_rows > 0) and !$success):
?><h2>Create a thread</h2>
		
<form method='post' class='form'>
 <label for='title'>Title:</label>
 <input type='text' name='title' id='title' value='<?php echo(htmlspecialchars($_POST["title"] ?? "")); ?>'>
 <label for='category'>Category:</label>
 <select name='category' id='category'>
 <?php
    while ($cat = $cats->fetch_assoc()) {
        echo "<option ";
        if (($_POST["category"] ?? "") == $cat["categoryid"]) echo "selected ";
        echo "value='" . $cat["categoryid"] . "'>" . htmlspecialchars($cat["categoryname"], ENT_NOQUOTES) . "</option>";
    }
 ?>
 </select>
 <label for='postcontent'>Content:</label>
 <textarea name='postcontent' id='postcontent' class='postTextbox'><?php echo(htmlspecialchars($_POST["postcontent"] ?? "")); ?></textarea>
 <br>
 <input type="submit" class='item' value="Create thread">
</form>

<?php endif; require "views/footer.php"; ?>
