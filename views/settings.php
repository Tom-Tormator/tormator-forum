<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// settings.php
// Allows the user to change their settings.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if ($_SESSION["signed_in"]):
?><h2>Settings</h2>

<h3>Change color</h3>
<form method='post' class='form'>
 <input type='hidden' name='token' value='<?php echo($_SESSION["token"]); ?>'>
 <label for='color'>Color:</label>
 <input type='color' name='color' id='color' value='<?php echo(htmlspecialchars($_POST["color"] ?? "#" . $user_info["color"])); ?>'>
 <br>
 <input type='submit' class='item' value='Change color'>
</form>
<h4>Change username</h4>
<form method='post' class='form'>
 <input type='hidden' name='token' value='<?php echo($_SESSION["token"]); ?>'>
 <label for='newusername'>New username:</label>
 <input type='text' name='newusername' id='newusername' value='<?php echo(htmlspecialchars($_POST["newusername"] ?? "")); ?>'>
 <label for='confirmpass'>Current password:</label>
 <input type='password' name='confirmpass' id='confirmpass'>
 <br>
 <input type='submit' class='item' value='Change username'>
</form>

<?php endif; require "views/footer.php"; ?>
