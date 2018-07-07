<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 07/07/2018
 * Time: 00:36
 */

include("../tools/api_conf.php");
include("../tools/database.php");

$battles = getWarBattlesFromApi($api);
$currentWar = getWarFromApi($api);
$lastWar = getLastWarEndDate($db);
$currentEnd = $currentWar['warEndTime'];
$lastEnd = intval($lastWar['created']);
$warId = getCurrentWar($db)['id'];
$state = getWarStateFromApi($api);

if ($state == "warDay") {
    cleanDeckResults($db, $warId);
}

foreach ($battles as $battle) {
    if ($battle['type'] != 'clanWarWarDay')
        continue;

    if ($battle['utcTime'] < $lastEnd || $battle['utcTime'] > $currentEnd)
        continue;

    $winResult = $battle['winner'];
    $win = $winResult <= 0 ? 0 : 1;
    $crowns = $battle['teamCrowns'];
    $team = $battle['team'][0];
    $deckLine = $team['deckLink'];
    $deck = $team['deck'];
    $card1 = getCardId($db, $deck, 0);
    $card2 = getCardId($db, $deck, 1);
    $card3 = getCardId($db, $deck, 2);
    $card4 = getCardId($db, $deck, 3);
    $card5 = getCardId($db, $deck, 4);
    $card6 = getCardId($db, $deck, 5);
    $card7 = getCardId($db, $deck, 6);
    $card8 = getCardId($db, $deck, 7);
    $getExistingDeck = getExistingDeck($db, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, 610);

    if (!$getExistingDeck) {
        $deckId = getLastDeckId($db, 610, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $warId);
        insertDeckResults($db, $deckId, 1, $win, $crowns);
    } else {
        $deckResults = getDeckResults($db, $getExistingDeck['id']);
        $win += $deckResults['wins'];
        $played = $deckResults['played'] + 1;
        $crowns += $deckResults['crowns'];
        updateDeckResults($db, $getExistingDeck['id'], $played, $win, $crowns);
    }
}

function getCardId($db, $deck, $pos)
{
    $cardId = getCardByCrId($db, $deck[$pos]['id']);
    return intval($cardId['id']);
}