<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 07/09/18
 * Time: 10:58
 */

require(__DIR__ . "/../tools/api.class.php");
require(__DIR__ . "/../tools/database.php");
require(__DIR__ . "/../models/war.class.php");
require(__DIR__ . "/../models/clan.class.php");

ClashRoyaleApi::create();
$clan = new Clan();
$war = new War();

$warLog = $war->getWarLogFromApi();

if ($warLog == false)
    return;

foreach ($warLog['items'] as $apiWar) {
    $season = $apiWar['seasonId'];
    $d = $apiWar['createdDate'];
    $dateString = substr($d, 0, 4) . "-" .
        substr($d, 4, 2) . "-" .
        substr($d, 6, 5) . ":" .
        substr($d, 11, 2) . ":" .
        substr($d, 13);
    $date = new DateTime($dateString);
    $created = $date->getTimestamp();

    $warId = $war->getWarID(
        $war->getWarFromCreated($created),
        $war->getCurrentWar(),
        $created,
        $season
    );

    foreach ($clan->getPlayers() as $player) {
        $cardsEarned = null;
        $battlesPlayed = null;
        $wins = null;
        foreach ($apiWar['participants'] as $participant) {
            if ($player['tag'] == $participant['tag']) {
                $cardsEarned = $participant['cardsEarned'];
                $battlesPlayed = $participant['battlesPlayed'];
                $wins = $participant['wins'];
            }
        }
        $cardsEarned = $cardsEarned != null ? $cardsEarned : 0;
        $battlesPlayed = $battlesPlayed != null ? $battlesPlayed : 0;
        $wins = $wins != null ? $wins : 0;
        $war->updateWarLog($cardsEarned, $battlesPlayed, $wins, intval($player['id']), $warId);
    }
}

$war->setLastUpdatedWarLog();