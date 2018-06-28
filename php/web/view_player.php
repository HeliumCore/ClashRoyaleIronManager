<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 28/06/2018
 * Time: 16:08
 */

include("../tools/database.php");

if (isset($_GET['tag']) && !empty($_GET['tag'])) $getPlayerTag = $_GET['tag'];
else header('Location: index.php');

$getPlayerByTag = "
SELECT players.tag, players.name as playerName, players.rank, players.trophies, role.name as playerRole, 
arena.arena as arena, players.donations, players.donations_received  
FROM players
INNER JOIN arena ON arena.arena_id = players.arena
INNER JOIN role ON role.id = players.role_id
WHERE tag = \"%s\"
";

$getPlayer = fetch_query($db, utf8_decode(sprintf($getPlayerByTag, $getPlayerTag)));
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
    <h1 class="pageTitle">Détails du joueur</h1>
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
        echo '<tr>';
        echo '<th class="headIndex">' . $getPlayer['rank'] . '</th>';
        echo '<td class="lineIndex">' . $getPlayer['tag'] . '</td>';
        echo '<td class="lineIndex">' . utf8_encode($getPlayer['playerName']) . '</td>';
        echo '<td class="lineIndex">' . utf8_encode($getPlayer['playerRole']) . '</td>';
        echo '<td class="lineIndex">' . $getPlayer['trophies'] . '</td>';
        echo '<td class="lineIndex">' . $getPlayer['arena'] . '</td>';
        echo '<td class="lineIndex">' . $getPlayer['donations'] . '</td>';
        echo '<td class="lineIndex">' . $getPlayer['donations_received'] . '</td>';
        echo '</tr>';
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
