<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 27/06/2018
 * Time: 23:07
 */

include("update_clan.php");
include("../tools/sql.php");

$apiResult = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/warlog", true, $context);
$data = json_decode($apiResult, true);

$allPlayers = getAllPlayersInClan($db);
foreach ($data as $war) {
    if ($war['seasonNumber'] <= 5 || $war['createdDate'] == 1530223645) {
        continue;
    }
    $created = $war['createdDate'];
    $warId = getWarID(
        $db,
        getWar($db, $created),
        getCurrentWar($db),
        $created);

    foreach ($allPlayers as $player) {
        global $cardsEarned;
        global $battlesPlayed;
        global $wins;
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
        $playerWarResult = getPlayerWar($db, $player['id'], $warId);

        if (is_array($playerWarResult)) {
            updatePlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $player['id'], $warId);
        } else {
            insertPlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $player['id'], $warId);
        }
        $playerWarResult = null;
    }
}