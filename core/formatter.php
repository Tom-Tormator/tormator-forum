<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// formatter.php
// Functions responsible for formatting posts.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

function format($string) {
    $string = htmlspecialchars($string);
    
    $string = format_bold($string);
    $string = format_italic($string);
    $string = format_underline($string);
    $string = format_strikethrough($string);
    $string = format_size($string);
    $string = format_color($string);
    $string = format_center($string);
    $string = format_left($string);
    $string = format_right($string);
    $string = format_quote($string);
    $string = format_spoiler($string);
    $string = format_url($string);
    $string = format_image($string);
    $string = format_list($string);
    $string = format_code($string);
    $string = format_pre($string);
    $string = format_table($string);
    // Non-standard.
    $string = format_cleft($string);
    $string = format_heading($string);
    $string = format_video($string);
    $string = format_audio($string);
    // Make newlines work.
    $string = format_newlines($string);
    
    return $string;
}

function format_bold($string) {
    return preg_replace("/\[b\](.+?)\[\/b\]/s", "<strong>$1</strong>", $string);
}

function format_italic($string) {
    return preg_replace("/\[i\](.+?)\[\/i\]/s", "<em>$1</em>", $string);
}

function format_underline($string) {
    return preg_replace("/\[u\](.+?)\[\/u\]/s", "<u>$1</u>", $string);
}

function format_strikethrough($string) {
    return preg_replace("/\[s\](.+?)\[\/s\]/s", "<s>$1</s>", $string);
}

function format_size($string) {
    return preg_replace("/\[size=([0-9]|[0-9][0-9])\](.+?)\[\/size\]/s", "<span style='font-size: $1px;'>$2</span>", $string);
}

function format_color($string) {
    return preg_replace("/\[color=([0-9a-zA-Z#]+?)\](.+?)\[\/color\]/s", "<span style='color: $1;'>$2</span>", $string);
}

function format_center($string) {
    return preg_replace("/\[center\](.+?)\[\/center\]/s", "<div class='centered'>$1</div>", $string);
}

function format_left($string) {
    return preg_replace("/\[left\](.+?)\[\/left\]/s", "<div class='lefted'>$1</div>", $string);
}

function format_right($string) {
    return preg_replace("/\[right\](.+?)\[\/right\]/s", "<div class='righted'>$1</div>", $string);
}

function format_quote($string) {
    $string = preg_replace("/\[quote=(.+?)\](.+?)\[\/quote\]/s", "<blockquote><strong>$2 wrote:</strong> $1</blockquote>", $string);
    return preg_replace("/\[quote\](.+?)\[\/quote\]/s", "<blockquote>$1</blockquote>", $string);
}

function format_spoiler($string) {
    $string = preg_replace("/\[spoiler=(.+?)\](.+?)\[\/spoiler\]/s", "<details><summary>$2</summary>$1</details>", $string);
    return preg_replace("/\[spoiler\](.+?)\[\/spoiler\]/s", "<details>$1</details>", $string);
}

function format_url($string) {
    $string = preg_replace("/\[url=(http:\/\/|https:\/\/|mailto:|tel:)(.+?)\](.+?)\[\/url\]/s", "<a href='$1$2' target='_blank' rel='nofollow'>$3</a>", $string);
    return preg_replace("/\[url\](http:\/\/|https:\/\/|mailto:|tel:)(.+?)\[\/url\]/s", "<a href='$1$2' target='_blank' rel='nofollow'>$1$2</a>", $string);
}

function format_image($string) {
    $string = preg_replace("/\[img=([0-9]|[0-9][0-9]|[0-9][0-9][0-9])x([0-9]|[0-9][0-9]|[0-9][0-9][0-9])\](http:\/\/|https:\/\/)(.+?)\[\/img\]/s", "<img width='$1' height='$2' src='$3$4'>", $string);
    return preg_replace("/\[img\](http:\/\/|https:\/\/)(.+?)\[\/img\]/s", "<img src='$1$2'>", $string);
}

function format_list($string) {
    $string = preg_replace("/\[ul\](.+?)\[\/ul\]/s", "<ul>$1</ul>", $string);
    $string = preg_replace("/\[ol\](.+?)\[\/ol\]/s", "<ol>$1</ol>", $string);
    $string = preg_replace("/\[li\](.+?)\[\/li\]/s", "<li>$1</li>", $string);
    return preg_replace("/\[\*\](.+?)\n/s", "<li>$1</li>", $string);
}

function format_code($string) {
    return preg_replace("/\[code\](.+?)\[\/code\]/s", "<code>$1</code>", $string);
}

function format_pre($string) {
    return preg_replace("/\[pre\](.+?)\[\/pre\]/s", "<pre>$1</pre>", $string);
}

function format_table($string) {
    $string = preg_replace("/\[table\](.+?)\[\/table\]/s", "<table>$1</table>", $string);
    $string = preg_replace("/\[tr\](.+?)\[\/tr\]/s", "<tr>$1</tr>", $string);
    $string = preg_replace("/\[th\](.+?)\[\/th\]/s", "<th>$1</th>", $string);
    return preg_replace("/\[td\](.+?)\[\/td\]/s", "<td>$1</td>", $string);
}

/*** Non-standard. ***/

function cleave($string) {
    $string = $string[1];
    $middle = ceil(strlen($string)/2);
    $left = substr($string, 0, $middle);
    $right = substr($string, $middle);
    return "<div class='cleft'><div class='lefted'>" . $left . "</div><div class='righted'>" . $right . "</div></div>";
}
function format_cleft($string) {
    return preg_replace_callback("/\[cleft\](.+?)\[\/cleft\]/s", "cleave", $string);
}

function format_heading($string) {
    // We pretend h3-h6 are really h1-h4.
    $string = preg_replace("/\[h1\](.+?)\[\/h1\]/s", "<h3>$1</h3>", $string);
    $string = preg_replace("/\[h2\](.+?)\[\/h2\]/s", "<h4>$1</h4>", $string);
    $string = preg_replace("/\[h3\](.+?)\[\/h3\]/s", "<h5>$1</h5>", $string);
    return preg_replace("/\[h4\](.+?)\[\/h4\]/s", "<h6>$1</h6>", $string);
}

function format_video($string) {
    $string = preg_replace("/\[video=([0-9]|[0-9][0-9]|[0-9][0-9][0-9])x([0-9]|[0-9][0-9]|[0-9][0-9][0-9])\](http:\/\/|https:\/\/)(.+?)\[\/video\]/s", "<video width='$1' height='$2' controls><source src='$3$4'></video>", $string);
    return preg_replace("/\[video\](http:\/\/|https:\/\/)(.+?)\[\/video\]/s", "<video controls><source src='$1$2'></video>", $string);
}

function format_audio($string) {
    return preg_replace("/\[audio\](http:\/\/|https:\/\/)(.+?)\[\/audio\]/s", "<audio controls><source src='$1$2'></audio>", $string);
}

function format_newlines($string) {
    return str_replace("\n", "<br>", $string);
}

?>
