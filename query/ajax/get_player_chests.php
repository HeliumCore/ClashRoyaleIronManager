<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 06/08/18
 * Time: 13:36
 */

if (isset($_GET['tag']) && !empty($_GET['tag']))
    $playerTag = $_GET['tag'];
else {
    echo 'false';
    return;
}

require(__DIR__ . "/../../tools/api.class.php");
require(__DIR__ . "/../../models/player.class.php");
ClashRoyaleApi::create();
$player = new Player($playerTag);
$chests = $player->getNextChests();

if ($chests == false)
    return;

$counter = 1;
$needed = 3;
$chestsArray = array("Silver Chest" => "silver", "Golden Chest" => "gold", "Magical Chest" => "magical",
    "Giant Chest" => "giant", "Epic Chest" => "epic", "Super Magical Chest" => "superMagical", "Legendary Chest" => "legendary");

$counter = 0;
$needed = 3;

foreach ($chests as $chest) {
    $isFatChest = $chest['name'] != 'Silver Chest' && $chest['name'] != 'Golden Chest';
    if ($isFatChest)
        $needed++;

    if ($counter < $needed) {
        ?>
        <div class="col-xs-3">
            <div class="img-responsive">
                <img src="/images/chests/<?php print $chestsArray[$chest['name']]; ?>-chest.png"
                     alt="failed to load img"
                     class="img-responsive chests"/>
                <span class="chestNumber whiteShadow">+<?php print ($chest['index'] + 1); ?></span>
            </div>
        </div>
        <?php
        $counter++;
    }
}