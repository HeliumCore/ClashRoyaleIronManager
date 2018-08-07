<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 07/08/18
 * Time: 11:41
 */

include(__DIR__ . "/../../tools/database.php");

if (session_status() == PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['accountId']) || empty($_SESSION['accountId'])) {
    echo 'false';
    return;
}

$accountId = $_SESSION['accountId'];
$pauses = getAllPauseByAccount($db, $accountId);
echo json_encode($pauses);
