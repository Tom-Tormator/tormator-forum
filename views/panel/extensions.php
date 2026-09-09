<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */

// newcategory.php
// New category view.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

?>

<h2>Manage extensions</h2>
<div class='extensionTiles'>
<?php
foreach ($extensions as $id=>$dir) {
    if (in_array($id, $loaded_extensions)) $buttontext = "Disable";
    else $buttontext = "Enable";
    $manifest = json_decode(file_get_contents("extensions/{$dir}/manifest.json"), true);
    if (!array_key_exists("author", $manifest)) $manifest["author"] = "Nobody";
    if (!array_key_exists("name", $manifest)) $manifest["name"] = $id;
    if (!array_key_exists("description", $manifest)) $manifest["description"] = "No description.";
    if (!array_key_exists("version", $manifest)) $manifest["version"] = "MISSING";
    echo("<div class='extensionTile'>
     <div>
      " . htmlspecialchars($manifest["name"]) . " - " . htmlspecialchars($manifest["description"]) . "
      <br>By: " . htmlspecialchars($manifest["author"]) . "
      <br>Made for version: " . htmlspecialchars($manifest["version"]) . "
     </div>
     <div class='extensionTileRight'>
      <form method='post'>
       <input type='hidden' name='token' value='{$_SESSION["token"]}'>
       <button class='item' name='{$buttontext}' value=" . htmlspecialchars($id) . ">{$buttontext}</button>
      </form>
     </div>
    </div>");
}
?>
</div>
