<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/06/18
 * Time: 10:08
 */

include("update_clan.php");

$apiResult = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/war", true, $context);
$data = json_decode($apiResult, true);

$getWarQuery = "
SELECT id
FROM war
WHERE past_war = 0
";

$setWarQuery = "
INSERT INTO war
VALUES ('', null, 0)
";

$insertPlayerWarPattern = "
INSERT INTO player_war (collection_played, collection_won, player_id, war_id)
VALUES (%d, %d, %d, %d)
";

$updatePlayerWarPattern = "
UPDATE player_war
SET collection_played = %d, collection_won = %d
WHERE id = %d
";

$insertWarPattern = "
INSERT INTO player_war (battle_played, battle_won, player_id, war_id)
VALUES (%d, %d, %d, %d)
";

$updateWarPattern = "
UPDATE player_war
SET battle_played = %d, battle_won = %d
WHERE id = %d
";

$getPlayerWarPattern = "
SELECT id
FROM player_war
WHERE player_id = %d
AND war_id = %d
";

$getIdPattern = "
SELECT players.id
FROM players
WHERE players.tag = \"%s\"
";

// On récupère l'ID de la guerre en cours
$getWarResult = fetch_query($db, sprintf($getWarQuery));
if (is_array($getWarResult)) {
    $warId = $getWarResult['id'];
} else {
    execute_query($db, $setWarQuery);
    $warId = $db->lastInsertId();
}

foreach ($data['participants'] as $player) {
    $getIdResult = fetch_query($db, utf8_decode(sprintf($getIdPattern, $player['tag'])));
    $playerId = $getIdResult ['id'];

    $getPlayerWarResult = fetch_query($db, sprintf($getPlayerWarPattern, $playerId, $warId));

    if (is_array($getPlayerWarResult)) {
        // Si le joueur a déjà été enregistré pour cette guerre, on update
        if ($data['state'] == "collectionDay") {
            $pattern = $updatePlayerWarPattern;
        } else if ($data['state'] == "warDay") {
            $pattern = $updateWarPattern;
        }
        execute_query($db, sprintf(
            $pattern, $player['battlesPlayed'], $player['wins'], $getPlayerWarResult['id']
        ));
    } else {
        // Si le joueur n'est pas encore dans cette guerre, on insert
        if ($data['state'] == "collectionDay") {
            $pattern = $insertPlayerWarPattern;
        } else if ($data['state'] == "warDay") {
            $pattern = $insertWarPattern;
        }
        execute_query($db, sprintf(
            $pattern, $player['battlesPlayed'], $player['wins'], $playerId, $warId
        ));
    }
}