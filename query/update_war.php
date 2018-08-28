<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/06/18
 * Time: 10:08
 */

include(__DIR__ . "/../tools/api_conf.php");
include(__DIR__ . "/../tools/database.php");

if (!isApiRunning($api))
    return;

$data = getWarFromApi($api);
$numberOfCurrentPlayers = getNumberOfCurrentPlayersInWar($db);
if (getNumberOfPlayersInClan($db) > $numberOfCurrentPlayers && $numberOfCurrentPlayers != 0) {
    $notEligible = getNotEligiblePlayers($db);
}
$warId = getCurrentWarId($db);
$warState = $data['state'];

// Standings
if ($warState == 'warDay') {
    foreach ($data['standings'] as $clan) {
        $getStanding = getStandings($db, $clan['tag'], $warId);

        if (is_array($getStanding)) {
            updateStanding($db, $clan['participants'], $clan['battlesPlayed'], $clan['wins'], $clan['crowns'],
                $clan['warTrophies'], $getStanding['id']);
        } else {
            insertStanding($db, $clan['tag'], $clan['name'], $clan['participants'], $clan['battlesPlayed'],
                $clan['wins'], $clan['crowns'], $clan['warTrophies'], $warId);
        }
    }
} else if (($warState == 'collectionDay' && sizeof($data['participants']) <= 0) || $warState != 'collectionDay') {
    return;
}
$counter = 0;
foreach (getAllPlayersInClan($db) as $player) {
    foreach ($notEligible as $notEligiblePlayer) {
        if ($player['id'] == $notEligiblePlayer['id']) {
            continue 2;
        }
    }
    $counter++;
    $playerId = intval($player['id']);
    $getPlayerWarResult = getPlayerWar($db, $playerId, $warId);
    $cardsEarned = null;
    $battlesPlayed = null;
    $wins = null;
    foreach ($data['participants'] as $participant) {
        if ($player['tag'] == $participant['tag']) {
            if ($warState == "collectionDay") {
                $cardsEarned = $participant['cardsEarned'];
            }
            $battlesPlayed = $participant['battlesPlayed'];
            $wins = $participant['wins'];
        }
    }
    if ($warState == "collectionDay") {
        $cardsEarned = $cardsEarned != null ? $cardsEarned : 0;
    }
    $battlesPlayed = $battlesPlayed != null ? $battlesPlayed : 0;
    $wins = $wins != null ? $wins : 0;
    if (is_array($getPlayerWarResult)) {
        $playerWarId = intval($getPlayerWarResult['player_war_id']);
        // Si le joueur a déjà été enregistré pour cette guerre, on update
        if ($warState == "collectionDay") {
            updateCollectionDay($db, $cardsEarned, $battlesPlayed, $wins, $playerWarId);
        } else if ($warState == "warDay") {
            updateWarDay($db, $battlesPlayed, $wins, $playerWarId);
        }
    } else {
        // Si le joueur n'est pas encore dans cette guerre, on insert
        if ($warState == "collectionDay") {
            insertCollectionDay($db, $cardsEarned, $battlesPlayed, $wins, $player['id'], $warId);
        }
    }
}
setLastUpdated($db, "war");