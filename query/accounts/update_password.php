<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 16:59
 */

include(__DIR__ . "/../../tools/accounts.php");

if (
    isset($_GET['password']) && !empty($_GET['password'])
    && isset($_GET['old_password']) && !empty($_GET['old_password'])
    && isset($_GET['tag']) && !empty($_GET['tag'])
) {
    $password = $_GET['password'];
    $playerTag = $_GET['tag'];
} else {
    return 'false';
}
$hash = getHashedPassword($db, $playerTag);
if (validate_pw($password, $hash)) {
    $playerId = intval(getPlayerByTag($db, $playerTag)['id']);
    updatePassword($db, $playerId, $password);
} else {
    return 'false';
}

return 'true';