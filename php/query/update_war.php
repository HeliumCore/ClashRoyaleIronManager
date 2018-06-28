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

$getWarPattern = "
SELECT id
FROM war
WHERE war.timestamp = %d
";

$setWarPattern = "
INSERT INTO war
VALUES ('', %d)
";

$insertPlayerWar = "
INSERT INTO player_war
VALUES ('', %d, %d, %d, %d, %d)
";

$updatePlayerWar = "
UPDATE player_war
SET (cards_earned, collection_played, collection_won)
WHERE player_id = %d
";

if ($data['state'] == "collectionDay") {
    $getWarQuery = sprintf($getWarPattern, $data['collectionEndTime']);
    $transaction = $db->prepare($getWarQuery);
    $transaction->execute();
    $getWarResult = $transaction->fetch();

    // On récupère l'ID de la guerre en cours
    if (is_array($getWarResult)) {
        $warId = $getWarResult['id'];
    } else {
        $setWarQuery = sprintf($setWarPattern, $data['collectionEndTime']);
        $transaction = $db->prepare($setWarQuery);
        $transaction->execute();
        $warId = $db->lastInsertId();
    }

    //
    foreach ($data['participants'] as $player) {
        $player['battlesPlayed'];
        $player['wins'];
        $player['cardsEarned'];
    }
} else if ($data['state'] == "warDay") {
    // meme pas sur d'en avoir besoin
}