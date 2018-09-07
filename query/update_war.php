<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/06/18
 * Time: 10:08
 */

require(__DIR__ . "/../tools/api.class.php");
require(__DIR__ . "/../tools/database.php");
require(__DIR__ . "/../models/war.class.php");
require(__DIR__ . "/../models/clan.class.php");

ClashRoyaleApi::create();
$war = new War();
$clan = new Clan();
$war->getCurrentWarId();
$data = $war->getWarFromApi();
if ($data == false)
    return;

$notEligible = $war->getNotEligiblePlayers();
$warState = $data['state'];

// Standings
if ($warState == 'warDay')
    $war->updateStandings($data['clans']);
else if (($warState == 'collectionDay' && sizeof($data['participants']) <= 0) || $warState != 'collectionDay')
    return;

foreach ($clan->getPlayers() as $player) {
    foreach ($notEligible as $notEligiblePlayer) {
        if ($player['id'] == $notEligiblePlayer['id'])
            continue 2;
    }

    $p = new Player($player['tag']);
    $p->setPlayerId();

    $getPlayerWarResult = $war->getPlayerWar($p->getId());
    $cardsEarned = null;
    $battlesPlayed = null;
    $wins = null;
    foreach ($data['participants'] as $participant) {
        if ($player['tag'] == ltrim($participant['tag'], "#")) {
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
            if (
                intval($getPlayerWarResult['cards_earned']) < $cardsEarned &&
                intval($getPlayerWarResult['collection_played']) < $battlesPlayed &&
                intval($getPlayerWarResult['collection_won']) <= $wins
            ) {
                $war->updateCollectionDay($cardsEarned, $battlesPlayed, $wins, $playerWarId);
            }
        } else if ($warState == "warDay") {
            if (
                intval($getPlayerWarResult['battle_played']) < $battlesPlayed &&
                intval($getPlayerWarResult['battle_won']) <= $wins
            ) {
                $war->updateWarDay($battlesPlayed, $wins, $playerWarId);
            }
        }
    } else {
        if ($warState == "collectionDay") {
            $war->insertCollectionDay($cardsEarned, $battlesPlayed, $wins, $player['id']);
        }
    }
}
$war->setLastUpdated();