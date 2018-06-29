<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 28/06/2018
 * Time: 16:08
 */

include("tools/database.php");
include("tools/api_conf.php");


if (isset($_GET['tag']) && !empty($_GET['tag'])) $getPlayerTag = $_GET['tag'];
else header('Location: index.php');

$getPlayerByTag = "
SELECT players.tag, players.name as playerName, players.rank, players.trophies, role.name as playerRole, players.exp_level as level,
players.donations_delta as delta, players.donations_ratio as ratio, arena.arena as arena, players.donations, players.donations_received as received,
arena.trophy_limit, arena.arena_id, player_war.battle_played, player_war.battle_won, player_war.collection_played, player_war.collection_won, player_war.cards_earned,
SUM(player_war.cards_earned) as total_cards_earned,
SUM(player_war.collection_played) as total_collection_played, 
SUM(player_war.collection_won) as total_collection_won,
SUM(player_war.battle_played) as total_battle_played,
SUM(player_war.battle_won) as total_battle_won
FROM players
INNER JOIN arena ON arena.arena_id = players.arena
INNER JOIN role ON role.id = players.role_id
INNER JOIN player_war ON player_war.player_id = players.id
WHERE tag = \"%s\"
";

$getPlayer = fetch_query($db, utf8_decode(sprintf($getPlayerByTag, $getPlayerTag)));

$url = utf8_decode(sprintf("https://api.royaleapi.com/player/%s/chests", $getPlayerTag));
$apiResult = file_get_contents($url, true, $context);
$chests = json_decode($apiResult, true);

$upcomingChests[] = $chests["upcoming"];
$superMagical = $chests["superMagical"];
$magical = $chests["magical"];
$legendary = $chests["legendary"];
$epic = $chests["epic"];
$giant = $chests["giant"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Les membres</title>
    <link rel="stylesheet" type="text/css" href="css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1 class="pageTitle">Coffres à venir</h1><br>
    <div class="chestDiv">
        <?php
        $counter = 0;

        foreach ($upcomingChests[0] as $chest) {
            echo '<img src="res/'.$chest.'_chest.png" alt="'.$chest.' chest" class="imgChest">';
            $counter++;
            echo '<label class="labelChest">'. $counter .'</label>';
        }
        ?>
    </div>
    <br>
    <h1 class="pageTitle">Détails du joueur</h1>
    <br><br>
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
                echo '<th class="headIndex">' . $getPlayer['rank'] . '</th>';
                echo '<td class="lineIndex">' . $getPlayer['tag'] . '</td>';
                echo '<td class="lineIndex">' . utf8_encode($getPlayer['playerName']) . '</td>';
                echo '<td class="lineIndex">' . utf8_encode($getPlayer['playerRole']) . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['level'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['trophies'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['arena'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['donations'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['received'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['delta'] . '</td>';
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
                echo '<td class="lineIndex">' . $getPlayer['arena'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['trophies'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['trophy_limit'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['arena_id'] . '</td>';
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
                echo '<td class="lineIndex">' . $getPlayer['battle_played'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['battle_won'] . '</td>';
                if ($getPlayer['battle_played'] != 0) echo "<td class=\"lineIndex\">" . round((($getPlayer['battle_won'] / $getPlayer['battle_played']) * 100)) . "</td>";
                else echo "<td class=\"lineIndex\">0</td>";
                echo '<td class="lineIndex">' . $getPlayer['total_battle_played'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['total_battle_won'] . '</td>';
                if ($getPlayer['total_battle_played'] != 0) echo "<td class=\"lineIndex\">" . round((($getPlayer['total_battle_won'] / $getPlayer['total_battle_played']) * 100)) . "</td>";
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
                echo '<td class="lineIndex">' . $getPlayer['collection_played'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['collection_won'] . '</td>';
                if ($getPlayer['collection_won'] != 0) echo "<td class=\"lineIndex\">" . round((($getPlayer['collection_played'] / $getPlayer['collection_won']) * 100)) . "</td>";
                else echo "<td class=\"lineIndex\">0</td>";
                echo '<td class="lineIndex">' . $getPlayer['total_collection_played'] . '</td>';
                echo '<td class="lineIndex">' . $getPlayer['total_collection_won'] . '</td>';
                if ($getPlayer['total_collection_played'] != 0) echo "<td class=\"lineIndex\">" . round((($getPlayer['total_collection_won'] / $getPlayer['total_collection_played']) * 100)) . "</td>";
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
