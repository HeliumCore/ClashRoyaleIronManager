<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 16:41
 */

include("../tools/database.php");

$getWarPlayers = "
SELECT players.rank, players.tag, players.name, role.name as role_name, players.trophies, player_war.battle_played, 
player_war.battle_won, player_war.collection_played, player_war.collection_won, player_war.cards_earned as cards
FROM player_war
INNER JOIN war ON war.id = player_war.war_id
INNER JOIN players ON players.id = player_war.player_id
INNER JOIN role ON role.id = players.role_id
WHERE war.past_war = 0
ORDER BY players.rank ASC
";

$warPlayers = fetch_all_query($db, $getWarPlayers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guerre en cours</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tableIndex').on('click', 'tbody td', function () {
                window.location = $(this).closest('tr').find('td:eq(0) a').attr('href');
            });
        });
        function update() {
            $.ajax({
                url: '../query/update_war.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    window.location.reload(true);
                }
            })
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1 class="pageTitle">Liste des joueurs</h1>
    <br><br>
    <table id="tableIndex" class="tableIndex">
        <thead>
        <tr class="rowIndex">
            <th class="headIndex">Rang</th>
            <th class="headIndex">Nom</th>
            <th class="headIndex">Role</th>
            <th class="headIndex">Trophées</th>
            <th class="headIndex">Collections jouées</th>
            <th class="headIndex">Collections gagnées</th>
            <th class="headIndex">Cartes gagnées</th>
            <th class="headIndex">Batailles jouées</th>
            <th class="headIndex">Batailles gagnées</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($warPlayers as $player) {
            echo '<tr>';
            echo '<th class="headIndex">' . $player['rank'] . '</th>';
            echo '<td class="lineIndex"><a class="linkToPlayer" href="view_player.php?tag=' . $player['tag'] . '">' . utf8_encode($player['name']) . '</a></td>';
            echo '<td class="lineIndex">' . utf8_encode($player['role_name']) . '</td>';
            echo '<td class="lineIndex">' . $player['trophies'] . '</td>';
            echo '<td class="lineIndex">' . $player['collection_played'] . '</td>';
            echo '<td class="lineIndex">' . $player['collection_won'] . '</td>';
            echo '<td class="lineIndex">' . $player['cards'] . '</td>';
            echo '<td class="lineIndex">' . $player['battle_played'] . '</td>';
            echo '<td class="lineIndex">' . $player['battle_won'] . '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
        <tfoot>
        <tr class="rowIndex">
            <th class="headIndex">Rang</th>
            <th class="headIndex">Nom</th>
            <th class="headIndex">Role</th>
            <th class="headIndex">Trophées</th>
            <th class="headIndex">Collections jouées</th>
            <th class="headIndex">Collections gagnées</th>
            <th class="headIndex">Cartes gagnées</th>
            <th class="headIndex">Batailles jouées</th>
            <th class="headIndex">Batailles gagnées</th>
        </tr>
        </tfoot>
    </table>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="../../res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>
