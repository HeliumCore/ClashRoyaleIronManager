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
 war_history.collection_won, war_history.battle_played, war_history.battle_won, players.name
FROM war_history
JOIN players ON war_history.player_id = players.id
AND players.in_clan > 0
";

$transaction = $db->prepare($getQuery);
$transaction->execute();
$warHistory = $transaction->fetchAll();
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
            <td>Statut</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($warHistory as $player) {
            $warning = $player['missed_collection'] >= 6
                || $player['missed_battle'] >=2
                || ($player['missed_collection'] + $player['missed_battle'] >= 4);

            $ban = $player['missed_collection'] >= 9
            || $player['missed_battle'] >= 3
            || ($player['missed_collection'] + $player['missed_battle'] >= 7);

            echo "<tr>";
            echo "<td>" . utf8_decode($player['name']) . "</td>";
            echo "<td>" . $player['missed_collection'] . "</td>";
            echo "<td>" . $player['missed_battle'] . "</td>";
            echo "<td>" . $player['collection_played'] . "</td>";
            echo "<td>" . $player['collection_won'] . "</td>";
            echo "<td>" . $player['battle_played'] . "</td>";
            echo "<td>" . $player['battle_won'] . "</td>";

            if ($ban) {
                echo "<td bgcolor='#D42F2F'>Exlure</td>";
            } elseif ($warning) {
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
    <img id="loaderImg" src="../../res/loader.gif" />
</div>
</body>
</html>