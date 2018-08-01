<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 01/08/2018
 * Time: 18:49
 */

include(__DIR__ . "/../tools/database.php");

if (isset($_GET['tag']) && !empty($_GET['tag'])) $playerTag = $_GET['tag'];
else echo json_encode(array("exists" => false));

$player = getPlayerByTag($db, $playerTag);

if ($player == null)
    echo json_encode(array("exists" => false));
else
    echo json_encode(array("exists" => true));