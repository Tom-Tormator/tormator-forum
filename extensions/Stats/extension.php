<?php

namespace stats;

$startTime = 0;

function getStartTime() {
    global $startTime;
    $startTime = microtime(true);
}

function getEnd() {
    global $startTime;
    echo("<br>Page executed in " . ((microtime(true) - $startTime)*1000) . " milliseconds.");
}

hook("stats\getStartTime", "beforePageLoad");
hook("stats\getEnd", "afterRenderFooter");

?>
