<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 07/07/2018
 * Time: 00:36
 */

require(__DIR__ . "/../tools/api.class.php");
require(__DIR__ . "/../tools/database.php");
require(__DIR__ . "/../models/war.class.php");
require(__DIR__ . "/../models/clan.class.php");

$clan = new Clan();
$members = $clan->getPlayers();
$warBattles = array();
foreach ($members as $member) {
    $player = new Player($member['tag']);
    $playerBattles = $player->getPlayerBattlesFromApi();
    foreach ($playerBattles as $battle) {
        if ($battle['type'] == "clanWarWarDay") {
            array_push($warBattles, $battle);
        }
    }
}

$war = new War();
$currentWar = $war->getWarFromApi();
$currentEnd = getTimeStampFromIso($currentWar['warEndTime']);
$lastEnd = intval($war->getLastWarEndDate());
$war->getCurrentWarId();
$warId = $war->getId();

foreach ($warBattles as $battle) {
    $combatTime = getTimeStampFromIso($battle['battleTime']);
    if ($combatTime < $lastEnd || $combatTime > $currentEnd)
        continue;

    $team = $battle['team'][0];
    if ($team['clan']['tag'] != "#9RGPL8PC")
        continue;

    $crowns = $team['crowns'];
    $win = $crowns > $battle['opponent'][0]['crowns'];
    $deck = $team['cards'];
    $p = new Player(ltrim($team['tag'], '#'));
    $currentDeck = $p->getCardsIds($deck);
    $deckId = $p->getDeckIdFromCards($currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3], $currentDeck[4],
        $currentDeck[5], $currentDeck[6], $currentDeck[7]);

    if ($deckId != null && $deckId > 0) {
        if ($war->isDeckUsedInCurrentWar($deckId)) {
            if ($war->getDeckResultsByTime($combatTime) == false) {
                $war->insertDeckResults($deckId, $win, $crowns, $combatTime);
            }
        } else {
            $war->insertDeckWar($deckId);
            $war->insertDeckResults($deckId, $win, $crowns, $combatTime);
        }
    } else {
        $deckId = $p->createDeck();
        for ($i = 0; $i <= 7; $i++) {
            $p->insertCardDeck($currentDeck[$i], $deckId);
        }
        $war->insertDeckWar($deckId);
        $war->insertDeckResults($deckId, $win, $crowns, $combatTime);
    }
}
$war->setLastUpdatedWarDecks();

function getTimeStampFromIso($d) {
    $dateString = substr($d, 0, 4) . "-" .
        substr($d, 4, 2) . "-" .
        substr($d, 6, 5) . ":" .
        substr($d, 11, 2) . ":" .
        substr($d, 13);
    $date = new DateTime($dateString);
    return intval($date->getTimestamp());
}