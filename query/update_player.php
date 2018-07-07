<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 01:01
 */

if (isset($_GET['tag']) && !empty($_GET['tag'])) $playerTag = $_GET['tag'];
else return;
//else $playerTag = "LJV8928C";
include("../tools/api_conf.php");
include("../tools/database.php");

$player = getPlayerFromApi($api, $playerTag);
updateMaxTrophies($db, $player['stats']['maxTrophies'], $playerTag);
$deck = $player['currentDeck'];
$currentDeck = getCurrentDeck($db, $deck);
$playerId = intval(getPlayerByTag($db, $playerTag)['id']);
$getExistingDeck = getExistingDeck($db, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3],
    $currentDeck[4], $currentDeck[5], $currentDeck[6], $currentDeck[7], $playerId);

disableAllDeck($db, $playerId);
if (!$getExistingDeck) {
    insertDeck($db, $playerId, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3], $currentDeck[4],
        $currentDeck[5], $currentDeck[6], $currentDeck[7], null);
} else {
    enableOldDeck($db, $getExistingDeck['id']);
}