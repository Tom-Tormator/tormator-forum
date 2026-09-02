<?php
//signup.php
// Allows users to create accounts.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

echo("<h3>Sign up</h3>");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    
    $_POST["user_name"] = $_POST["user_name"] ?? "";
    $_POST["user_email"] = $_POST["user_email"] ?? "";
    $_POST["user_pass"] = $_POST["user_pass"] ?? "";
    $_POST["user_pass_check"] = $_POST["user_pass_check"] ?? "";

    if (!ctype_alnum($_POST["user_name"])) {
        $errors[] = "Your username can only contain alphanumeric characters.";
    }
    if (strlen($_POST["user_name"]) < 1) {
        $errors[] = "Your username cannot be blank.";
    }
    elseif (strlen($_POST["user_name"]) > 24) {
        $errors[] = "Your username cannot be longer than 24 characters.";
    }
    // TODO make sure username isnt taken
    
    // TODO email check

    if (strlen($_POST["user_pass"]) < 1) {
        $errors[] = "Your password cannot be blank.";
    }
    elseif ($_POST["user_pass"] != $_POST["user_pass_check"]) {
        $errors[] = "Your passwords do not match.";
    }

    if (count($errors) != 0) {
        foreach($errors as $error) {
            message($error);
        }
    }
    else {
        // Construct the query.
        $username = $_POST['user_name'];
        $email = $_POST['user_email'];
        $password = password_hash($_POST['user_pass'], PASSWORD_DEFAULT);
        $role = "Member";
        $color = '1';
        $ip = hash("sha256", $_SERVER["REMOTE_ADDR"]);
        $verified = '1';
        $now = time();
		
        $result = $db->query("INSERT INTO `users` (`username`, `email`, `password`, `role`, `jointime`, `lastactive`, `color`, `ip`, `verified`) VALUES('" . $db->real_escape_string($username) . "', '" . $db->real_escape_string($email) . "', '" . $db->real_escape_string($password) . "', '$role', '$now', '$now', '$color', '$ip', '$verified')");
						
        if (!$result) {
            // Something went wrong, display an error.
            message("Something went wrong while registering. Please try again later.");
        }
        else {
            message('Successfully registered. You can now <a href="login.php">log in</a> and start posting! :-)');
        }
    }
}
else {
    echo("<form method='post'>
        Username: <input type='text' name='user_name'>
        <br>
        E-mail: <input type='email' name='user_email'>
        <br>
        Password: <input type='password' name='user_pass'>
        <br>
        Confirm password: <input type='password' name='user_pass_check'>
        <br>
        <input type='submit' value='Register'>
    </form>");
}

require "views/footer.php";

?>
