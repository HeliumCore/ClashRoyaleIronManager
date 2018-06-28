<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 10:56
 */

include("../tools/database.php");

$getAllPlayersQuery = "
SELECT players.id, players.name
FROM players
WHERE in_clan > 0
";

$allPlayers = fetch_all_query($db, $getAllPlayersQuery);

$getPattern = "
SELECT SUM(cards_earned) as total_cards_earned, 
SUM(collection_played) as total_collection_played, 
SUM(collection_won) as total_collection_won,
SUM(battle_played) as total_battle_played,
SUM(battle_won) as total_battle_won
FROM player_war
WHERE player_id = %d
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
        foreach ($allPlayers as $player) {
            $getResult = fetch_query($db, sprintf($getPattern, $player['id']));
            $totalCollectionPlayed = $getResult['total_collection_played'] != null ? $getResult['total_collection_played'] : 0;
            $totalCollectionWon = $getResult['total_collection_won'] != null ? $getResult['total_collection_won'] : 0;
            $totalCardsEarned = $getResult['total_cards_earned'] != null ? $getResult['total_cards_earned'] : 0;
            $totalBattlesPlayed = $getResult['total_battle_played'] != null ? $getResult['total_battle_played'] : 0;
            $totalBattlesWon = $getResult['total_battle_won'] != null ? $getResult['total_battle_won'] : 0;

//            $warning = $player['missed_collection'] >= 6
//                || $player['missed_battle'] >= 2
//                || ($player['missed_collection'] + $player['missed_battle'] >= 4);
//
//            $ban = $player['missed_collection'] >= 9
//                || $player['missed_battle'] >= 3
//                || ($player['missed_collection'] + $player['missed_battle'] >= 7);

            echo "<tr>";
            echo "<td>" . utf8_encode($player['name']) . "</td>";
            echo "<td> pas encore fait </td>";
            echo "<td> pas encore fait </td>";
            echo "<td>" . $totalCollectionPlayed . "</td>";
            echo "<td>" . $totalCollectionWon . "</td>";
            echo "<td>" . $totalBattlesPlayed . "</td>";
            echo "<td>" . $totalBattlesWon . "</td>";
            echo "<td>" . $totalCardsEarned . "</td>";

//            if ($ban) {
//                echo "<td bgcolor='#D42F2F'>Exlure</td>";
//            } else if ($warning) {
//                echo "<td bgcolor='#FFB732'>A surveiller</td>";
//            } else {
//                echo "<td bgcolor='#66B266'>Good</td>";
//            }
            echo "<td bgcolor='#66B266'>Good</td>";

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