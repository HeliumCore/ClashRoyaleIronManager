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
VALUES ('', 0, 0)
";

$insertPlayerWarPattern = "
INSERT INTO player_war (cards_earned, collection_played, collection_won, player_id, war_id)
VALUES (%d, %d, %d, %d, %d)
";

$updatePlayerWarPattern = "
UPDATE player_war
SET cards_earned = %d, collection_played = %d, collection_won = %d
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

$getAllPlayersQuery = "
SELECT players.id, players.tag
FROM players
WHERE in_clan > 0
";

$getAllExistingQuery = "
SELECT COUNT(player_war.id) as numberOfCurrentPlayers
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE war.past_war = 0
";

$getNotEligibleQuery = "
SELECT DISTINCT players.id 
FROM players 
WHERE players.id NOT IN
(
  SELECT DISTINCT pw.player_id 
  FROM player_war pw 
  JOIN war ON pw.war_id = war.id 
  WHERE war.past_war = 0
)
";

$numberOfCurrentPlayers = intval(fetch_query($db, $getAllExistingQuery)['numberOfCurrentPlayers']);
$allPlayersSize = sizeof(fetch_all_query($db, $getAllPlayersQuery));
if ($allPlayersSize > $numberOfCurrentPlayers) {
    $notEligible = fetch_all_query($db, $getNotEligibleQuery);
}
if (fetch_query($db, $getAllExistingQuery))
// On récupère l'ID de la guerre en cours
$getWarResult = fetch_query($db, sprintf($getWarQuery));
if (is_array($getWarResult)) {
    $warId = $getWarResult['id'];
} else {
    execute_query($db, $setWarQuery);
    $warId = $db->lastInsertId();
}

foreach (fetch_all_query($db, $getAllPlayersQuery) as $player) {
    foreach ($notEligible as $notEligiblePlayer) {
        if ($player['id'] == $notEligiblePlayer['id']) {
            continue 2;
        }
    }
    $getPlayerWarResult = fetch_query($db, sprintf($getPlayerWarPattern, $player['id'], $warId));

    global $battlesPlayed;
    global $wins;
    $cardsEarned = null;
    $battlesPlayed = null;
    $wins = null;
    foreach ($data['participants'] as $participant) {
        if ($player['tag'] == $participant['tag']) {
            if ($data['state'] == "collectionDay") {
                $cardsEarned = $participant['cardsEarned'];
            }
            $battlesPlayed = $participant['battlesPlayed'];
            $wins = $participant['wins'];
        }
    }
    if ($data['state'] == "collectionDay") {
        $cardsEarned = $cardsEarned != null ? $cardsEarned : 0;
    }
    $battlesPlayed = $battlesPlayed != null ? $battlesPlayed : 0;
    $wins = $wins != null ? $wins : 0;
    if (is_array($getPlayerWarResult)) {
        // Si le joueur a déjà été enregistré pour cette guerre, on update
        if ($data['state'] == "collectionDay") {
            execute_query($db, sprintf(
                $updatePlayerWarPattern, $cardsEarned, $battlesPlayed, $wins, $getPlayerWarResult['id']
            ));
        } else if ($data['state'] == "warDay") {
            execute_query($db, sprintf(
                $updateWarPattern, $battlesPlayed, $wins, $getPlayerWarResult['id']
            ));
        }

    } else {
        // Si le joueur n'est pas encore dans cette guerre, on insert
        if ($data['state'] == "collectionDay") {
            execute_query($db, sprintf(
                $insertPlayerWarPattern, $cardsEarned, $battlesPlayed, $wins, $player['id'], $warId
            ));
        } else if ($data['state'] == "warDay") {
            execute_query($db, sprintf(
                $insertWarPattern, $cardsEarned, $battlesPlayed, $wins, $player['id'], $warId
            ));
        }

    }
}