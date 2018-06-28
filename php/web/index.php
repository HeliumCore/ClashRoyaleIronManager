<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 14:56
 */

include("../tools/database.php");

/*Data to get
-> name
-> tag
-> rank
-> previousRank
-> role
-> expLevel
-> trophies
-> donations
-> donationsDelta
-> arena
-> donationsPercent
*/

$query = "
SELECT players.tag, players.name as playerName, players.rank, players.trophies, role.name as playerRole, players.exp_level, 
players.arena, players.donations, players.donations_received, players.donations_delta, players.donations_ratio
FROM players
INNER JOIN role ON role.id = players.role_id
WHERE players.in_clan = 1
ORDER BY players.rank ASC
";

$getPlayerRequest = $db->prepare($query);
$getPlayerRequest->execute();
?>
<!--TODO gerer le donation delta et ratio-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Les membres</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        function updateClan() {
            $.ajax({
                url: '../query/update_clan.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    window.location = 'index.php';
                }
            })
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1>Liste des joueurs</h1>
    <br>
    <button
            id="updateClanBtn"
            class="btn"
            onclick="updateClan()"
    >
        Mettre à jour
    </button>
    <br><br>
    <table>
        <thead>
        <tr>
            <th>Rang</th>
            <th>Tag</th>
            <th>Nom</th>
            <th>Role</th>
            <th>Niveau du roi</th>
            <th>Trophée</th>
            <th>Arène</th>
            <th>Donations</th>
            <th>Donations reçues</th>
        </tr>
        </thead>
        <tbody>
        <?php
        //
        foreach ($getPlayerRequest as $player) {
            echo "<tr>";
            echo "<th>" . $player['rank'] . "</th>";
            echo "<td>" . $player['tag'] . "</td>";
            echo "<td>" . utf8_encode($player['playerName']) . "</td>";
            echo "<td>" . utf8_encode($player['playerRole']) . "</td>";
            echo "<td>" . $player['exp_level'] . "</td>";
            echo "<td>" . $player['trophies'] . "</td>";
            echo "<td>" . $player['arena'] . "</td>";
            echo "<td>" . $player['donations'] . "</td>";
            echo "<td>" . $player['donations_received'] . "</td>";
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