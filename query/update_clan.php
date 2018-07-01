<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 27/06/2018
 * Time: 20:01
 */

include("../tools/api_conf.php");
include("../tools/database.php");

foreach (getClanFromApi($api)['members'] as $player) {
    $result = getPlayerByTag($db, $player['tag']);
    if (is_array($result)) {
        updatePlayer($db, $player['name'], $player['rank'], $player['trophies'], $player['role'], $player['expLevel'],
            $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'], $player['donationsDelta'],
            $player['donationsPercent'], $player['tag']);
    } else {
        insertPlayer($db, $player['name'], $player['tag'], $player['rank'], $player['trophies'], $player['role'],
            $player['expLevel'], $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'],
            $player['donationsDelta'], $player['donationsPercent']);
    }
}

// TODO faire la requete qui verifie si une personne est toujours dans le clan ou pas, et si non, passer le in_clan a 0