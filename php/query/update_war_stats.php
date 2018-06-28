<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 27/06/2018
 * Time: 23:07
 */

include("update_clan.php");

$apiResult = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/warlog", true, $context);
$data = json_decode($apiResult, true);

$updatePattern = "
UPDATE player_war
SET cards_earned = %d, battle_played = %d, battle_won = %d
WHERE player_id = %d
AND war_id = %d
";

$insertPattern = "
INSERT INTO player_war (cards_earned, battle_played, battle_won, player_id, war_id)
VALUE (%d, %d, %d, %d, %d)
";

$getIdPattern = "
SELECT players.id
FROM players
WHERE players.tag = \"%s\"
";

$getPattern = "
SELECT cards_earned, collection_played, collection_won, battle_played, battle_won
FROM player_war
WHERE player_id = %d
AND war_id = %d
";

$insertWarPattern = "
INSERT INTO war
VALUES ('', %d, %d)
";

$getWarPattern = "
SELECT id
FROM war
WHERE created = %d
";

foreach ($data as $war) {
    // On crée ou update la guerre (table: war)
    $created = $war['createdDate'];
    $getWarResult = fetch_query($db, sprintf($getWarPattern, $created));

    // Si la guerre n'existe pas déjà, on la crée
    // Sinon, on récupère son ID
    if (!is_array($getWarResult)) {
        execute_query($db, sprintf($insertWarPattern, $created, 1));
        $warId = $db->lastInsertId();
    } else {
        $warId = $getWarResult['id'];
    }

    foreach ($war['participants'] as $player) {
        $playerIdResult = fetch_query($db, utf8_decode(sprintf($getIdPattern, $player['tag'])));
        $playerId = $playerIdResult['id'];

        $playerWarResult = fetch_query($db, utf8_decode(sprintf($getPattern, $playerId, $warId)));


        if (is_array($playerWarResult)) {
            execute_query($db, sprintf(
                $updatePattern, $player['cardsEarned'], $player['battlesPlayed'], $player['wins'], $playerId, $warId
            ));
        } else {
            execute_query($db, sprintf(
                $insertPattern, $player['cardsEarned'], $player['battlesPlayed'], $player['wins'], $playerId, $warId
            ));
        }
    }
}
