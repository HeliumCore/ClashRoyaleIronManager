<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 27/06/2018
 * Time: 20:01
 */

include("../tools/api_conf.php");
include("../tools/database.php");

$apiResult = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC", true, $context);
$data = json_decode($apiResult, true);

// Récupère tous les tags
$getAllQuery = "
SELECT id, tag
FROM players
WHERE in_clan > 0
";
$resultTags = fetch_all_query($db, $getAllQuery);

$getPattern = "
SELECT players.tag
FROM players
WHERE players.in_clan = 1
AND players.tag = \"%s\"
";

$updatePattern = "
UPDATE players
SET players.name = \"%s\",
players.rank = %d,
players.trophies = %d,
players.role_id = %d,
players.exp_level = %d,
players.arena = %d,
players.donations = %d,
players.donations_received = %d,
players.donations_delta = %d,
players.donations_ratio= %f
WHERE players.tag = \"%s\"
";

$insertPattern = "
INSERT INTO players (players.name, tag, rank, trophies, role_id, exp_level, in_clan, arena, donations, donations_received, donations_delta, donations_ratio)
VALUES (\"%s\", \"%s\", %d, %d, %d, %d, %d, %d, %d, %d, %d, %f)
";

foreach ($data["members"] as $player) {
    $result = fetch_query($db, sprintf($getPattern, $player['tag']));
    if (is_array($result)) {
        // On récupère le role_id
        $roleId = getRoleId($db, $player['role']);
        // Il y a un retour, on update
        $query = utf8_decode(sprintf(
            $updatePattern, $player['name'], $player['rank'], $player['trophies'], $roleId, $player['expLevel'],
            $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'],
            $player['donationsDelta'], $player['donationsPercent'], $player['tag']
        ));
    } else {
        // On récupère le role_id
        $roleId = getRoleId($db, $player['role']);
        // Il n'y a pas de retour, on insert
        $query = utf8_decode(sprintf(
            $insertPattern, $player['name'], $player['tag'], $player['rank'], $player['trophies'], $roleId,
            $player['expLevel'], 1, $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'],
            $player['donationsDelta'], $player['donationsPercent']
        ));
    }
    execute_query($db, $query);
}

function removeFromClan($db, $allTags)
{
    $query = utf8_decode("UPDATE players SET in_clan = 0 WHERE tag IN " . $allTags);
    execute_query($db, $query);
}

function getRoleId($db, $machineName)
{
    $query = "SELECT id FROM role WHERE machine_name LIKE \"%s\"";
    $result = fetch_query($db, utf8_decode(sprintf($query, $machineName)));
    return $result['id'];
}