<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 06/08/18
 * Time: 15:11
 */

include("tools/database.php");

if (session_status() == PHP_SESSION_NONE)
    session_start();

if ((!isset($_SESSION['accountId']) || empty($_SESSION['accountId']))
    && isset($_SESSION['playerTAg']) && isset($_SESSION['remember'])) {

    $playerTag = $_COOKIE['playerTag'];
    $remember = $_COOKIE['remember'];

    if ($playerTag == $remember) {
        $accountId = getAccountInfos($db, $playerTag)['id'];
        $date = new DateTime();
        $time = $date->getTimestamp();
        setLastVisit($db, $accountId, $time);
        $_SESSION['accountId'] = $accountId;
        header("Location: https://ironmanager.fr" . $_SERVER['REQUEST_URI']);
    }
}