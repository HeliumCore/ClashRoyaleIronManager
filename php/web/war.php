<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 16:41
 */

include("../tools/database.php");

$getWarPlayers = "
SELECT players.rank, players.tag, players.name, players.playerRole, players.trophies, player_war.battle_played, player_war.battle_won,
player_war.collection_played, player_war.collection_won
FROM player_war
INNER JOIN war ON war.id = player_war.war_id
INNER JOIN players ON players.id = player_war.player_id
INNER JOIN player_war ON player_war.id = players.id
WHERE war.past_war = 0
ORDER BY player.rank ASC
";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guerre en cours</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
</head>
<body>
<?php include("header.html"); ?>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1 class="pageTitle">Liste des joueurs</h1>
    <br><br>
    <table id="tableIndex" class="tableIndex">
        <thead>
        <tr class="rowIndex">
            <th class="headIndex">Rang</th>
            <th class="headIndex">Tag</th>
            <th class="headIndex">Nom</th>
            <th class="headIndex">Role</th>
            <th class="headIndex">Trophée</th>
            <th class="headIndex">Arène</th>
            <th class="headIndex">Donations</th>
            <th class="headIndex">Donations reçues</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($getPlayerRequest as $player) {
            echo '<tr>';
            echo '<th class="headIndex">' . $player['rank'] . '</th>';
            echo '<td class="lineIndex"><a class="linkToPlayer" href="view_player.php?tag=' . $player['tag'] . '">' . utf8_encode($player['playerName']) . '</a></td>';
            echo '<td class="lineIndex">' . utf8_encode($player['playerRole']) . '</td>';
            echo '<td class="lineIndex">' . $player['trophies'] . '</td>';
            echo '<td class="lineIndex">' . $player['collection_played'] . '</td>';
            echo '<td class="lineIndex">' . $player['collection_won'] . '</td>';
            echo '<td class="lineIndex">' . $player['battle_played'] . '</td>';
            echo '<td class="lineIndex">' . $player['battle_won'] . '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
        <tfoot>
        <tr class="rowIndex">
            <th class="headIndex">Rang</th>
            <th class="headIndex">Tag</th>
            <th class="headIndex">Nom</th>
            <th class="headIndex">Role</th>
            <th class="headIndex">Trophée</th>
            <th class="headIndex">Arène</th>
            <th class="headIndex">Donations</th>
            <th class="headIndex">Donations reçues</th>
        </tr>
        </tfoot>
    </table>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="../../res/loader.gif"/>
</div>
</body>
<?php include("footer.html"); ?>
</html>
<script>
    $(document).ready(function () {
        $('#tableIndex').on('click', 'tbody td', function () {
            window.location = $(this).closest('tr').find('td:eq(1) a').attr('href');
        });
    });
</script>
