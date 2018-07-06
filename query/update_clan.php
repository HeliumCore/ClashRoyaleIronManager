<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 27/06/2018
 * Time: 20:01
 */

include("../tools/api_conf.php");
include("../tools/database.php");

$allPlayers = getAllPlayersInClan($db);
$allPlayersTags = [];
$allPlayersTagsInClan = [];

foreach ($allPlayers as $p) {
    array_push($allPlayersTags, $p['tag']);
}

foreach (getClanFromApi($api)['members'] as $player) {
    $result = getPlayerByTag($db, $player['tag']);
    if (is_array($result)) {
        updatePlayer($db, $player['name'], $player['rank'], $player['trophies'], $player['role'], $player['expLevel'],
            $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'], $player['donationsDelta'],
            $player['donationsPercent'], $player['tag']);
    } else {
        insertPlayer($db, $player['name'], $player['tag'], $player['rank'], $player['trophies'], $player['role'],
            $player['expLevel'], $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'],
            $player['donationsDelta'], $player['donationsPercent']);
    }
    array_push($allPlayersTagsInClan, $player['tag']);
}

foreach (array_diff($allPlayersTags, $allPlayersTagsInClan) as $tag) {
    removePlayerFromClan($db, $tag);
}