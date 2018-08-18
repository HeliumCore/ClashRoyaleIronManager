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

$playerInfos = getPlayerByTag($db, $playerTag);
if ($playerInfos == null) {
    echo 'wrongTag';
    return;
}

$passInfos = getAccountInfos($db, $playerTag);
$date = new DateTime();
$time = $date->getTimestamp();

if (is_array($passInfos)) {
    $passwordHashed = generate_hash($password);
    $accountId = createAccount($db, intval($playerInfos['id']), $passwordHashed, $time);
    if ($accountId < 1) {
        echo 'registerFailed';
        return;
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['accountId'] = $accountId;
    setLastVisit($db, $accountId, $time);
    setcookie('remember', $playerTag, strtotime( '+30 days' ), "/");
    setcookie('playerTag', $playerTag, strtotime( '+30 days' ), "/");
    echo 'registerOk';
} else {
    if (validate_pw($password, $passInfos['password'])) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['accountId'] = $passInfos['id'];
        setLastVisit($db, $passInfos['id'], $time);
        setcookie('remember', $playerTag, strtotime( '+30 days' ), "/");
        setcookie('playerTag', $playerTag, strtotime( '+30 days' ), "/");
        echo 'loginOk';
    } else {
        setcookie('remember', null, -1, "/");
        setcookie('playerTag', null, -1, "/");
        session_destroy();
        echo 'wrongPass';
    }
}
