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
    <title>Les membres</title>
    <link rel="stylesheet" type="text/css" href="css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        function update() {
            $.ajax({
                url: '../query/update_clan.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    $.ajax({
                        url: '../query/update_player.php?tag=' + $('#playerTag').html(),
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
<div class="bodyIndex">
    <h1 class="pageTitle">Détails du joueur</h1><br>
    <div class="chestDiv">
        <?php
        $counter = 1;

        foreach ($upcomingChests[0] as $nextChest) {
            echo '<img src="res/' . $nextChest . '_chest.png" alt="' . $nextChest . ' chest" class="imgChest">';
            echo '<label class="labelChest">' . $counter . '</label>';
            $counter++;
        }

        foreach ($fatChests as $key => $chest) {
            if ($key > 10) {
                echo '<img src="res/' . $chest . '_chest.png" alt="' . $chest . ' chest" class="imgChest">';
                echo '<label class="labelChest">+' . $key . '</label>';
            }
        }
        ?>
    </div>
    <h2 class="pageSecondTitle">Deck du moment</h2>
    <br>
    <div class="divInfoPlayer">
        <?php
        foreach ($apiDeck as $card) {
            echo '<img src="' . $card['icon'] . '" alt="failed to load img" class="cardClass"/>';
        }
        echo '<br>';
        echo '<a href="' . $deckLink . '" class="deckLink">Copier le deck</a>';
        echo 'Si le lien ne marche pas ou ne pointe pas sur le bon deck, actualiser les informations';
        ?>
    </div>
    <br> <br><br>
    <div class="divInfoPlayer">
        <table class="tableIndex">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Rang</th>
                <th class="headIndex">Tag</th>
                <th class="headIndex">Nom</th>
                <th class="headIndex">Role</th>
                <th class="headIndex">Niveau joueur</th>
                <th class="headIndex">Trophée</th>
                <th class="headIndex">Trophée Max</th>
                <th class="headIndex">Arène</th>
                <th class="headIndex">Donations</th>
                <th class="headIndex">Donations reçues</th>
                <th class="headIndex">Delta don/reception</th>
                <th class="headIndex">Statut</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="pageSecondTitle">Joueur</h2>
                <?php
                echo '<tr>';
                echo '<th class="headIndex">' . $player['rank'] . '</th>';
                echo '<td id="playerTag" class="lineIndex">' . $player['tag'] . '</td>';
                echo '<td class="lineIndex">' . utf8_encode($player['playerName']) . '</td>';
                echo '<td class="lineIndex">' . utf8_encode($player['playerRole']) . '</td>';
                echo '<td class="lineIndex">' . $player['level'] . '</td>';
                echo '<td class="lineIndex">' . $player['trophies'] . '</td>';
                echo '<td class="lineIndex">' . $player['max_trophies'] . '</td>';
                echo '<td class="lineIndex">' . $player['arena'] . '</td>';
                echo '<td class="lineIndex">' . $player['donations'] . '</td>';
                echo '<td class="lineIndex">' . $player['received'] . '</td>';
                echo '<td class="lineIndex">' . $player['delta'] . '</td>';
                echo "<td bgcolor='#66B266'>Good</td>";
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <div class="divInfoPlayer">
        <table class="tableIndex">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Nom arène</th>
                <th class="headIndex">Trophées actuels</th>
                <th class="headIndex">Trophées minimum de l'arène</th>
                <th class="headIndex">Numéro arène</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="pageSecondTitle">Arène</h2>
                <?php
                echo '<tr>';
                echo '<td class="lineIndex">' . $player['arena'] . '</td>';
                echo '<td class="lineIndex">' . $player['trophies'] . '</td>';
                echo '<td class="lineIndex">' . $player['trophy_limit'] . '</td>';
                echo '<td class="lineIndex">' . $player['arena_id'] . '</td>';
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <div class="divInfoPlayer">
        <table class="tableIndex">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Guerres jouées</th>
                <th class="headIndex">Guerres gagnées</th>
                <th class="headIndex">Pourcentage de victoire</th>
                <th class="headIndex">Total de guerre</th>
                <th class="headIndex">Total de victoire</th>
                <th class="headIndex">Pourcentage global de victoire</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="pageSecondTitle">Guerre</h2>
                <?php
                echo '<tr>';
                echo '<td class="lineIndex">' . $player['battle_played'] . '</td>';
                echo '<td class="lineIndex">' . $player['battle_won'] . '</td>';
                if ($player['battle_played'] != 0) echo "<td class=\"lineIndex\">" . round((($player['battle_won'] / $player['battle_played']) * 100)) . "</td>";
                else echo "<td class=\"lineIndex\">0</td>";
                echo '<td class="lineIndex">' . $player['total_battle_played'] . '</td>';
                echo '<td class="lineIndex">' . $player['total_battle_won'] . '</td>';
                if ($player['total_battle_played'] != 0) echo "<td class=\"lineIndex\">" . round((($player['total_battle_won'] / $player['total_battle_played']) * 100)) . "</td>";
                else echo "<td class=\"lineIndex\">0</td>";
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    <div class="divInfoPlayer">
        <table class="tableIndex">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Collections jouées</th>
                <th class="headIndex">Collections gagnées</th>
                <th class="headIndex">Pourcentage de victoire</th>
                <th class="headIndex">Cartes gagnées</th>
                <th class="headIndex">Cartes gagnées totales</th>
                <th class="headIndex">Pourcentage global de victoires</th>
            </tr>
            </thead>
            <tbody>
            <div>
                <h2 class="pageSecondTitle">Clan</h2>
                <?php
                echo '<tr>';
                echo '<td class="lineIndex">' . $player['collection_played'] . '</td>';
                echo '<td class="lineIndex">' . $player['collection_won'] . '</td>';
                if ($player['collection_won'] != 0) echo "<td class=\"lineIndex\">" . round((($player['collection_played'] / $player['collection_won']) * 100)) . "</td>";
                else echo "<td class=\"lineIndex\">0</td>";
                echo '<td class="lineIndex">' . $player['total_collection_played'] . '</td>';
                echo '<td class="lineIndex">' . $player['total_collection_won'] . '</td>';
                if ($player['total_collection_played'] != 0) echo "<td class=\"lineIndex\">" . round((($player['total_collection_won'] / $player['total_collection_played']) * 100)) . "</td>";
                else echo "<td class=\"lineIndex\">0</td>";
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
