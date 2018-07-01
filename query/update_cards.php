<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 15:49
 */

include("../tools/api_conf.php");
include("../tools/database.php");

$apiResult = file_get_contents("https://api.royaleapi.com/constants", true, $context);
$data = json_decode($apiResult, true);

$insertPattern = "
INSERT INTO cards (card_key, cards.name, elixir, cards.type, rarity, arena, cr_id)
VALUES(\"%s\", \"%s\", %d, \"%s\", \"%s\", %d, %d)
";

$updatePattern = "
UPDATE cards
SET cards.name = \"%s\",
elixir = %d,
cards.type = \"%s\",
rarity = \"%s\",
arena = %d,
cr_id = %d
WHERE cards.card_key = \"%s\" 
";

$getPattern = "
SELECT id
FROM cards
WHERE cards.card_key = \"%s\"
";

foreach ($data['cards'] as $card) {
    $getResult = fetch_query($db, utf8_decode(sprintf($getPattern, $card['key'])));

    if (is_array($getResult)) {
        $query = utf8_decode(sprintf($updatePattern, $card['name'], $card['elixir'], $card['type'], $card['rarity'],
            $card['arena'], $card['key'], $card['id']));
    } else {
        $query = utf8_decode(sprintf($insertPattern, $card['key'], $card['name'], $card['elixir'], $card['type'],
            $card['rarity'], $card['arena'], $card['id']));
    }
    execute_query($db, $query);
}