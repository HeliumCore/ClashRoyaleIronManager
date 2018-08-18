<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 16:59
 */

include(__DIR__ . "/../../tools/accounts.php");

if (
    isset($_POST['old']) && !empty($_POST['old'])
    && isset($_POST['new']) && !empty($_POST['new'])
    && isset($_POST['tag']) && !empty($_POST['tag'])
) {
    $oldPassword = $_POST['old'];
    $newPassword = $_POST['new'];
    $playerTag = $_POST['tag'];
} else {
    echo 'false';
    return;
}

$passInfos = getAccountInfos($db, $playerTag);
if (validate_pw($oldPassword, $passInfos['password'])) {
    $playerId = intval(getPlayerByTag($db, $playerTag)['id']);
    updatePassword($db, $playerId, generate_hash($newPassword));
} else {
    echo 'false';
    return;
}
echo 'true';