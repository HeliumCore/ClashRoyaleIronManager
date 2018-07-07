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
$finalPlayerList = array();
foreach ($allPlayers as $player) {
    $warStats = getWarStatsByPlayerId($db, $player['id']);
    $thisPlayer['rank'] = $player['rank'];
    $thisPlayer['tag'] = $player['tag'];
    $thisPlayer['name'] = $player['name'];
    $thisPlayer['totalCollectionPlayed'] = $totalCollectionPlayed = $warStats['total_collection_played'] != null ? $warStats['total_collection_played'] : 0;
    $thisPlayer['totalCollectionWon'] = $totalCollectionWon = $warStats['total_collection_won'] != null ? $warStats['total_collection_won'] : 0;
    $thisPlayer['totalCardsEarned'] = $totalCardsEarned = $warStats['total_cards_earned'] != null ? $warStats['total_cards_earned'] : 0;
    $thisPlayer['totalBattlesPlayed'] = $totalBattlesPlayed = $warStats['total_battle_played'] != null ? $warStats['total_battle_played'] : 0;
    $thisPlayer['totalBattlesWon'] = $totalBattlesWon = $warStats['total_battle_won'] != null ? $warStats['total_battle_won'] : 0;
    $missedCollection = countMissedCollection($db, $player['id'])['missed_collection'];
    $thisPlayer['missedCollection'] = $missedCollection == null ? 0 : $missedCollection;
    $missedWar = countMissedWar($db, $player['id'])['missed_war'];
    $thisPlayer['missedWar'] = $missedWar == null ? 0 : $missedWar;

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

    $thisPlayer['warning'] = ($missedCollection + $missedWar) >= 2;
    $thisPlayer['ban'] = ($missedCollection + $missedWar) >= 3;
    if ($thisPlayer['warning'] || $thisPlayer['ban']) {
        $allBadStatus++;
    }
    $finalPlayerList[] = $thisPlayer;
}
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
                url: 'query/update_clan.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    updateWarStats();
                }
            })
        }

        function updateWarStats() {
            $.ajax({
                url: 'query/update_war_stats.php',
                success: function () {
                    window.location.reload(true);
                }
            });
        }

        $(document).ready(function () {
            $('.js-player-table').each(function () {
                $(this).on('click', 'tbody td', function () {
                    $("body").css("cursor", "wait");
                    window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
                });
            });

            $('#tx_search').on("keyup paste", function() {
                let value = $(this).val().toLowerCase();
                const playerLine = $('.playerTr');
                if (value.length < 3) {
                    playerLine.show();
                    return;
                }

                playerLine.each(function() {
                    if ($(this).next().val().toLowerCase().indexOf(value) < 0)
                        $(this).hide();
                });
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
    <input type="text" id="tx_search" class="" placeholder="Trier par nom"/>
    <!--    todo faire design du champ de recherche-->
    <br><br>
    <!-- Nav tabs -->
    <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#collect" aria-controls="collect" role="tab" data-toggle="tab"
                                                  class="tab-link">Collections</a></li>
        <li role="presentation"><a href="#war" aria-controls="war" role="tab" data-toggle="tab" class="tab-link">Batailles</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="collect">
            <div class="table-responsive">
                <table class="table" id="tableIndex">
                    <tbody>
                    <tr>
                        <td class="whiteShadow text-center">Joueurs<br><?php echo sizeof($finalPlayerList); ?></td>
                        <td class="whiteShadow text-center">Jouées<br><?php echo $allCollectionsPlayed; ?></td>
                        <td class="whiteShadow text-center">Gagnées<br><?php echo $allCollectionsWon; ?></td>
                        <td class="whiteShadow text-center">
                            % victoires<br>
                            <?php echo ($allCollectionsPlayed != 0) ? round((($allCollectionsWon / $allCollectionsPlayed) * 100)) : '--'; ?>
                        </td>
                        <td class="whiteShadow text-center">Absences<br><?php echo $allMissedCollections; ?></td>
                        <td class="whiteShadow text-center">% présences<br>
                            <?php echo ($allCollectionsPlayed != 0) ? round(($allCollections / $allCollectionsPlayed) * 100) : '--'; ?>
                        </td>
                        <td class="whiteShadow text-center"><img src="images/ui/deck.png"
                                                                 height="35px"/>&nbsp;<?php echo $allCardsEarned; ?>
                        </td>
                        <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                    style="display:block;width: 41px;margin:auto"><?php echo $allBadStatus; ?></span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!--            TODO gerer les pourcentages (pas bon pour le % de presence, total et par joueur-->
            <div class="table-responsive">
                <table class="table js-player-table" id="tableIndex">
                    <tbody>
                    <?php foreach ($finalPlayerList as $player) : ?>
                        <tr class="pointerHand playerTr">
                            <td class="whiteShadow text-center rank">
                                <span><?php echo utf8_encode($player['rank']); ?></span></td>
                            <td class="whiteShadow"><a class="linkToPlayer"
                                                       href="view_player.php?tag=<?php echo $player['tag']; ?>">
                                    <?php echo utf8_encode($player['name']); ?></a></td>
                            <td class="whiteShadow text-center">
                                jouées<br><?php echo $player['totalCollectionPlayed']; ?></td>
                            <td class="whiteShadow text-center">gagnées<br><?php echo $player['totalCollectionWon']; ?>
                            </td>
                            <td class="whiteShadow text-center">Victoires <br>
                                <?php echo ($player['totalCollectionPlayed'] != 0) ? round((($player['totalCollectionWon'] / $player['totalCollectionPlayed']) * 100)) . '%' : '--'; ?>
                            <td class="whiteShadow  text-center">Absence<br><?php echo $player['missedCollection'] ?>
                            </td>
                            <td class="whiteShadow text-center">Présence<br>
                                <?php echo ($player['totalCollectionPlayed'] != 0) ? round(($player['totalCollection'] / $player['totalCollectionPlayed']) * 100) : 0; ?>
                            </td>
                            <td class="whiteShadow"><img src="images/ui/deck.png"
                                                         height="35px"/>&nbsp;<?php echo $player['totalCardsEarned']; ?>
                            </td>
                            <!-- Status -->
                            <?php if ($player['ban']) : ?>
                                <td bgcolor="#D42F2F" class="text-center"><img src="images/ui/no-cancel.png"
                                                                               height="35px"/></td>
                            <?php elseif ($player['warning']): ?>
                                <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png" height="35px"/>
                                </td>
                            <?php else : ?>
                                <td bgcolor="#66B266" class="text-center"><img src="images/ui/yes-confirm.png"
                                                                               height="35px"/></td>
                            <?php endif; ?>
                        </tr>
                        <input type="hidden" class="hd_playerName" value="<?php print utf8_encode($player['name']); ?>"/>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="war">
            <div class="table-responsive">
                <table class="table" id="tableIndex">
                    <tbody>
                    <tr>
                        <td class="whiteShadow text-center">Joueurs<br><?php echo sizeof($finalPlayerList); ?></td>
                        <td class="whiteShadow text-center">Jouées<br><?php echo $allBattlePlayed; ?></td>
                        <td class="whiteShadow text-center">Gagnées<br><?php echo $allBattleWon; ?></td>
                        <td class="whiteShadow text-center">% victoires<br>
                            <?php if ($allBattlePlayed != 0) echo round((($allBattleWon / $allBattlePlayed) * 100));
                            else echo '--'; ?>
                        </td>
                        <td class="whiteShadow text-center">Abscences<br><?php echo $allMissedWar; ?></td>
                        <td class="whiteShadow text-center">% présences<br>
                            <?php if ($allWars != 0) echo '' . round(($allBattlePlayed / $allWars) * 100);
                            else echo '--'; ?>
                        </td>
                        <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                    style="display:block;width: 41px;margin:auto"><?php echo $allBadStatus; ?></span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table js-player-table" id="tableIndex">
                    <tbody>
                    <?php foreach ($finalPlayerList as $player) : ?>
                        <tr class="pointerHand playerTr">
                            <td class="whiteShadow text-center rank">
                                <span><?php echo utf8_encode($player['rank']); ?></span></td>
                            <td class="whiteShadow"><a class="linkToPlayer"
                                                       href="view_player.php?tag=<?php echo $player['tag']; ?>">
                                    <?php echo utf8_encode($player['name']); ?></a></td>
                            <td class="whiteShadow text-center">jouées<br><?php echo $player['totalBattlesPlayed']; ?>
                            </td>
                            <td class="whiteShadow text-center">gagnées<br><?php echo $player['totalBattlesWon']; ?>
                            </td>
                            <td class="whiteShadow text-center">Victoires<br>
                                <?php echo ($player['totalBattlesPlayed'] != 0) ? round((($player['totalBattlesWon'] / $player['totalBattlesPlayed']) * 100)) . '%' : '-'; ?>
                            </td>
                            <td class="whiteShadow text-center">Absence<br><?php echo $player['missedWar'] ?></td>
                            <td class="whiteShadow text-center">Présence<br>
                                <?php echo ($player['totalBattlesPlayed'] != 0) ? round(($player['totalWar'] / $player['totalBattlesPlayed']) * 100) . "%" : '-'; ?>
                            </td>

                            <!-- Status -->
                            <?php if ($player['ban']) : ?>
                                <td bgcolor="#D42F2F" class="text-center"><img src="images/ui/no-cancel.png"
                                                                               height="35px"/></td>
                            <?php elseif ($player['warning']): ?>
                                <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png" height="35px"/>
                                </td>
                            <?php else : ?>
                                <td bgcolor="#66B266" class="text-center"><img src="images/ui/yes-confirm.png"
                                                                               height="35px"/></td>
                            <?php endif; ?>
                        </tr>
                        <input type="hidden" class="hd_playerName" value="<?php print utf8_encode($player['name']); ?>"/>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>