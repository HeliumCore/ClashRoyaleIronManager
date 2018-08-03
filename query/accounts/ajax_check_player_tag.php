<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 01/08/2018
 * Time: 18:49
 */

include(__DIR__ . "/../../tools/database.php");

if (isset($_GET['tag']) && !empty($_GET['tag'])) $playerTag = $_GET['tag'];
else {
    echo 'false';
    return;
}

if (getPlayerByTag($db, $playerTag) == null)
    echo 'false';
else
    echo 'true';