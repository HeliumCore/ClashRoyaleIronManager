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

// MAX TROPHIES
$updatePattern = "
UPDATE players
SET players.max_trophies = %d
WHERE players.tag = \"%s\" 
";
$maxTrophies = $playerApiResult['stats']['maxTrophies'];
execute_query($db, utf8_decode(sprintf($updatePattern, $maxTrophies, $playerTag)));

// DECKS
// https://link.clashroyale.com/deck/fr?deck=26000031;26000041;26000022;26000017;26000016;28000004;26000026;28000011
$insertDeckPattern = "
INSERT INTO decks (player_id, card_1, card_2, card_3, card_4, card_5, card_6, card_7, card_8, decks.current)
VALUES(%d, %d, %d, %d, %d, %d, %d, %d, %d, 1)
";

$getPlayerId = "
SELECT id
FROM players
WHERE tag = \"%s\"
";
$deck = $playerApiResult['currentDeck'];
$currentDeck = [];

$getCardIdPattern = "
SELECT cards.id
FROM cards
WHERE cr_id = %d
";

foreach ($deck as $card) {
    $cardId = fetch_query($db, sprintf($getCardIdPattern, $card['id']))['id'];
    array_push($currentDeck, $cardId);
}

$getExistingDeckPattern = "
SELECT decks.id
FROM decks
WHERE card_1 = %d
AND card_2 = %d
AND card_3 = %d
AND card_4 = %d
AND card_5 = %d
AND card_6 = %d
AND card_7 = %d
AND card_8 = %d
AND player_id = 
";

$updateDecksPattern = "
UPDATE decks
SET decks.current = %d
WHERE decks.player_id = %d
";

$useAgainDeck = "
UPDATE decks
SET decks.current = 1
WHERE decks.id = %d
";
$playerResult = fetch_query($db, utf8_decode(sprintf($getPlayerId, $playerTag)));
$playerId = intval($playerResult['id']);
$query = sprintf($getExistingDeckPattern, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3],
    $currentDeck[4], $currentDeck[5], $currentDeck[6], $currentDeck[7], $currentDeck[8]);
$getExistingDeck = fetch_query($db, $query . $playerId);

if (!$getExistingDeck) {
    execute_query($db, sprintf($insertDeckPattern, $playerId, $currentDeck[0], $currentDeck[1], $currentDeck[2],
        $currentDeck[3], $currentDeck[4], $currentDeck[5], $currentDeck[6], $currentDeck[7], $currentDeck[8]));
    execute_query($db, sprintf($updateDecksPattern, 0, $playerId));
} else {
    execute_query($db, sprintf($useAgainDeck, $getExistingDeck['id']));
}