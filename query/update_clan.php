<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 27/06/2018
 * Time: 20:01
 */

require(__DIR__ . "/../tools/api.class.php");
require(__DIR__ . "/../tools/database.php");
require(__DIR__ . "/../models/clan.class.php");
require(__DIR__ . "/../models/player.class.php");

ClashRoyaleApi::create();

$clan = new Clan();
$allPlayers = $clan->getPlayers();

$allPlayersTags = [];
$allPlayersTagsInClan = [];

foreach ($allPlayers as $p) {
    array_push($allPlayersTags, $p['tag']);
}

foreach ($clan->getMembersFromApi() as $apiPlayer) {
    $player = new Player(ltrim($apiPlayer['tag'], '#'));
    $player->updatePlayer($apiPlayer['name'], $apiPlayer['clanRank'], $apiPlayer['trophies'], $apiPlayer['role'], $apiPlayer['expLevel'],
        $apiPlayer['arena']['name'], $apiPlayer['donations'], $apiPlayer['donationsReceived']);
    array_push($allPlayersTagsInClan, $player->getTag());
}

foreach (array_diff($allPlayersTags, $allPlayersTagsInClan) as $tag) {
    $clan->removePlayerFromClan($tag);
}

$clan->setLastUpdated();