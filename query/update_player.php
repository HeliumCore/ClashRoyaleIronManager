<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 01:01
 */
include("../tools/api_conf.php");
include("../tools/database.php");

if (isset($_GET['tag']) && !empty($_GET['tag'])) $playerTag = $_GET['tag'];
else return;
//ligne de test
//else $playerTag = "8YG908L08";

$apiQuery = "https://api.royaleapi.com/player/" . $playerTag;
$playerApiResult = json_decode(file_get_contents($apiQuery, true, $context), true);

updateMaxTrophies($db, $playerApiResult['stats']['maxTrophies'], $playerTag);

// DECKS
$deck = $playerApiResult['currentDeck'];
$currentDeck = getCurrentDeck($db, $deck);
$playerId = intval(getPlayerByTag($db, $playerTag)['id']);
$getExistingDeck = getExistingDeck($db, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3],
    $currentDeck[4], $currentDeck[5], $currentDeck[6], $currentDeck[7], $playerId);

disableAllDeck($db, $playerId);
if (!$getExistingDeck) {
    insertDeck($db, $playerId, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3], $currentDeck[4],
        $currentDeck[5], $currentDeck[6], $currentDeck[7]);
} else {
    enableOldDeck($db, $getExistingDeck['id']);
}