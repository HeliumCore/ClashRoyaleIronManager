<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 17/08/2018
 * Time: 17:49
 */

if (session_status() == PHP_SESSION_NONE)
    session_start();

$accountId = $_SESSION['accountId'];
$playerTag = $_COOKIE['remember'];
if ((!isset($accountId) || empty($accountId)) && isset($playerTag) && !empty($playerTag)) {
    $accountId = getAccountInfos($db, $playerTag)['id'];
    $date = new DateTime();
    $time = $date->getTimestamp();
    setLastVisit($db, $accountId, $time);
    $_SESSION['accountId'] = $accountId;
}