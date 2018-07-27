<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 27/06/2018
 * Time: 23:07
 */

include(__DIR__ . "/../tools/database.php");
include(__DIR__ . "/../tools/api_conf.php");

$allPlayers = getAllPlayersInClan($db);

foreach (getWarLogFromApi($api) as $war) {
    $created = $war['createdDate'];
    $season = $war['seasonNumber'];
    $warId = getWarID(
        $db,
        getWar($db, $created),
        getCurrentWar($db),
        $created,
        $season
    );

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