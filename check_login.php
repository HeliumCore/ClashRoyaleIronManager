<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 17/08/2018
 * Time: 17:49
 */

if (session_status() == PHP_SESSION_NONE)
    session_start();

if (empty($_SESSION['accountId']) && !empty($_COOKIE['remember'])) {
    $accountId = getAccountInfos($db, $_COOKIE['remember'])['id'];
    $date = new DateTime();
    $time = $date->getTimestamp();
    setLastVisit($db, $accountId, $time);
    $_SESSION['accountId'] = $accountId;
}
