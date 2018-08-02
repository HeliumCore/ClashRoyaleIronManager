<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 16:59
 */

include(__DIR__ . "/../tools/accounts.php");
include(__DIR__ . "/../tools/database.php");

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
$playerId = intval(getPlayerByTag($db, $playerTag)['id']);
$hash = getHashedPassword($db, $playerId);
if (validate_pw($password, $hash)) {
    updatePassword($db, $playerId, $password);
} else {
    return 'false';
}

return 'true';