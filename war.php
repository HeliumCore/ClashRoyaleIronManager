<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 16:41
 */

include("tools/database.php");
include("tools/api_conf.php");

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

$getStandings = "
SELECT standings.name, participants, battles_played, battles_won, crowns, war_trophies
FROM standings 
ORDER BY battles_won DESC, crowns DESC 
";
$apiResult = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/war", true, $context);
$state = json_decode($apiResult, true)['state'];
if ($state == "collectionDay") {
    $stateName = "Jour de collection";
} else {
    $stateName = "Jour de guerre";
    $standings = fetch_all_query($db, $getStandings);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guerre en cours</title>
    <link rel="stylesheet" type="text/css" href="css/css.css">
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
    <h1 class="pageTitle">Guerre en cours</h1>
    <span class="pageSubtitle"><?php echo $stateName ?></span>
    <br><br>
    <?php
    if ($stateName == "Jour de guerre") {?>
    <div class="divStandings">
        <table class="tableIndex">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Position</th>
                <th class="headIndex">Nom du clan</th>
                <th class="headIndex">Nombre de participants</th>
                <th class="headIndex">Batailles jouéees</th>
                <th class="headIndex">Batailles gagnées</th>
                <th class="headIndex">Nombre de couronnes</th>
                <th class="headIndex">Trophées de guerre</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (isset($standings)) {
                $pos = 1;
                foreach ($standings as $clan) {
                    echo '<tr>';
                    echo '<th class="headIndex">' . $pos . '</th>';
                    echo '<td class="lineIndex">' . utf8_encode($clan['name']) . '</td>';
                    echo '<td class="lineIndex">' . $clan['participants'] . '</td>';
                    echo '<td class="lineIndex">' . $clan['battles_played'] . '</td>';
                    echo '<td class="lineIndex">' . $clan['battles_won'] . '</td>';
                    echo '<td class="lineIndex">' . $clan['crowns'] . '</td>';
                    echo '<td class="lineIndex">' . $clan['war_trophies'] . '</td>';
                    echo '</tr>';
                    $pos++;
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
    <br><br>
    <span class="pageSubtitle">Résultats par joueurs</span>
    <br><br>
    <div class="divCurrentWar">
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
            global $totalTrophies;
            global $totalCollectionPlayed;
            global $totalCollectionWon;
            global $totalCardsEarned;
            global $totalBattlesPlayed;
            global $totalBattlesWon;
            global $minusParticipant;

            $totalTrophies = 0;
            $totalCollectionPlayed = 0;
            $totalCollectionWon = 0;
            $totalCardsEarned = 0;
            $totalBattlesPlayed = 0;
            $totalBattlesWon = 0;
            $minusParticipant = 0;

            foreach ($warPlayers as $player) {
                $playerTrophies = $player['trophies'];
                $playerCollectionPlayed = $player['collection_played'];
                $playerCollectionWon = $player['collection_won'];
                $playerCardsEarned = $player['cards'];
                $playerBattlesPlayed = $player['battle_played'];
                $playerBattlesWon = $player['battle_won'];

                $totalTrophies += $playerTrophies;
                $totalCollectionPlayed += $playerCollectionPlayed;
                $totalCollectionWon += $playerCollectionWon;
                $totalCardsEarned += $playerCardsEarned;
                $totalBattlesPlayed += $playerBattlesPlayed;
                $totalBattlesWon += $playerBattlesWon;

                if ($playerCollectionPlayed == 0) {
                    $minusParticipant++;
                }

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
            <br>
            <tr>
                <th class="headTotalIndex"><?php echo sizeof($warPlayers); ?></th>
                <td class="lineTotalIndex"><?php
                    $numberOfParticipant = intval(sizeof($warPlayers) - $minusParticipant);
                    echo $numberOfParticipant; ?></td>
                <td class="lineTotalIndex"><?php echo $minusParticipant; ?></td>
                <td class="lineTotalIndex"><?php echo $totalTrophies; ?></td>
                <td class="lineTotalIndex"><?php echo $totalCollectionPlayed; ?></td>
                <td class="lineTotalIndex"><?php echo $totalCollectionWon; ?></td>
                <td class="lineTotalIndex"><?php echo $totalCardsEarned; ?></td>
                <td class="lineTotalIndex"><?php echo $totalBattlesPlayed; ?></td>
                <td class="lineTotalIndex"><?php echo $totalBattlesWon; ?></td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="rowIndex">
                <th class="headTotalIndex">Nombre de joueur éligible à la guerre</th>
                <th class="headTotalIndex">Nombre de participant</th>
                <th class="headTotalIndex">Nombre d'absent</th>
                <th class="headTotalIndex">Total des trophées</th>
                <th class="headTotalIndex">Total des collections jouées</th>
                <th class="headTotalIndex">Total des collections gagnées</th>
                <th class="headTotalIndex">Total des cartes gagnées</th>
                <th class="headTotalIndex">Total des batailles jouées</th>
                <th class="headTotalIndex">Total des batailles gagnées</th>
            </tr>
            </tfoot>
        </table>
    </div>
    <br><br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>
