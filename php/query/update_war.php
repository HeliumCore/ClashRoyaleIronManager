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


if ($data['state'] == "collectionDay") {
    foreach ($data['participants'] as $player) {
        $player['battlesPlayed'];
        $player['wins'];
        $player['cardsEarned'];
    }
} else if ($data['state'] == "warDay") {
    // meme pas sur d'en avoir besoin
}