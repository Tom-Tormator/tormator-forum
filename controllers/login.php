<?php
// login.php
// Logs the user in.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

require "views/header.php";

echo("<h3>Sign in</h3>");

if ($_SESSION["signed_in"]) {
    message("You are already signed in, you can <a href='/logout/'>log out</a> if you want.");
    require "views/footer.php";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    
    $_POST["user_name"] = $_POST["user_name"] ?? "";
    $_POST["user_pass"] = $_POST["user_pass"] ?? "";
		
    if (strlen($_POST["user_name"]) < 1) {
        $errors[] = "Your username cannot be blank.";
    }
		
    if (strlen($_POST["user_pass"]) < 1) {
        $errors[] = "Your password cannot be blank.";
    }
		
    if (count($errors) != 0) {
        foreach ($errors as $error) {
            message($error);
        }
    }
    else {
        $password_query = $db->query("SELECT `userid`, `username`, `role`, `password` FROM `users` WHERE `username`='" . $db->real_escape_string($_POST["user_name"]) . "'");
			
        if (!$password_query->num_rows) {
            message("The specified user doesn't exist.");
        }
			
        $user_info = $password_query->fetch_assoc();
				
        // Now check if the password is correct.
        if (!password_verify($_POST["user_pass"], $user_info["password"])) {
            message("Wrong password.");
        }
        else {
            $_SESSION["signed_in"] = true;
            $_SESSION["userid"] = $user_info["userid"];
            $_SESSION["username"] = $user_info["username"];
            $_SESSION["role"] = $user_info["role"];
  
            message("Welcome, " . htmlspecialchars($_SESSION["username"], ENT_NOQUOTES) . ". <a href='/'>Proceed to the forum overview</a>.");
        }
    }
}
else {
    echo("<form method='post'>
        Username: <input type='text' name='user_name'>
        </br></br>
        Password: <input type='password' name='user_pass'>
        </br></br>
        <input type='submit' value='Log in'>
    </form>");
}

require "views/footer.php";

?>
