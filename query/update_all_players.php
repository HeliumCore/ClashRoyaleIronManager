<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 27/07/18
 * Time: 11:20
 */


require(__DIR__ . "/../tools/api.class.php");
require(__DIR__ . "/../tools/database.php");
require(__DIR__ . "/../models/player.class.php");
require(__DIR__ . "/../models/clan.class.php");

ClashRoyaleApi::create();

$clan = new Clan();
foreach ($clan->getPlayers() as $playerDB) {
    $player = new Player($playerDB['tag']);
    $player->setPlayerId();
    $apiInfos = $player->getPlayerFromApi();
    if ($apiInfos == false)
        return;

    $player->updateMaxTrophies($apiInfos['bestTrophies']);
    $deck = $apiInfos['currentDeck'];
    $currentDeck = $player->getCardsIds($deck);
    $player->updateDeck($currentDeck);
    $player->updatePlayerCards($apiInfos['cards']);
    $player->setLastUpdated();
}