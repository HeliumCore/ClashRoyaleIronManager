<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 27/06/2018
 * Time: 23:07
 */

include("../tools/database.php");
include("../tools/api_conf.php");

$wars = getWarLogFromApi($api);
$allPlayers = getAllPlayersInClan($db);
foreach ($wars as $war) {
    if ($war['seasonNumber'] <= 5 || $war['createdDate'] == 1530223645
        || $war['createdDate'] == 1530569765 || $war['createdDate'] == 1530396482) {
        continue;
    }
    $created = $war['createdDate'];
    $warId = getWarID(
        $db,
        getWar($db, $created),
        getCurrentWar($db),
        $created);

    foreach ($allPlayers as $player) {
        $cardsEarned = null;
        $battlesPlayed = null;
        $wins = null;
        foreach ($war['participants'] as $participant) {
            if ($player['tag'] == $participant['tag']) {
                $cardsEarned = $participant['cardsEarned'];
                $battlesPlayed = $participant['battlesPlayed'];
                $wins = $participant['wins'];
            }
        }
        $cardsEarned = $cardsEarned != null ? $cardsEarned : 0;
        $battlesPlayed = $battlesPlayed != null ? $battlesPlayed : 0;
        $wins = $wins != null ? $wins : 0;
        $playerId = intval($player['id']);
        $playerWarResult = getPlayerWar($db, $playerId, $warId);
        if (sizeof($playerWarResult) > 0) {
            updatePlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId);
        } else {
            insertPlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId);
        }
        $playerWarResult = null;
    }
}

setLastUpdated($db, "war_stats");