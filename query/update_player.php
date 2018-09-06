<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 01:01
 */

if (isset($_GET['tag']) && !empty($_GET['tag'])) $playerTag = $_GET['tag'];
else return;

require(__DIR__ . "/../tools/api.class.php");
require(__DIR__ . "/../tools/database.php");
require(__DIR__ . "/../models/player.class.php");

ClashRoyaleApi::create();
$player = new Player($playerTag);
$player->setPlayerId();
$apiInfos = $player->getPlayerFromApi();

$player->updateMaxTrophies($apiInfos['bestTrophies']);
$deck = $apiInfos['currentDeck'];
$currentDeck = $player->getCardsIds($deck);
$player->updateDeck($currentDeck);
$player->updatePlayerCards($apiInfos['cards']);
$player->setLastUpdated();