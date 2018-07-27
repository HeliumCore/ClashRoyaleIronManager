<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 07/07/2018
 * Time: 00:36
 */

include(__DIR__ . "/../tools/api_conf.php");
include(__DIR__ . "/../tools/database.php");

$battles = getWarBattlesFromApi($api);
$currentWar = getWarFromApi($api);
$lastWar = getLastWarEndDate($db);
$currentEnd = $currentWar['warEndTime'];
$lastEnd = intval($lastWar['created']);
$warId = getCurrentWar($db)['id'];
$state = getWarStateFromApi($api);

if ($state == "warDay")
    cleanDeckResults($db, $warId);
else {
    setLastUpdated($db, "war_decks");
    return;
}

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

    $allWarDecks = getAllCurrentWarDecksId($db, $warId);
    if (sizeof($allWarDecks) > 0) {
        foreach ($allWarDecks as $warDeck) {
            $warDeckId = intval($warDeck['id']);
            if (isSameDeck($db, $deck, $warDeckId)) {
                $deckId = $warDeckId;
                break;
            }
        }
    }

    if ($deckId > 0) {
        $deckResults = getDeckResults($db, $deckId);
        if ($deckResults) {
            $played = intval($deckResults['played']);
            $win += intval($deckResults['wins']);
            $crowns += intval($deckResults['crowns']);
            $played++;
            updateDeckResults($db, $deckId, $played, $win, $crowns);
        } else {
            insertDeckResults($db, $deckId, 1, $win, $crowns);
        }
    } else {
        $deckId = createDeck($db, 610);
        for ($i = 0; $i <= 7; $i++) {
            insertCardDeck($db, getCardId($db, $deck, $i), $deckId);
        }
        insertDeckWar($db, $deckId, $warId);
        insertDeckResults($db, $deckId, 1, $win, $crowns);
    }
}

setLastUpdated($db, "war_decks");

function getCardId($db, $deck, $pos)
{
    $cardId = getCardByCrId($db, $deck[$pos]['id']);
    return intval($cardId['id']);
}

// deck1 : deck provenant de l'API
// deck 2 : id du deck de la base
function isSameDeck($db, $deck1, $deck2)
{
    $deck1Cards = [];
    for ($i = 0; $i <= 7; $i++) {
        array_push($deck1Cards, getCardByCrId($db, $deck1[$i]['id'])['id']);
    }

    $deckId = getDeckFromCards($db, $deck1Cards[0], $deck1Cards[1], $deck1Cards[2], $deck1Cards[3], $deck1Cards[4],
        $deck1Cards[5], $deck1Cards[6], $deck1Cards[7])['deck_id'];

    if ($deckId == null)
        return false;

    return $deckId == $deck2;
}