<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// login.php
// Login view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";
?>

<?php if (!$_SESSION["signed_in"]): ?>
<h3>Log in</h3>
<form method='post' class='form'>
 <label for='user_name'>Username:</label>
 <input type='text' name='user_name' id='user_name' value='<?php echo(htmlspecialchars($_POST["user_name"] ?? "")); ?>'>
 <label for='user_pass'>Password:</label>
 <input type='password' name='user_pass' id='user_pass'>
 <br>
 <input type='submit' value='Log in'>
</form>
<?php endif; ?>

<?php require "views/footer.php"; ?>
