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
//                TODO afficher X coffres supplemetaires (les upcomings) si un des gros est dans cette liste
                foreach ($apiDeck as $card): ?>
                    <div class="col-xs-3">
                        <div class="img-responsive">
                            <img src="<?php print $card['icon']; ?>" alt="failed to load img" class="img-responsive cards"/>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="<?php print $deckLink ?>" class="deckLink "><img src="images/ui/copy-deck.png" height="50px"
                                                                          alt="Copier le lien"/></a>
                <span class="whiteShadow">Si le lien ne marche pas ou ne pointe pas sur le bon deck, actualiser les informations</span>
            </div>
        </div>
        <!-- TODO trouver un moyen d'afficher les positions (ex: +54)-->
        <div class="col-md-5 col-md-offset-2">
            <div class="row">
                <?php
                $counter = 1;
                foreach ($upcomingChests[0] as $nextChest):
                    if ($counter <= 3) { ?>
                        <div class="col-xs-3">
                            <div class="img-responsive">
                                <img src="images/chests/<?php print $nextChest; ?>-chest.png" alt="failed to load img"
                                     class="img-responsive little-chest chests"/>
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
    <div class="divInfoPlayer">
        <table class="table">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Rang</th>
                <th class="headIndex text-center" colspan="3">Joueur</th>
                <th class="headIndex text-center" colspan="2">Trophées</th>
                <th class="headIndex">Arène</th>
                <th class="headIndex text-center" colspan="2">Dons</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="whiteShadow">Joueur</h2>
                <?php
                echo '<tr>';
                echo '<input id="playerTagHidden" type="hidden" name="playerTagHidden" value="' . $player['tag'] . '" />';
                echo '<th class="whiteShadow">' . $player['rank'] . '</th>';
                echo '<td id="playerTag" class="whiteShadow">Tag<br>' . $player['tag'] . '</td>';
                echo '<td class="whiteShadow">Nom<br>' . utf8_encode($player['playerName']) . '<br>' . utf8_encode($player['playerRole']) . '</td>';
                echo '<td class="whiteShadow">Niveau<br>' . $player['level'] . '</td>';
                echo '<td class="whiteShadow">Courant<br>' . $player['trophies'] . '</td>';
                echo '<td class="whiteShadow">Max<br>' . $player['max_trophies'] . '</td>';
                echo '<td class="whiteShadow">' . $player['arena'] . '</td>';
                echo '<td class="whiteShadow">Données<br>' . $player['donations'] . '</td>';
                echo '<td class="whiteShadow">Reçues<br>' . $player['received'] . '</td>';
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <!--    TODO retrait temporaire du tableau arène, discuter son utilité-->
    <!--    <div class="divInfoPlayer">-->
    <!--        <table class="table">-->
    <!--            <thead>-->
    <!--            <tr class="rowIndex">-->
    <!--                <th class="headIndex">Nom arène</th>-->
    <!--                <th class="headIndex">Trophées actuels</th>-->
    <!--                <th class="headIndex">Trophées minimum de l'arène</th>-->
    <!--                <th class="headIndex">Numéro arène</th>-->
    <!--            </tr>-->
    <!--            </thead>-->
    <!--            <tbody>-->
    <!--            <div>-->
    <!--                <h2 class="whiteShadow">Arène</h2>-->
    <!--                --><?php
    //                echo '<tr>';
    //                echo '<td class="lineIndex">' . $player['arena'] . '</td>';
    //                echo '<td class="lineIndex">' . $player['trophies'] . '</td>';
    //                echo '<td class="lineIndex">' . $player['trophy_limit'] . '</td>';
    //                echo '<td class="lineIndex">' . $player['arena_id'] . '</td>';
    //                echo '</tr>';
    //                ?>
    <!--            </tbody>-->
    <!--        </table>-->
    <!--    </div>-->
    <!--    TODO Faire un tableau des absences? la tableau de la guerre est reporté plus bas-->
    <!--    <div class="divInfoPlayer">-->
    <!--        <table class="table">-->
    <!--            <thead>-->
    <!--            <tr class="rowIndex">-->
    <!--                <th class="headIndex">Guerres jouées</th>-->
    <!--                <th class="headIndex">Guerres gagnées</th>-->
    <!--                <th class="headIndex">Pourcentage de victoire</th>-->
    <!--                <th class="headIndex">Total de guerre</th>-->
    <!--                <th class="headIndex">Total de victoire</th>-->
    <!--                <th class="headIndex">Pourcentage global de victoire</th>-->
    <!--            </tr>-->
    <!--            </thead>-->
    <!--            <tbody>-->
    <!--            <div>-->
    <!--                <h2 class="whiteShadow">Guerre</h2>-->
    <!--                --><?php
    //                echo '<tr>';
    //                echo '<td class="lineIndex">' . $player['battle_played'] . '</td>';
    //                echo '<td class="lineIndex">' . $player['battle_won'] . '</td>';
    //                if ($player['battle_played'] != 0) echo "<td class=\"lineIndex\">" . round((($player['battle_won'] / $player['battle_played']) * 100)) . "</td>";
    //                else echo "<td class=\"lineIndex\">0</td>";
    //                echo '<td class="lineIndex">' . $player['total_battle_played'] . '</td>';
    //                echo '<td class="lineIndex">' . $player['total_battle_won'] . '</td>';
    //                if ($player['total_battle_played'] != 0) echo "<td class=\"lineIndex\">" . round((($player['total_battle_won'] / $player['total_battle_played']) * 100)) . "</td>";
    //                else echo "<td class=\"lineIndex\">0</td>";
    //                echo '</tr>';
    //                ?>
    <!--            </tbody>-->
    <!--        </table>-->
    <!--    </div>-->
    <div class="divInfoPlayer">
        <table class="table">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Guerres</th>
                <th class="headIndex text-center" colspan="3">Collections</th>
                <th class="headIndex text-center" colspan="2">Batailles</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="whiteShadow">Guerres</h2>
                <?php
                echo '<tr>';
                echo '<td class="whiteShadow">Jouées<br>' . $totalWarPlayed['total_war_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Jouées<br>' . $player['total_collection_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['total_collection_won'] . '</td>';
                echo '<td class="whiteShadow text-center"><img src="images/ui/deck.png" height="35px"/>&nbsp;' . $player['total_cards_earned'] . '</td>';
                echo '<td class="whiteShadow text-center">Jouées<br>' . $player['total_battle_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['total_battle_won'] . '</td>';
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
