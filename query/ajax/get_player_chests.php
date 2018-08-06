<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 06/08/18
 * Time: 13:36
 */

include(__DIR__ . "/../../tools/api_conf.php");

if (isset($_GET['tag']) && !empty($_GET['tag']))
    $playerTag = $_GET['tag'];
else {
    echo 'false';
    return;
}

// CHESTS
$chests = getPlayerChestsFromApi($api, $playerTag);
$upcomingChests[] = $chests["upcoming"];
$fatChests = array(
    $chests["superMagical"] => "superMagical", $chests["magical"] => "magical", $chests["legendary"] => "legendary",
    $chests["epic"] => "epic", $chests["giant"] => "giant"
);
ksort($fatChests);

$counter = 1;
$needed = 3;
foreach ($upcomingChests[0] as $nextChest):
    $isFatChest = $nextChest != 'silver' && $nextChest != 'gold';

    if ($isFatChest)
        $needed++;

    if ($counter <= $needed) { ?>
        <div class="col-xs-3">
            <div class="img-responsive">
                <img src="/images/chests/<?php print $nextChest; ?>-chest.png" alt="failed to load img"
                     class="img-responsive chests"/>
                <span class="chestNumber whiteShadow">+<?php print $counter; ?></span>
            </div>
        </div>
        <?php
    }
    $counter++;
endforeach;
foreach ($fatChests as $key => $chest) {
    if ($key > 3) { ?>
        <div class="col-xs-3">
            <div class="img-responsive">
                <img src="/images/chests/<?php print $chest; ?>-chest.png" alt="failed to load img"
                     class="img-responsive chests"/>
                <span class="chestNumber whiteShadow">+<?php print $chests[$chest]; ?></span>
            </div>
        </div>
        <?php
    }
}
?>
