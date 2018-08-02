<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 14:51
 */

include(__DIR__ . "/../tools/accounts.php");
include(__DIR__ . "/../tools/database.php");

if (isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['tag']) && !empty($_POST['tag'])) {
    $password = $_POST['password'];
    $playerTag = $_POST['tag'];
    var_dump($password);
    var_dump($playerTag);
} else {
    var_dump("fuck");
    echo 'false';
    return;
}
$playerId = intval(getPlayerByTag($db, $playerTag)['id']);
$passwordHashed = generate_hash($password);
createAccount($db, $playerId, $password);
echo 'true';