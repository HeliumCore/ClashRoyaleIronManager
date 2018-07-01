<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 10:56
 */

include("tools/database.php");

$getAllPlayersQuery = "
SELECT players.id, players.name, players.rank, players.tag
FROM players
WHERE in_clan > 0
ORDER BY rank ASC
";

$allPlayers = fetch_all_query($db, $getAllPlayersQuery);

$getPattern = "
SELECT SUM(cards_earned) as total_cards_earned, 
SUM(collection_played) as total_collection_played, 
SUM(collection_won) as total_collection_won,
SUM(battle_played) as total_battle_played,
SUM(battle_won) as total_battle_won
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_id = %d
AND war.past_war > 0
";

$countMissedWarPattern = "
SELECT COUNT(player_war.id) as missed_war
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_war.battle_played = 0
AND war.past_war > 0
AND player_war.player_id = %d
";

$countMissedCollectionPattern = "
SELECT player_war.collection_played as missed_collection
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_war.collection_played = 0
AND war.past_war > 0
AND war.created != 1530050844
AND player_war.player_id = %d
";

$getFirstWarDateQuery = "
SELECT created
FROM war
WHERE past_war > 0
AND created != 1530050844
LIMIT 1
";
$firstWarDate = fetch_query($db, $getFirstWarDateQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Historique des guerres</title>
    <link rel="stylesheet" type="text/css" href="css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        function update() {
            $.ajax({
                url: '../query/update_war_stats.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    window.location.reload(true);
                }
            })
        }

        $(document).ready(function () {
            $('#tableIndex').on('click', 'tbody td', function () {
                window.location = $(this).closest('tr').find('td:eq(0) a').attr('href');
            });
        });
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1 class="pageTitle">Statistiques des guerres</h1>
    <span class="pageSubtitle">Première guerre : <b><?php echo '' . date('d/m/Y', $firstWarDate['created']) ?></b></span>
    <br>
    <br><br>
    <table class="tableIndex" id="tableIndex">
        <thead>
        <tr class="rowIndex">
            <th class="headIndex">Rang du joueur</th>
            <th class="headIndex">Nom du joueur</th>
            <th class="headIndex">Collections jouées</th>
            <th class="headIndex">Collections gagnées</th>
            <th class="headIndex">Pourcentage victoire collection</th>
            <th class="headIndex">Absence collections</th>
            <th class="headIndex">Pourcentage de présence collection</th>
            <th class="headIndex">Cartes récoltées</th>
            <th class="headIndex">Batailles jouées</th>
            <th class="headIndex">Batailles gagnées</th>
            <th class="headIndex">Pourcentage victoire guerre</th>
            <th class="headIndex">Absence batailles</th>
            <th class="headIndex">Pourcentage de présence guerre</th>
            <th class="headIndex">Statut</th>
        </tr>
        </thead>
        <tbody>
        <?php
        global $allCollections;
        global $allCollectionsPlayed;
        global $allCollectionsWon;
        global $allCardsEarned;
        global $allWars;
        global $allBattlePlayed;
        global $allBattleWon;
        global $allMissedCollections;
        global $allMissedWar;
        global $missedConsecutiveCollection;
        global $missedConsecutiveWar;
        global $allBadStatus;

        $allCollections = 0;
        $allCollectionsPlayed = 0;
        $allCollectionsWon = 0;
        $allCardsEarned = 0;
        $allWars = 0;
        $allBattlePlayed = 0;
        $allBattleWon = 0;
        $allMissedCollections = 0;
        $allMissedWar = 0;
        $allBadStatus = 0;
        foreach ($allPlayers as $player) {
            $getResult = fetch_query($db, sprintf($getPattern, $player['id']));

            $totalCollectionPlayed = $getResult['total_collection_played'] != null ? $getResult['total_collection_played'] : 0;
            $totalCollectionWon = $getResult['total_collection_won'] != null ? $getResult['total_collection_won'] : 0;
            $totalCardsEarned = $getResult['total_cards_earned'] != null ? $getResult['total_cards_earned'] : 0;
            $totalBattlesPlayed = $getResult['total_battle_played'] != null ? $getResult['total_battle_played'] : 0;
            $totalBattlesWon = $getResult['total_battle_won'] != null ? $getResult['total_battle_won'] : 0;
            $missedCollection = fetch_query($db, sprintf($countMissedCollectionPattern, $player['id']))['missed_collection'];
            $missedCollection = $missedCollection == null ? 0 : $missedCollection;
            $missedWar = fetch_query($db, sprintf($countMissedWarPattern, $player['id']))['missed_war'];
            $missedWar = $missedWar == null ? 0 : $missedWar;

            $totalCollection = $totalCollectionPlayed + $missedCollection;
            $totalWar = $totalBattlesPlayed + $missedWar;

            $allCollections += $totalCollection;
            $allCollectionsPlayed += $totalCollectionPlayed;
            $allCollectionsWon += $totalCollectionWon;
            $allMissedCollections += $missedCollection;
            $allCardsEarned += $totalCardsEarned;
            $allWars += $totalWar;
            $allBattlePlayed += $totalBattlesPlayed;
            $allBattleWon += $totalBattlesWon;
            $allMissedWar += $missedWar;

            $warning = ($missedCollection + $missedWar) >= 2;
            $ban = ($missedCollection + $missedWar) >= 3;

            echo '<tr>';
            echo '<th class="headIndex">' . utf8_encode($player['rank']) . '</th>';
            echo '<td class="lineIndex"><a class="linkToPlayer" href="view_player.php?tag=' . $player['tag'] . '">' . utf8_encode($player['name']) . '</a></td>';
            // Collections
            echo '<td class="lineIndex">' . $totalCollectionPlayed . '</td>';
            echo '<td class="lineIndex">' . $totalCollectionWon . '</td>';
            if ($totalCollectionPlayed != 0) echo '<td class="lineIndex">' . round((($totalCollectionWon / $totalCollectionPlayed) * 100)) . '</td>';
            else echo '<td class="lineIndex">0</td>';
            echo '<td class="lineIndex">' . $missedCollection . '</td>';
            if ($totalCollectionPlayed != 0) echo '<td class="lineIndex">' . round(($totalCollection / $totalCollectionPlayed) * 100) . '</td>';
            else echo '<td class="lineIndex">0</td>';
            echo '<td class="lineIndex">' . $totalCardsEarned . '</td>';
            // War
            echo '<td class="lineIndex">' . $totalBattlesPlayed . '</td>';
            echo '<td class="lineIndex">' . $totalBattlesWon . '</td>';
            if ($totalBattlesPlayed != 0) echo '<td class="lineIndex">' . round((($totalBattlesWon / $totalBattlesPlayed) * 100)) . '</td>';
            else echo '<td class="lineIndex">0</td>';
            echo '<td class="lineIndex">' . $missedWar . '</td>';
            if ($totalBattlesPlayed != 0) echo '<td class="lineIndex">' . round(($totalWar / $totalBattlesPlayed) * 100) . '</td>';
            else echo '<td class="lineIndex">0</td>';
            // Status
            if ($ban) {
                echo '<td bgcolor="#D42F2F">Exlure</td>';
                $allBadStatus++;
            } else if ($warning) {
                echo '<td bgcolor="#FFB732">A surveiller</td>';
                $allBadStatus++;
            } else {
                echo '<td bgcolor="#66B266">RAS</td>';
            }
            echo '</tr>';
        }
        ?>
        <tr>
            <th class="headTotalIndex"><?php echo sizeof($allPlayers); ?></th>
            <td class="lineTotalIndex"><?php echo 'X'; ?></td>
            <td class="lineTotalIndex"><?php echo $allCollectionsPlayed; ?></td>
            <td class="lineTotalIndex"><?php echo $allCollectionsWon; ?></td>
            <?php if ($allCollectionsPlayed != 0) echo '<td class="lineTotalIndex">' . round((($allCollectionsWon / $allCollectionsPlayed) * 100)) . '</td>';
            else echo '<td class="lineTotalIndex">0</td>'; ?>
            <td class="lineTotalIndex"><?php echo $allMissedCollections; ?></td>
            <?php if ($allCollectionsPlayed != 0) echo '<td class="lineIndex">' . round(($allCollections / $allCollectionsPlayed) * 100) . '</td>';
            else echo '<td class="lineTotalIndex">0</td>'; ?>
            <td class="lineTotalIndex"><?php echo $allCardsEarned; ?></td>
            <td class="lineTotalIndex"><?php echo $allBattlePlayed; ?></td>
            <td class="lineTotalIndex"><?php echo $allBattleWon; ?></td>
            <?php if ($allBattlePlayed != 0) echo '<td class="lineTotalIndex">' . round((($allBattleWon / $allBattlePlayed) * 100)) . '</td>';
            else echo '<td class="lineTotalIndex">0</td>'; ?>
            <td class="lineTotalIndex"><?php echo $allMissedWar; ?></td>
            <?php if ($allWars != 0) echo '<td class="lineTotalIndex">' . round(($allBattlePlayed / $allWars) * 100) . '</td>';
            else echo '<td class="lineTotalIndex">0</td>'; ?>
            <td bgcolor="#66B266"><?php echo $allBadStatus; ?></td>
        </tr>
        </tbody>
        <thead>
        <tr class="rowIndex">
            <th class="headTotalIndex">Nombre de joueur éligible à la guerre</th>
            <th class="headTotalIndex">X</th>
            <th class="headTotalIndex">Total des collections jouées</th>
            <th class="headTotalIndex">Total des collections gagnées</th>
            <th class="headTotalIndex">Pourcentage victoire collections</th>
            <th class="headTotalIndex">Total des absence collections</th>
            <th class="headTotalIndex">Pourcentage de présences absences</th>
            <th class="headTotalIndex">Total des cartes récoltées</th>
            <th class="headTotalIndex">Total des batailles jouées</th>
            <th class="headTotalIndex">Total des batailles gagnées</th>
            <th class="headTotalIndex">Pourcentage victoire guerres</th>
            <th class="headTotalIndex">Total des absence batailles</th>
            <th class="headTotalIndex">Pourcentage de présence guerres</th>
            <th class="headTotalIndex">Nombre de status pas RAS</th>
        </tr>
        </thead>
    </table>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>