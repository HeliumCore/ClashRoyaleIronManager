<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 15:49
 */

include("../tools/api_conf.php");
include("../tools/database.php");

$constants = getConstants($api);

foreach ($constants['cards'] as $card) {
    if (is_array(getCardByKey($db, $card['key']))) {
        updateCard($db, $card['key'], $card['name'], $card['elixir'], $card['type'], $card['rarity'], $card['arena'],
            $card['id']);
    } else {
        insertCard($db, $card['key'], $card['name'], $card['elixir'], $card['type'], $card['rarity'], $card['arena'],
            $card['id']);
    }
    execute_query($db, $query);
}