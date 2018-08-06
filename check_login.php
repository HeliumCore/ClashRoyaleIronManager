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
        $_SESSION['accountId'] = getHashedPassword($db, $playerTag)['id'];
        $url = "https://ironmanager.fr" . $_SERVER['REQUEST_URI'];
        header('Location: '.$url);
    }
}