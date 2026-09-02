<?php
// settings.php
// Allows the user to change their settings.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($_SESSION["signed_in"]):
?><h2>Settings</h2>

<h3>Change color</h3>
<form method='post' class='form'>
 <label for='color'>Color:</label>
 <input type='color' name='color' id='color' value='<?php echo(htmlspecialchars($_POST["color"] ?? "#" . $user_info["color"])); ?>'>
 <br>
 <input type='submit' value='Change color'>
</form>
<h4>Change username</h4>
<form method='post' class='form'>
 <label for='newusername'>New username:</label>
 <input type='text' name='newusername' id='newusername' value='<?php echo(htmlspecialchars($_POST["newusername"] ?? "")); ?>'>
 <label for='confirmpass'>Current password:</label>
 <input type='password' name='confirmpass' id='confirmpass'>
 <br>
 <input type='submit' value='Change username'>
</form>

<?php endif; require "views/footer.php"; ?>
