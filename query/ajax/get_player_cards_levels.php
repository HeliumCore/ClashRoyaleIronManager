<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 24/08/2018
 * Time: 23:02
 */

include(__DIR__ . "/../../tools/database.php");

if (isset($_GET['playerId']) && !empty($_GET['playerId']))
    $playerId = $_GET['playerId'];
else {
    echo 'false';
    return;
}

$cardsLevels = getCardsLevelsByPlayerId($db, $playerId);
if (sizeof($cardsLevels) == 8) {
    echo json_encode($cardsLevels);
    return;
}

echo 'false';
return;