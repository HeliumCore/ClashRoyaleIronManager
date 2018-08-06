<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 14:51
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
if (is_array($passInfos)) {
    echo 'exists';
} else {
    $playerId = intval(getPlayerByTag($db, $playerTag)['id']);
    $passwordHashed = generate_hash($password);
    $date = new DateTime();
    $time = $date->getTimestamp();
    $accountId = createAccount($db, $playerId, $passwordHashed, $time);
// Début de la session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['accountId'] = $accountId;
    echo 'true';
}