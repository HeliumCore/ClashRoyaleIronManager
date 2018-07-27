<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 27/07/18
 * Time: 11:20
 */

include("../tools/api_conf.php");
include("../tools/database.php");

foreach (getAllPlayersInClan($db) as $playerDB) {
    $playerTag = $playerDB['tag'];
    $player = getPlayerFromApi($api, $playerTag);
    updateMaxTrophies($db, $player['stats']['maxTrophies'], $playerTag);
    $deck = $player['currentDeck'];
    $currentDeck = getCurrentDeck($db, $deck);
    $playerId = intval(getPlayerByTag($db, $playerTag)['id']);

    disableAllDeck($db, $playerId);
    $deckId = getDeckFromCards($db, $currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3], $currentDeck[4],
        $currentDeck[5], $currentDeck[6], $currentDeck[7], $playerId)['deck_id'];

// Si le deck n'existe pas, on le crée, sinon on l'enable pour le joueur
    if ($deckId == null) {
        $deckId = createDeck($db, $playerId);
        for ($i = 0; $i <= 7; $i++) {
            insertCardDeck($db, $currentDeck[$i], $deckId);
        }
    } else {
        enableOldDeck($db, $deckId);
    }

    if (is_array(getLastUpdatedPlayer($db, $playerTag)))
        setLastUpdatedPlayer($db, $playerTag);
    else
        insertLastUpdatedPlayer($db, $playerTag);

}