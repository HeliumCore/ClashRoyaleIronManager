<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 14:56
 */

include("../tools/database.php");

$query = "
SELECT players.tag, players.name as playerName, players.rank, players.trophies, role.name as playerRole, 
players.arena, players.donations, players.donations_received  
FROM players
INNER JOIN role ON role.id = players.role_id
WHERE players.in_clan = 1
ORDER BY players.rank ASC
";

$getPlayerRequest = $db->prepare($query);
$getPlayerRequest->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Les membres</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
            echo "<tr>";
            echo "<th class=\"headIndex\">" . $player['rank'] . "</th>";
            echo "<td class=\"lineIndex\">" . $player['tag'] . "</td>";
            echo "<td class=\"lineIndex\"><a class=\"linkToPlayer\" href=\"view_player.php?tag=" . $player['tag'] . "\">" . utf8_encode($player['playerName']) . "</a></td>";
            echo "<td class=\"lineIndex\">" . utf8_encode($player['playerRole']) . "</td>";
            echo "<td class=\"lineIndex\">" . $player['trophies'] . "</td>";
            echo "<td class=\"lineIndex\">" . $player['arena'] . "</td>";
            echo "<td class=\"lineIndex\">" . $player['donations'] . "</td>";
            echo "<td class=\"lineIndex\">" . $player['donations_received'] . "</td>";
            echo "</tr>";
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
</html>
<script>
    $(document).ready(function () {
        $('#tableIndex').on('click', 'tbody td', function () {
            window.location = $(this).closest('tr').find('td:eq(1) a').attr('href');
        });
    });
</script>