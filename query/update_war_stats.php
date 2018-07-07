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
// todo revoir les requetes de passage de current war a old war. perte de collection
// todo revoir eligible players (ex: bonobo marqué absent alors qu'il est arrivé en cours de guerre)
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