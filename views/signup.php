<?php
// signup.php
// Signup view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

if (!$_SESSION["signed_in"] and !$success):
?><h3>Sign up</h3>

<form method='post' class='form'>
 <label for='user_name'>Username:</label>
 <input type='text' name='user_name' id='user_name' value='<?php echo(htmlspecialchars($_POST["user_name"] ?? "")); ?>'>
 <label for='user_email'>Email:</label>
 <input type='email' name='user_email' id='user_email' value='<?php echo(htmlspecialchars($_POST["user_email"] ?? "")); ?>'>
 <label for='user_pass'>Password:</label>
 <input type='password' name='user_pass' id='user_pass' value='<?php echo(htmlspecialchars($_POST["user_pass"] ?? "")); ?>'>
 <label for='user_pass_check'>Confirm password:</label>
 <input type='password' name='user_pass_check' id='user_pass_check' value='<?php echo(htmlspecialchars($_POST["user_pass_check"] ?? "")); ?>'>
 <br>
 <input type='submit' value='Register'>
</form>

<?php endif; require "views/footer.php"; ?>
