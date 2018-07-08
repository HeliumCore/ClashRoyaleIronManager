<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 01:01
 */

if (isset($_GET['tag']) && !empty($_GET['tag'])) $playerTag = $_GET['tag'];
else return;

include("../tools/api_conf.php");
include("../tools/database.php");
$player = getPlayerFromApi($api, $playerTag);
updateMaxTrophies($db, $player['stats']['maxTrophies'], $playerTag);
$deck = $player['currentDeck'];
$currentDeck = getCurrentDeck($db, $deck);
$playerId = intval(getPlayerByTag($db, $playerTag)['id']);
$playerDecks = getPlayerDecks($db, $playerId);

$deckId = -1;
if (sizeof($playerDecks) > 0) {
    foreach ($playerDecks as $playerDeck) {
        if (isSameDeck($db, $deck, $playerDeck)) {
            $deckId = intval($playerDeck['id']);
            break;
        }
    }
}

disableAllDeck($db, $playerId);
if ($deckId < 0) {
    insertDeck($db, $playerId, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3], $currentDeck[4],
        $currentDeck[5], $currentDeck[6], $currentDeck[7], null);
} else {
    enableOldDeck($db, $deckId);
}

$last = getLastUpdatedPlayer($db, $playerTag);
if (is_array($last))
    setLastUpdatedPlayer($db, $playerTag);
else
    insertLastUpdatedPlayer($db, $playerTag);

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