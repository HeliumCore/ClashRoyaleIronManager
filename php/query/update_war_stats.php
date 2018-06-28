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

$updateCurrentWarPattern = "
UPDATE war
SET created = %d,
past_war = 1
WHERE id = %d
";

$getCurrentWarQuery = "
SELECT id
FROM war
WHERE past_war = 0
LIMIT 1
";
$currentWarResult = fetch_query($db, $getCurrentWarQuery);

$getAllPlayersQuery = "
SELECT players.id, players.tag
FROM players
WHERE in_clan > 0
";
$allPlayers = fetch_all_query($db, $getAllPlayersQuery);
foreach ($data as $war) {
    if ($war['seasonNumber'] <= 5 || $war['createdDate'] == 1530223645) {
        continue;
    }

    // On crée ou update la guerre (table: war)
    $created = $war['createdDate'];
    $getWarResult = fetch_query($db, sprintf($getWarPattern, $created));

    // Si la guerre n'existe pas déjà, on la crée
    // Sinon, on récupère son ID
    $warId = getWarID($getWarResult, $currentWarResult, $insertWarPattern, $updateCurrentWarPattern, $db, $created);

    foreach ($allPlayers as $player) {
        global $cardsEarned;
        global $battlesPlayed;
        global $wins;
        $cardsEarned = null;
        $battlesPlayed = null;
        $wins = null;
        foreach ($war['participants'] as $participant) {
            if ($player['tag'] == $participant['tag']) {
                $cardsEarned = $participant['cardsEarned'];
                $battlesPlayed = $participant['battlesPlayed'];
                $wins = $participant['wins'];
            }
        }
        $cardsEarned = $cardsEarned != null ? $cardsEarned : 0;
        $battlesPlayed = $battlesPlayed != null ? $battlesPlayed : 0;
        $wins = $wins != null ? $wins : 0;
        $playerWarResult = fetch_query($db, utf8_decode(sprintf($getPattern, $player['id'], $warId)));

        if (is_array($playerWarResult)) {
            execute_query($db, sprintf(
                $updatePattern, $cardsEarned, $battlesPlayed, $wins, $player['id'], $warId
            ));
        } else {
            execute_query($db, sprintf(
                $insertPattern, $cardsEarned, $battlesPlayed, $wins, $player['id'], $warId
            ));
        }
        $playerWarResult = null;
    }
}
function getWarID($getWarResult, $currentWarResult, $insertWarPattern, $updateCurrentWarPattern, $db, $created) {
    if (!is_array($getWarResult)) {
        if (!is_array($currentWarResult)) {
            execute_query($db, sprintf($insertWarPattern, $created, 1));
            return $db->lastInsertId();
        } else {
            execute_query($db, sprintf($updateCurrentWarPattern, $created, $currentWarResult['id']));
            return getWarID($getWarResult, $currentWarResult, $insertWarPattern, $updateCurrentWarPattern, $db, $created);
        }
    } else {
        return $getWarResult['id'];
    }
}