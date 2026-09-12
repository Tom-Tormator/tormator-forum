<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// signup.php
// Allows users to create accounts.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$title = "Sign up";
$success = false;

if ($_SESSION["signed_in"]) {
    message("You are already signed in, you can <a href='" . makeURL("logout") . "'>log out</a> if you want.", "error");
    require("views/error.php");
    exit();
}

function generateCaptchaChars() {
    global $config;
    $chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789&#*%@?";
    $captcha = "";
    for ($i = 0; $i < $config["captchaLength"]; $i++) {
        $captcha .= $chars[random_int(0, strlen($chars)-1)];
    }
    return $captcha;
}

function generateCaptcha() {
    $_SESSION["captcha"] = generateCaptchaChars();
    $length = strlen($_SESSION["captcha"]);
    
    $font = random_int(2, 5);
    $fontwidth = imagefontwidth($font);
    $fontheight = imagefontheight($font);
    
    $width = $fontwidth*$length*2;
    $height = $fontheight*2;
    
    $img = imagecreatetruecolor($width, $height);
    
    for ($w = 0; $w < $width; $w++) {
        for ($h = 0; $h < $height; $h++) {
            $rand = imagecolorallocate($img, rand(0, 100), rand(0, 100), rand(0, 100));
            imagesetpixel($img, $w, $h, $rand);
        }
    }
    
    for ($i = 0; $i < $length; $i++) {
        $rand = imagecolorallocate($img, rand(200, 255), rand(200, 255), rand(200, 255));
        imagestring($img, $font, (($i*$fontwidth)*2)+($fontwidth/2)+random_int(-$fontwidth/3, $fontwidth/3), $fontheight/2+random_int(-$fontheight/2, $fontheight/2), $_SESSION["captcha"][$i], $rand);
    }
    
    $img = imagescale($img, $width*3, $height*3);
    
    imagefilter($img, IMG_FILTER_SCATTER, 0, 2);
    imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
    
    ob_start();
    imagewebp($img);
    $result = ob_get_clean();
    
    return base64_encode($result);
}

if (validateToken()) {
    $errors = array();

    $uiv = validateUsername($_POST["user_name"] ?? "");
    if ($uiv) $errors[] = $uiv;
    $eiv = validateEmail($_POST["user_email"] ?? "");
    if ($eiv) $errors[] = $eiv;
    $piv = validatePassword($_POST["user_pass"] ?? "", $_POST["user_pass_check"] ?? "");
    if ($piv) $errors[] = $piv;
    
    if ($config["captcha"] and extension_loaded("gd")) {
        if (($_POST["captcha"] ?? "") != $_SESSION["captcha"]) {
            $errors[] = "Incorrect CAPTCHA response.";
        }
    }
    
    $ipHash = hash("sha256", $_SERVER["REMOTE_ADDR"]);
    $accounts = $db->query("SELECT `jointime` FROM `users` WHERE `joinip`='{$ipHash}' OR `ip`='{$ipHash}' ORDER BY `jointime` DESC");
    
    if ($accounts->num_rows >= $config["accountsPerIP"]) {
        $errors[] = "You've made too many accounts. Try <a href='" . makeURL("login") . "'>logging in</a> to an existing one instead.";
    }
    
    $jts = $accounts->fetch_assoc();
    if (($jts !== null) and ($jts !== false)) {
        if ((time()-(int)$jts["jointime"]) < $config["timeBetweenSignups"]) {
            $errors[] = "You created another account too recently. Wait a little while and try again.";
        }
    }

    if (count($errors) != 0) {
        foreach($errors as $error) {
            message($error, "error");
        }
    }
    else {
        // Construct the query.
        $password = $db->real_escape_string(password_hash($_POST["user_pass"], PASSWORD_DEFAULT));
        $role = "Member";
        $ip = $db->real_escape_string(hash("sha256", $_SERVER["REMOTE_ADDR"]));
        $verified = '1';
        $now = time();
		
        $result = $db->query("INSERT INTO `users` (`username`, `email`, `password`, `role`, `jointime`, `lastactive`, `joinip`, `ip`, `verified`) VALUES('" . $db->real_escape_string($_POST["user_name"]) . "', '" . $db->real_escape_string($_POST["user_email"]) . "', '{$password}', '{$role}', '{$now}', '{$now}', '{$ip}', '{$ip}', '{$verified}')");
						
        if (!$result) {
            // Something went wrong, display an error.
            message("Something went wrong while signing up. Please try again later.", "error");
        }
        else {
            // Log the new user in.
            session_regenerate_id(true);
            $_SESSION["signed_in"] = true;
            $_SESSION["userid"] = $db->insert_id;
            $_SESSION["username"] = $_POST["user_name"];
            $_SESSION["role"] = $role;
            
            // Finally send them to their settings page.
            redirect("settings");
        }
    }
}

require "views/signup.php";

?>
