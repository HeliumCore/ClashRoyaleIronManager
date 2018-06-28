<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 10:56
 */

include("../tools/database.php");

$getQuery = "
SELECT war_history.missed_collection, war_history.missed_battle, war_history.collection_played,
war_history.collection_won, war_history.battle_played, war_history.battle_won, war_history.cards_earned, players.name,
players.id
FROM war_history
JOIN players ON war_history.player_id = players.id
AND players.in_clan > 0
";
$warHistory = fetch_all_query($db, $getQuery);

$getPattern = "
SELECT SUM(cards_earned) as total_cards_earned, 
SUM(collection_played) as total_collection_played, 
SUM(collection_won) as total_collection_won,
SUM(battle_played) as total_battle_played,
SUM(battle_won) as total_battle_won
FROM player_war
WHERE player_id = %d
";

$getIdPattern = "
SELECT players.id
FROM players
WHERE players.tag = \"%s\"
";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Historique des guerres</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        function updateWarHistory() {
            $.ajax({
                url: '../query/update_war_history.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    window.location = 'war_history.php';
                }
            })
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1>Historique des guerres</h1>
    <br>
    <button
            id="updateWarHistoryBtn"
            class="btn"
            onclick="updateWarHistory()"
    >
        Mettre à jour
    </button>
    <br><br>
    <table>
        <thead>
        <tr>
            <td>Nom du joueur</td>
            <td>Collections ratées</td>
            <td>Batailles ratées</td>
            <td>Collections jouées</td>
            <td>Collections gagnées</td>
            <td>Batailles jouées</td>
            <td>Batailles gagnées</td>
            <td>Cartes récoltées</td>
            <td>Statut</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($warHistory as $player) {
            $idResult = fetch_query($db, sprintf($getIdPattern, $player['tag']));
            $getResult = fetch_query($getPattern, $idResult['id']);

            $totalCollectionPlayed = $getResult['total_collection_played'] != null ? $getResult['total_collection_played'] : 0;
            $totalCollectionWon = $getResult['total_collection_won'] != null ? $getResult['total_collection_won'] : 0;
            $totalCardsEarned = $getResult['total_cards_earned'] != null ? $getResult['total_cards_earned'] : 0;
            $totalBattlesPlayed = $getResult['total_battle_played'] != null ? $getResult['total_battle_played'] : 0;
            $totalBattlesWon = $getResult['total_battle_won'] != null ? $getResult['total_battle_won'] : 0;

            $warning = $player['missed_collection'] >= 6
                || $player['missed_battle'] >= 2
                || ($player['missed_collection'] + $player['missed_battle'] >= 4);

            $ban = $player['missed_collection'] >= 9
                || $player['missed_battle'] >= 3
                || ($player['missed_collection'] + $player['missed_battle'] >= 7);

            echo "<tr>";
            echo "<td>" . utf8_encode($player['name']) . "</td>";
            echo "<td>" . $player['missed_collection'] . "</td>";
            echo "<td>" . $player['missed_battle'] . "</td>";
            echo "<td>" . $totalCollectionPlayed . "</td>";
            echo "<td>" . $totalCollectionWon . "</td>";
            echo "<td>" . $totalBattlesPlayed . "</td>";
            echo "<td>" . $totalBattlesWon . "</td>";
            echo "<td>" . $totalCardsEarned . "</td>";

            if ($ban) {
                echo "<td bgcolor='#D42F2F'>Exlure</td>";
            } else if ($warning) {
                echo "<td bgcolor='#FFB732'>A surveiller</td>";
            } else {
                echo "<td bgcolor='#66B266'>Good</td>";
            }
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="../../res/loader.gif"/>
</div>
</body>
</html>