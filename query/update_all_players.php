<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 27/07/18
 * Time: 11:20
 */

include(__DIR__ . "/../tools/api_conf.php");
include(__DIR__ . "/../tools/database.php");

foreach (getAllPlayersInClan($db) as $playerDB) {
    $playerTag = $playerDB['tag'];
    $player = getPlayerFromApi($api, $playerTag);
    updateMaxTrophies($db, $player['stats']['maxTrophies'], $playerTag);
    $deck = $player['currentDeck'];
    $currentDeck = getCurrentDeck($db, $deck);
    $playerId = intval(getPlayerByTag($db, $playerTag)['id']);

    disableAllDeck($db, $playerId);
    $deckId = getDeckIdFromCards($db, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3], $currentDeck[4],
        $currentDeck[5], $currentDeck[6], $currentDeck[7]);

    if (getPlayerDeck($db, $deckId, $playerId) != null) {
        enableOldDeck($db, $deckId, $playerId);
    } else if ($deckId != null && $deckId > 0) {
        createPlayerDeck($db, $deckId, $playerId);
    } else {
        $deckId = createDeck($db);
        $totalElixir = 0;
        for ($i = 0; $i <= 7; $i++) {
            insertCardDeck($db, $currentDeck[$i], $deckId);
            $totalElixir += getCardElixirCostById($db, $currentDeck[$i]);
        }
        $elixirCost = round(($totalElixir / 8), 1);
        createPlayerDeck($db, $deckId, $playerId);
        updateElixirCost($db, $deckId, $elixirCost);
    }

    foreach ($player['cards'] as $card) {
        $cardId = intval(getCardByCrId($db, $card['id'])['id']);
        $level = getCardLevelByPlayer($db, $cardId, $playerId);
        if ($level) {
            updateCardLevelByPlayer($db, $cardId, $playerId, $card['level']);
        } else {
            insertCardLevelByPlayer($db, $cardId, $playerId, $card['level']);
        }
    }

    if (is_array(getLastUpdatedPlayer($db, $playerTag)))
        setLastUpdatedPlayer($db, $playerTag);
    else
        insertLastUpdatedPlayer($db, $playerTag);

}