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

$playerId = intval(getPlayerByTag($db, $playerTag)['id']);
$passInfos = getHashedPassword($db, $playerId);
$passwordHashed = $passInfos['password'];

if (validate_pw($password, $passwordHashed)) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['accountId'] = $passInfos['id'];
    echo 'true';
} else {
    session_destroy();
    echo 'false';
}
