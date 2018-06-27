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
WHERE in_clan = 1
";
$transaction = $db->prepare($getAllQuery);
$transaction->execute();
$resultTags = $transaction->fetchAll();
foreach ($resultTags as $tag) {
//    var_dump($tag);
}

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
players.expLevel = %d,
players.arena = %d,
players.donations = %d,
players.donations_received = %d,
players.donations_delta = %d,
players.donations_ratio= %f
WHERE players.tag = \"%s\"
";

$insertPattern = "
INSERT INTO players (name, rank, trophies, role_id, exp_level, in_clan, arena, donations, donations_received, donations_delta, donations_ratio)
VALUES (\"%s\", %d, %d, %d, %d, 1, %d, %d, %d, %d, %f)
";

foreach ($data["members"] as $player) {
    $query = sprintf($getPattern, $player['tag']);
    $transaction = $db->prepare($query);
    $transaction->execute();
    $result = $transaction->fetch();

    if (is_array($result)) {
        // On récupère le role_id
        $roleId = getRoleId($db, $player['role']);
        // Il y a un retour, on update
        unset($query);
        $query = utf8_decode(sprintf(
            $updatePattern, $player['name'], $player['rank'], $player['trophies'], $roleId, $player['expLevel'],
            $player['arena']['arenaID'], $player['donations'], $player['donationsReceived'],
            $player['donationsDelta'], $player['donationsPercent'], $player['tag']
        ));
//        array_diff($allTags, $player['tag']);
    } else {
        // Il n'y a pas de retour, on insert
        unset($query);
        $query = utf8_decode(sprintf(
            $insertPattern, $player['name'], $player['rank'], $player['trophies'], $player['role_id'],
            $player['expLevel'], $player['donations'], $player['donationsReceived'], $player['donationsDelta'],
            $player['donationsPercent']
        ));
    }
    $transaction = $db->prepare($query);
    $transaction->execute();
    // Supprime les gens qui ne sont plus dans le clan
//    removeFromClan($db, $allTags);
}

function removeFromClan($db, $allTags)
{
    $query = "UPDATE players SET in_clan = 0 WHERE tag IN " . $allTags;
    $transaction = $db->prepare(utf8_decode($query));
    $transaction->execute();
}

function getRoleId($db, $machineName)
{
    $query = "SELECT id FROM role WHERE machine_name LIKE \"%s\"";
    $transaction = $db->prepare(utf8_decode(sprintf($query, $machineName)));
    $transaction->execute();
    $result = $transaction->fetch();
    return $result['id'];
}