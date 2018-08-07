<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 06/08/2018
 * Time: 22:15
 */

include(__DIR__ . "/../../tools/database.php");

if (isset($_POST['dates']) && !empty($_POST['dates'])) {
    $dates = $_POST['dates'];
} else {
    echo 'false';
}
if (session_status() == PHP_SESSION_NONE)
    session_start();

$accountId = $_SESSION['accountId'];
$pauses = getAllPauseByAccount($db, $accountId);
$diff = trueDiff($dates, $pauses);

foreach ($diff as $d) {
    if (!in_array($d, $pauses)) {
        insertPause($db, $accountId, array($d));
    } else if (!in_array($d, $dates)) {
        deletePause($db, $accountId, $d);
    }
}

function trueDiff($A, $B) {
    $intersect = array_intersect($A, $B);
    return array_merge(array_diff($A, $intersect), array_diff($B, $intersect));
}

echo 'true';