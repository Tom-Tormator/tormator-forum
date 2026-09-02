<?php
// footer.php
// Placed at the bottom of every page on the forum.

// Only load the page if it's being loaded through the index.php file.
if (!defined("INDEXED")) exit;

?></div>
<div id="footer"><?php echo(htmlspecialchars($config["footer"], ENT_NOQUOTES)); ?></div>
</div>
</body>
</html>
