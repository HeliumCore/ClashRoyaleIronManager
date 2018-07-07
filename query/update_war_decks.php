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

if ($state == "warDay")
    cleanDeckResults($db, $warId);
else
    return;

foreach ($battles as $battle) {
    if ($battle['type'] != 'clanWarWarDay')
        continue;

    if ($battle['utcTime'] < $lastEnd || $battle['utcTime'] > $currentEnd)
        continue;

    $winResult = $battle['winner'];
    $team = $battle['team'][0];
    $deckLine = $team['deckLink'];
    $deck = $team['deck'];
    $win = $winResult <= 0 ? 0 : 1;
    $crowns = $battle['teamCrowns'];
    $deckId = 0;
    $played = 0;

    $allWarDecks = getAllCurrentWarDecks($db);
    if (sizeof($allWarDecks) > 0) {
        foreach ($allWarDecks as $warDeck) {
            if (isSameDeck($db, $deck, $warDeck)) {
                $deckId = intval($warDeck['id']);
                break;
            }
        }
    }

    if ($deckId > 0) {
        $deckResults = getDeckResults($db, $deckId);
        $played = intval($deckResults['played']);
        $win += intval($deckResults['wins']);
        $crowns += intval($deckResults['crowns']);
        $played++;
        updateDeckResults($db, $deckId, $played, $win, $crowns);
    } else {
        $card1 = getCardId($db, $deck, 0);
        $card2 = getCardId($db, $deck, 1);
        $card3 = getCardId($db, $deck, 2);
        $card4 = getCardId($db, $deck, 3);
        $card5 = getCardId($db, $deck, 4);
        $card6 = getCardId($db, $deck, 5);
        $card7 = getCardId($db, $deck, 6);
        $card8 = getCardId($db, $deck, 7);
        $deckId = getLastDeckId($db, 610, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $warId);
        insertDeckResults($db, $deckId, 1, $win, $crowns);
    }
}

function getCardId($db, $deck, $pos)
{
    $cardId = getCardByCrId($db, $deck[$pos]['id']);
    return intval($cardId['id']);
}

// deck1 : deck provenant de l'API
// deck 2 : deck provenant de la base
function isSameDeck($db, $deck1, $deck2)
{
    $list1 = [];
    $list2 = [];
    $pos = 0;
    $crIds = getCrIdsByCards($db, $deck2['card_1'], $deck2['card_2'], $deck2['card_3'], $deck2['card_4'],
        $deck2['card_5'], $deck2['card_6'], $deck2['card_7'], $deck2['card_8']);

    while ($pos < 8) {
        array_push($list1, $deck1[$pos]['id']);
        array_push($list2, intval($crIds[$pos]['cr_id']));
        $pos++;
    }
    sort($list1);
    sort($list2);

    $diff = array_diff($list1, $list2);
    return sizeof($diff) == 0;
}