<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 27/06/2018
 * Time: 20:01
 */

include("../tools/api_conf.php");
include("../tools/database.php");

$result = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC", true, $context);
$data = json_decode($result, true);

$pattern = "INSERT INTO players VALUES ('', \"%s\", \"%s\", %d, %d, %d, %d, %d, %d, %d, %d, %d, %f)";
$rolePattern = "SELECT id FROM role WHERE machine_name LIKE \"%s\"";
$truncateQuery = "TRUNCATE players";

// Truncate la table players (remise à zéro)
execute_query($db, $truncateQuery);

foreach ($data["members"] as $player) {
    // Récupère l'id du role en fonction de son machine_name
    $roleQuery = sprintf($rolePattern, $player['role']);
    $roleResult = fetch_query($db, $roleQuery);
    // Insert les joueurs dans la BDD
    $query = utf8_decode(sprintf($pattern, $player['tag'], $player['name'], $player['rank'], $player['trophies'],
        $roleResult['id'], $player['expLevel'], 1, $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'],
        $player['donationsDelta'], $player['donationsPercent']));

    execute_query($db, $query);
}
