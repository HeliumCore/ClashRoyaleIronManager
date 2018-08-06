<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 16:59
 */

include(__DIR__ . "/../../tools/accounts.php");

if (
    isset($_POST['password']) && !empty($_POST['password'])
    && isset($_POST['old_password']) && !empty($_POST['old_password'])
    && isset($_POST['tag']) && !empty($_POST['tag'])
) {
    $password = $_POST['password'];
    $playerTag = $_POST['tag'];
} else {
    return 'false';
}
$hash = getAccountInfos($db, $playerTag);
if (validate_pw($password, $hash)) {
    $playerId = intval(getPlayerByTag($db, $playerTag)['id']);
    updatePassword($db, $playerId, $password);
} else {
    return 'false';
}

return 'true';