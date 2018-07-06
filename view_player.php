<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 28/06/2018
 * Time: 16:08
 */

include("tools/database.php");
include("tools/api_conf.php");

if (isset($_GET['tag']) && !empty($_GET['tag'])) $playerTag = $_GET['tag'];
else header('Location: index.php');

$player = getPlayersInfoByTag($db, $playerTag);
$totalWarPlayed = getTotalWarPlayedByPlayerId($db, $player['playerId']);

// DECK
$apiDeck = getPlayerCurrentDeckFromApi($api, $playerTag);

$currentDeck = getCardsInCurrentDeck($db, $player['playerId']);
$getCrResult = getCrIdsByCards($db, $currentDeck['card_1'], $currentDeck['card_2'], $currentDeck['card_3'],
    $currentDeck['card_4'], $currentDeck['card_5'], $currentDeck['card_6'], $currentDeck['card_7'],
    $currentDeck['card_8']);
$deckLinkPattern = "https://link.clashroyale.com/deck/fr?deck=%d;%d;%d;%d;%d;%d;%d;%d";
$deckLink = sprintf($deckLinkPattern, $getCrResult[0]['cr_id'], $getCrResult[1]['cr_id'], $getCrResult[2]['cr_id'],
    $getCrResult[3]['cr_id'], $getCrResult[4]['cr_id'], $getCrResult[5]['cr_id'], $getCrResult[6]['cr_id'],
    $getCrResult[7]['cr_id'], $getCrResult[8]['cr_id']);

// CHESTS
$chests = getPlayerChestsFromApi($api, $playerTag);

$upcomingChests[] = $chests["upcoming"];
$fatChests = array(
    $chests["superMagical"] => "superMagical", $chests["magical"] => "magical", $chests["legendary"] => "legendary",
    $chests["epic"] => "epic", $chests["giant"] => "giant"
);
ksort($fatChests);

// Absences
$missedCollections = countMissedCollection($db, $player['playerId'])['missed_collection'];
$missedWars = countMissedWar($db, $player['playerId'])['missed_war'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Détail du joueur</title>
    <?php include("head.php"); ?>
    <script>
        function update() {
            $.ajax({
                url: 'query/update_clan.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    $.ajax({
                        url: 'query/update_player.php?tag=' + $('input:hidden[name=playerTagHidden]').val(),
                        success: function () {
                            window.location.reload(true);
                        }
                    });
                }
            })
        }
        $(document).ready(function () {
            $('#deckLinkDiv').click(function () {
                $.ajax({
                    url: 'query/update_clan.php',
                    beforeSend: function () {
                        $('#loaderDiv').show();
                    },
                    success: function () {
                        $.ajax({
                            url: 'query/update_player.php?tag=' + $('input:hidden[name=playerTagHidden]').val(),
                            success: function () {
                                window.location = $('#hd_deckLink').data('link');
                            }
                        });
                    }
                });
            });
        });
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Détails du joueur</h1><br>
    <h2 class="whiteShadow">Deck du moment</h2>
    <br>
    <div class="row">
        <div class="col-md-5">
            <div class="row">
                <?php
                foreach ($apiDeck as $card): ?>
                    <div class="col-xs-3">
                        <div class="img-responsive">
                            <img src="<?php print $card['icon']; ?>" alt="failed to load img" class="img-responsive cards"/>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div id="deckLinkDiv" class="text-center pointerHand">
                    <input type="hidden" id="hd_deckLink" data-link="<?php print $deckLink ?>"/>
                    <input class="deckLink" id="input_deckLink" type="image" src="images/ui/copy-deck.png" height="50px"
                           alt="Copier le lien"/>
                    <span id="spanDeckLink" class="whiteShadow text-center">Copier le deck</span>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-md-offset-2">
            <div class="row">
                <?php
                $counter = 1;
                global $needed;
                $needed = 3;
                foreach ($upcomingChests[0] as $nextChest):
                    $isFatChest = !($nextChest == 'silver' || $nextChest == 'gold');
                    if ($isFatChest)
                        $needed++;
                if ($counter <= $needed) { ?>
                        <div class="col-xs-3">
                            <div class="img-responsive">
                                <img src="images/chests/<?php print $nextChest; ?>-chest.png" alt="failed to load img"
                                     class="img-responsive little-chest chests"/>
                                <span class="chestNumber whiteShadow">+<?php print $counter ;?></span>
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
                                <img src="images/chests/<?php print $chest; ?>-chest.png" alt="failed to load img"
                                     class="img-responsive big-chest chests"/>
                                <span class="chestNumber whiteShadow">+<?php print $chests[$chest];?></span>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <br> <br><br>
    <div class="divInfoPlayer table-responsive">
        <table class="table">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Rang</th>
                <th class="headIndex text-center table-border" colspan="3">Joueur</th>
                <th class="headIndex text-center table-border" colspan="2">Trophées</th>
                <th class="headIndex text-center">Arène</th>
                <th class="headIndex text-center table-border-left" colspan="2">Dons</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="whiteShadow">Joueur</h2>
                <?php
                echo '<tr>';
                echo '<input id="playerTagHidden" type="hidden" name="playerTagHidden" value="' . $player['tag'] . '" />';
                echo '<td class="whiteShadow">' . $player['rank'] . '</td>';
                echo '<td id="playerTag" class="whiteShadow text-center table-border-left">' . $player['tag'] . '</td>';
                echo '<td class="whiteShadow text-center">' . utf8_encode($player['playerName']) . '<br>' . utf8_encode($player['playerRole']) . '</td>';
                echo '<td class="whiteShadow text-center">Niveau<br>' . $player['level'] . '</td>';
                echo '<td class="whiteShadow text-center table-border-left">Courant<br>' . $player['trophies'] . '</td>';
                echo '<td class="whiteShadow text-center">Max<br>' . $player['max_trophies'] . '</td>';
                echo '<td class="whiteShadow text-center table-border">' . $player['arena'] . '</td>';
                echo '<td class="whiteShadow text-center">Données<br>' . $player['donations'] . '</td>';
                echo '<td class="whiteShadow text-center">Reçues<br>' . $player['received'] . '</td>';
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <div class="divInfoPlayer table-responsive">
        <table class="table">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Guerres</th>
                <th class="headIndex text-center table-border" colspan="3">Collections</th>
                <th class="headIndex text-center" colspan="2">Batailles</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="whiteShadow">Guerres</h2>
                <?php
                echo '<tr>';
                echo '<td class="whiteShadow">Jouées<br>' . $totalWarPlayed['total_war_played'] . '</td>';
                echo '<td class="whiteShadow text-center table-border-left">Jouées<br>' . $player['total_collection_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['total_collection_won'] . '</td>';
                echo '<td class="whiteShadow text-center"><img src="images/ui/deck.png" height="35px"/>&nbsp;' . $player['total_cards_earned'] . '</td>';
                echo '<td class="whiteShadow text-center table-border-left">Jouées<br>' . $player['total_battle_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['total_battle_won'] . '</td>';
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <div class="divInfoPlayer table-responsive">
        <table class="table">
            <thead>
            <tbody>
            <div>
                <h2 class="whiteShadow">Absences</h2>
                <?php

                echo '<tr>';
                echo '<td class="whiteShadow text-center">Collections<br>' . $missedCollections . '</td>';
                echo '<td class="whiteShadow text-center">Batailles<br>' . $missedWars . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . ($missedCollections + $missedWars) . '</td>';
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>
