<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 01/08/2018
 * Time: 18:44
 */

include(__DIR__ . "/../../tools/database.php");

$players = getAllPlayersInClan($db);
$availablePlayers = array();
foreach ($players as $player) {
    array_push($availablePlayers, utf8_decode($player['tag']) . ' - ' . utf8_decode($player['name']));
}

echo json_encode($availablePlayers);