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


// database :
//missed_collection
//missed_battle
//collection_played
//collection_won
//battle_played
//battle_won
unset($query);
unset($updatePattern);
unset($getPattern);

$updatePattern = "
UPDATE war_history
SET battle_played = %d,
battle_won = %d
WHERE player_id = %d
";

$getIdPattern = "
SELECT players.id
FROM players
WHERE players.tag = \"%s\"
";

$insertPattern = "
INSERT INTO war_history (player_id, battle_played, battle_won)
VALUE (%d, %d, %d)
";

$getPattern = "
SELECT missed_battle, battle_played, battle_won
FROM war_history
WHERE player_id = %d
";
// TODO manage collection
foreach ($data as $war) {
    //createdDate
    //battlesPlayed
    //participants?
    $created = $war['createdDate'];

    foreach ($war['participants'] as $player) {
        $getIdQuery = utf8_decode(sprintf($getIdPattern, $player['tag']));
        $transaction = $db->prepare($getIdQuery);
        $transaction->execute();
        $idResult = $transaction->fetch();

        $getQuery = utf8_decode(sprintf($getPattern, $idResult['id']));
        $transaction = $db->prepare($getQuery);
        $transaction->execute();
        $result = $transaction->fetch();

        if (is_array($result)) {
            $missedBattle = $result['missed_battle'];
            $battlePlayed = $result['battle_played'];
            $battleWon = $result['battle_won'];

            $battleWon += $player['wins'];
            $battlePlayed += $player['battlesPlayed'];

            $query = utf8_decode(sprintf($updatePattern, $battleWon, $battlePlayed, $idResult['id']));
            $transaction = $db->prepare($query);
        } else {
            $insertQuery = utf8_decode(
                sprintf($insertPattern, $idResult['id'], $player['battlesPlayed'], $player['wins'])
            );
            $transaction = $db->prepare($insertQuery);
        }
        $transaction->execute();
    }
}
