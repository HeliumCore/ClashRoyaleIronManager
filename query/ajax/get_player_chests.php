<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 06/08/18
 * Time: 13:36
 */

include(__DIR__ . "/../../tools/api.php");

if (isset($_GET['tag']) && !empty($_GET['tag']))
    $playerTag = $_GET['tag'];
else {
    echo 'false';
    return;
}

$chests = getPlayerChestsFromNewApi($playerTag)['items'];
$counter = 1;
$needed = 3;
$chestsArray = array("Silver Chest" => "silver", "Golden Chest" => "gold", "Magical Chest" => "magical",
    "Giant Chest" => "giant", "Epic Chest" => "epic", "Super Magical Chest" => "superMagical", "Legendary Chest" => "legendary");

$needed = 3;
for ($i = 0; $i < $needed; $i++) {
    $isFatChest = $chests[$i]['name'] != 'Silver Chest' && $chests[$i]['name'] != 'Golden Chest';
    if ($isFatChest)
        $needed++;

    ?>
    <div class="col-xs-3">
        <div class="img-responsive">
            <img src="/images/chests/<?php print $chestsArray[$chests[$i]['name']]; ?>-chest.png"
                 alt="failed to load img"
                 class="img-responsive chests"/>
            <span class="chestNumber whiteShadow">+<?php print ($i + 1); ?></span>
        </div>
    </div>
    <?php

}

$chests = array_reverse($chests);
$fatChests = array();
for ($i = 0; $i < (8 - $needed); $i++) {
    array_push($fatChests, $chests[$i]);
}
$fatChests = array_reverse($fatChests);
foreach ($fatChests as $fatChest) {
    ?>
    <div class="col-xs-3">
        <div class="img-responsive">
            <img src="/images/chests/<?php print $chestsArray[$fatChest['name']]; ?>-chest.png"
                 alt="failed to load img"
                 class="img-responsive chests"/>
            <span class="chestNumber whiteShadow">+<?php print $fatChest['index']; ?></span>
        </div>
    </div>
    <?php
}