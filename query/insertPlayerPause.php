<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 06/08/2018
 * Time: 22:15
 */

include(__DIR__ . "/../tools/database.php");

if (isset($_POST['dates']) && !empty($_POST['dates'])) {
    $dates = $_POST['dates'];
} else {
    echo 'false';
}
if (session_status() == PHP_SESSION_NONE)
    session_start();

$accountId = $_SESSION['accountId'];
$pauses = getAllPauseByAccount($db, $accountId);

foreach ($dates as $date) {
    if (in_array($date, $pauses))
        continue;

    insertPause($db, $accountId, array($date));
}
echo 'true';