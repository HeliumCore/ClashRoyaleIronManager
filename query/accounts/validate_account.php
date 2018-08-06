<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 03/08/18
 * Time: 10:55
 */

include(__DIR__ . "/../../tools/accounts.php");

if (isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['tag']) && !empty($_POST['tag'])) {
    $password = $_POST['password'];
    $playerTag = $_POST['tag'];
} else {
    echo 'false';
    return;
}

$passInfos = getAccountInfos($db, $playerTag);
$passwordHashed = $passInfos['password'];

if (validate_pw($password, $passwordHashed)) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $date = new DateTime();
    $time = $date->getTimestamp();
    setLastVisit($db, $passInfos['id'], $time);
    $_SESSION['accountId'] = $passInfos['id'];
    echo 'true';
} else {
    setcookie('remember', '', 1);
    session_destroy();
    echo 'false';
}
