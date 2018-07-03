<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 10:56
 */

include("tools/database.php");

$allPlayers = getAllPlayersByRank($db);
$firstWarDate = getFirstWarDate($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Historique des guerres</title>
    <?php include("head.php"); ?>
    <script>
        function update() {
            $.ajax({
                url: '../query/update_clan.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    $.ajax({
                        url: '../query/update_war_stats.php',
                        success: function () {
                            window.location.reload(true);
                        }
                    });
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
<div class="container">
    <h1 class="whiteShadow">Statistiques des guerres</h1>
    <span class="whiteShadow">Première guerre : <b><?php echo '' . date('d/m/Y', $firstWarDate['created']) ?></b></span>
    <br>
    <br><br>
    <table class="table" id="tableIndex">
        <thead>
        <tr class="rowIndex">
            <th class="text-center">Rang</th>
            <th>Joueur</th>
            <th class="text-center" colspan="6">Collections</th>
            <th class="text-center" colspan="5">Batailles</th>
            <th>Statut</th>
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
            $warStats = getWarStatsByPlayerId($db, $player['id']);

            $totalCollectionPlayed = $warStats['total_collection_played'] != null ? $warStats['total_collection_played'] : 0;
            $totalCollectionWon = $warStats['total_collection_won'] != null ? $warStats['total_collection_won'] : 0;
            $totalCardsEarned = $warStats['total_cards_earned'] != null ? $warStats['total_cards_earned'] : 0;
            $totalBattlesPlayed = $warStats['total_battle_played'] != null ? $warStats['total_battle_played'] : 0;
            $totalBattlesWon = $warStats['total_battle_won'] != null ? $warStats['total_battle_won'] : 0;
            $missedCollection = countMissedCollection($db, $player['id'])['missed_collection'];
            $missedCollection = $missedCollection == null ? 0 : $missedCollection;
            $missedWar = countMissedWar($db, $player['id'])['missed_war'];
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
            echo '<td class="whiteShadow text-center rank"><span>' . utf8_encode($player['rank']) . '</span></td>';
            echo '<td class="whiteShadow"><a class="linkToPlayer" href="view_player.php?tag=' . $player['tag'] . '">' . utf8_encode($player['name']) . '</a></td>';
            // Collections
            echo '<td class="whiteShadow text-center">jouées<br>' . $totalCollectionPlayed . '</td>';
            echo '<td class="whiteShadow text-center">gagnées<br>' . $totalCollectionWon . '</td>';
            echo '<td class="whiteShadow text-center">Victoires <br>';
            echo ($totalCollectionPlayed != 0) ? round((($totalCollectionWon / $totalCollectionPlayed) * 100)). '%' : '--';
            echo '<td class="whiteShadow  text-center">Absence<br>' . $missedCollection . '</td>';
            echo '<td class="whiteShadow text-center">Présence<br>' ;
            echo ($totalCollectionPlayed != 0) ? round(($totalCollection / $totalCollectionPlayed) * 100) : 0;
            echo  '</td>';
            echo '<td class="whiteShadow"><img src="images/ui/deck.png" height="35px"/>&nbsp;' . $totalCardsEarned . '</td>';
            // War
            echo '<td class="whiteShadow text-center">jouées<br>' . $totalBattlesPlayed . '</td>';
            echo '<td class="whiteShadow text-center">gagnées<br>' . $totalBattlesWon . '</td>';
            echo '<td class="whiteShadow text-center">Victoires<br>';
            echo ($totalBattlesPlayed != 0) ? round((($totalBattlesWon / $totalBattlesPlayed) * 100)). '%' : '-';
            echo '</td>';
            echo '<td class="whiteShadow text-center">Absence<br>' . $missedWar . '</td>';
            echo '<td class="whiteShadow text-center">Présence<br>';
            echo ($totalBattlesPlayed != 0) ? round(($totalWar / $totalBattlesPlayed) * 100). "%" : '-';
            echo '</td>';
            // Status
            if ($ban) {
                echo '<td bgcolor="#D42F2F" class="text-center"><img src="images/ui/no-cancel.png" height="35px"/></td>';
                $allBadStatus++;
            } else if ($warning) {
                echo '<td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png" height="35px"/></td>';
                $allBadStatus++;
            } else {
                echo '<td bgcolor="#66B266" class="text-center"><img src="images/ui/yes-confirm.png" height="35px"/></td>';
            }
            echo '</tr>';
        }
        ?>
        <tr>
            <th class="whiteShadow text-center"><?php echo sizeof($allPlayers); ?></th>
            <td class="whiteShadow text-center"><?php echo 'X'; ?></td>
            <td class="whiteShadow text-center"><?php echo $allCollectionsPlayed; ?></td>
            <td class="whiteShadow text-center"><?php echo $allCollectionsWon; ?></td>
            <td class="whiteShadow text-center">
            <?php 
            echo ($allCollectionsPlayed != 0) ? round((($allCollectionsWon / $allCollectionsPlayed) * 100)) : 0; ?>
            </td>
            <td class="whiteShadow text-center"><?php echo $allMissedCollections; ?></td>
            <td class="whiteShadow text-center">
            <?php echo ($allCollectionsPlayed != 0) ? round(($allCollections / $allCollectionsPlayed) * 100) : 0; ?>
            </td>
            <td class="whiteShadow text-center"><img src="images/ui/deck.png" height="35px"/>&nbsp;<?php echo $allCardsEarned; ?></td>
            <td class="whiteShadow text-center"><?php echo $allBattlePlayed; ?></td>
            <td class="whiteShadow text-center"><?php echo $allBattleWon; ?></td>
            <?php if ($allBattlePlayed != 0) echo '<td class="whiteShadow text-center">' . round((($allBattleWon / $allBattlePlayed) * 100)) . '</td>';
            else echo '<td class="whiteShadow">0</td>'; ?>
            <td class="whiteShadow text-center"><?php echo $allMissedWar; ?></td>
            <?php if ($allWars != 0) echo '<td class="whiteShadow text-center">' . round(($allBattlePlayed / $allWars) * 100) . '</td>';
            else echo '<td class="whiteShadow">0</td>'; ?>
            <td bgcolor="#66B266"><?php echo $allBadStatus; ?></td>
        </tr>
        </tbody>
        <thead>
        <tr class="rowIndex">
            <th class="">Nombre de joueur éligible à la guerre</th>
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
<!-- TODO corriger lien <tr> pour page joueur -->
</html>