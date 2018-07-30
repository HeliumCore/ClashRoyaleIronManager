<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 07/07/2018
 * Time: 00:36
 */

include(__DIR__ . "/../tools/api_conf.php");
include(__DIR__ . "/../tools/database.php");

$currentWar = getWarFromApi($api);
$lastWar = getLastWarEndDate($db);
$currentEnd = $currentWar['warEndTime'];
$lastEnd = intval($lastWar['created']);
$warId = getCurrentWar($db)['id'];

foreach (getWarBattlesFromApi($api) as $battle) {
    if ($battle['type'] != 'clanWarWarDay')
        continue;

    $combatTime = $battle['utcTime'];
    if ($combatTime < $lastEnd || $combatTime > $currentEnd)
        continue;

    $team = $battle['team'][0];
    if ($team['clan']['tag'] != "9RGPL8PC")
        continue;

    $winResult = $battle['winner'];
    $deckLine = $team['deckLink'];
    $deck = $team['deck'];
    saveCards($deck);
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
        for ($i = 0; $i <= 7; $i++) {
            insertCardDeck($db, $currentDeck[$i], $deckId);
        }
        insertDeckWar($db, $deckId, $warId);
        insertDeckResults($db, $deckId, $win, $crowns, $combatTime);
    }
}
setLastUpdated($db, "war_decks");

function saveCards($deck)
{
    foreach ($deck as $card) {
        $name = __DIR__ . "/../images/new_cards/" . $card['key'] . ".png";
        if (!file_exists($name)) {
            $url = $card['icon'];
            file_put_contents($name, file_get_contents($url));
        }
    }
}