<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// extensions.php
// Functions for making extensions work.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

$hooks = array();
$extensions = array();
if (file_exists("config/extensions.php")) {
    require "config/extensions.php";
}
else {
    $loaded_extensions = array();
}

function addHook($name) {
    global $hooks;
    if (!array_key_exists($name, $hooks)) {
        $hooks[$name] = array();
    }
    // Run any functions that use this hook.
    if (count($hooks[$name])) {
        foreach ($hooks[$name] as $hooked) {
            call_user_func($hooked);
        }
    }
}

function hook($functionname, $hookname) {
    global $hooks;
    if (!array_key_exists($hookname, $hooks)) {
        $hooks[$hookname] = array();
    }
    $hooks[$hookname][] = $functionname;
}

function flushExtensionConfig() {
    global $loaded_extensions;
    $success = file_put_contents("config/extensions.php", "<?php\n\nif (!defined(\"INDEXED\")) exit;\n\n\$loaded_extensions = " . var_export($loaded_extensions, true) . "\n\n?>");
    // Make PHP throw away the cached version of the config.
    if ($success !== false) opcache_invalidate("config/extensions.php");
    return $success;
}

// Find every extension.
$edirs = scandir("extensions");
$forbidden = array(".", "..");
foreach ($edirs as $edir) {
    if (in_array($edir, $forbidden)) continue;
    // Get the manifest.
    $manifestfile = "extensions/{$edir}/manifest.json";
    if (is_file($manifestfile) and is_file("extensions/{$edir}/extension.php")) {
        $manifestcontent = file_get_contents($manifestfile);
        if ($manifestcontent === false) continue;
        $manifest = json_decode($manifestcontent, true);
        if (($manifest === null) or (!is_array($manifest))) continue;
        if (array_key_exists("id", $manifest)) {
            $extensions[$manifest["id"]] = $edir;
        }
    }
}

// Load all extensions that have been enabled.
foreach ($loaded_extensions as $le) {
    if (array_key_exists($le, $extensions)) {
        include_once "extensions/{$extensions[$le]}/extension.php";
    }
}

?>
