<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 01/08/2018
 * Time: 18:49
 */

include(__DIR__ . "/../../tools/database.php");

if (isset($_POST['tag']) && !empty($_POST['tag'])) $playerTag = $_POST['tag'];
else {
    echo 'false';
    return;
}

if (getAccountInfos($db, $playerTag) == false)
    echo 'false';
else
    echo 'true';