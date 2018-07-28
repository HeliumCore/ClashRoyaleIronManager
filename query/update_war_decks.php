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

foreach ($battles as $battle) {
    if ($battle['type'] != 'clanWarWarDay')
        continue;

    $combatTime = $battle['utcTime'];
    if ($combatTime < $lastEnd || $combatTime > $currentEnd)
        continue;

    $winResult = $battle['winner'];
    $team = $battle['team'][0];
    $deckLine = $team['deckLink'];
    $deck = $team['deck'];
    $tag = $team['tag'];
    $playerId = intval(getPlayerByTag($db, $tag)['id']);
    $win = $winResult <= 0 ? 0 : 1;
    $crowns = $battle['teamCrowns'];

    $currentDeck = getCurrentDeck($db, $deck);
    $deckId = getDeckIdFromCards($db, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3], $currentDeck[4],
        $currentDeck[5], $currentDeck[6], $currentDeck[7]);

    if ($deckId != null && $deckId > 0) {
        if (isDeckUsedInCurrentWar($db, $warId, $deckId)) {
            if (getDeckResultsByTime($db, $combatTime) == false) {
                insertDeckResults($db, $deckId, $win, $crowns, $combatTime);
            }
        } else {
            insertDeckWar($db, $deckId, $warId);
            insertDeckResults($db, $deckId, $win, $crowns, $combatTime);
        }
    } else {
        $deckId = createDeck($db);
        $totalElixir = 0;
        for ($i = 0; $i <= 7; $i++) {
            insertCardDeck($db, $currentDeck[$i], $deckId);
            $totalElixir += getCardElixirCostById($db, $currentDeck[$i]);
        }
        $elixirCost = round(($totalElixir / 8), 2);
        updateElixirCost($db, $deckId, $elixirCost);
        insertDeckWar($db, $deckId, $warId);
        insertDeckResults($db, $deckId, $win, $crowns, $combatTime);
    }
}
setLastUpdated($db, "war_decks");