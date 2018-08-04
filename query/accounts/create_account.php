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

$playerId = intval(getPlayerByTag($db, $playerTag)['id']);
$passInfos = getHashedPassword($db, $playerId);
if (is_array($passInfos)) {
    echo 'exists';
} else {
    $passwordHashed = generate_hash($password);
    $accountId = createAccount($db, $playerId, $passwordHashed);
// Début de la session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['accountId'] = $accountId;
    echo 'true';
}