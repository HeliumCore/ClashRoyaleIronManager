<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 10:56
 */

include("tools/database.php");

if (isset($_GET['order']) && !empty($_GET['order'])) {
    $order = $_GET['order'];
    $selectValue = substr($order, -1);
    $order = substr($order, 0, -1);
    $warStatsByPlayer = getWarStats($db, $order);
} else {
    $selectValue = -1;
    $warStatsByPlayer = getWarStats($db);
}

$allPlayers = getAllPlayersByRank($db);
$firstWarDate = getFirstWarDate($db);

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

foreach ($warStatsByPlayer as $player) {
    $playerInfo['rank'] = $player['rank'];
    $playerInfo['tag'] = $player['tag'];
    $playerInfo['name'] = $player['name'];
    $playerInfo['totalCollectionPlayed'] = $totalCollectionPlayed = $player['total_collection_played'];
    $playerInfo['totalCollectionWon'] = $totalCollectionWon = $player['total_collection_won'];
    $playerInfo['totalCardsEarned'] = $totalCardsEarned = $player['total_cards_earned'];
    $playerInfo['totalBattlesPlayed'] = $totalBattlesPlayed = $player['total_battle_played'];
    $playerInfo['totalBattlesWon'] = $totalBattlesWon = $player['total_battle_won'];
    $missedCollection = countMissedCollection($db, $player['id'])['missed_collection'];
    $playerInfo['missedCollection'] = $missedCollection == null ? 0 : $missedCollection;
    $missedWar = countMissedWar($db, $player['id'])['missed_war'];
    $playerInfo['missedWar'] = $missedWar == null ? 0 : $missedWar;
    $playerInfo['totalCollection'] = $totalCollection = $totalCollectionPlayed + $missedCollection;
    $totalWar = $totalBattlesPlayed + $missedWar;
    $eligibleWars = getNumberOfEligibleWarByPlayerId($db, $player['id']);
    if ($missedWar >= 2) {
        $playerInfo['ban'] = true;
        $playerInfo['warning'] = false;
    } else if ($missedWar == 1) {
        $playerInfo['ban'] = false;
        $playerInfo['warning'] = true;
    } else if ($eligibleWars > 10) {
        $ratio = ($missedCollection / $eligibleWars);
        $playerInfo['warning'] = ($ratio >= 0.5 and $ratio < 0.75);
        $playerInfo['ban'] = $ratio >= 0.75;
    } else {
        $playerInfo['warning'] = false;
        $playerInfo['ban'] = false;
    }

    if ($playerInfo['warning'] || $playerInfo['ban']) {
        $allBadStatus++;
    }
    array_push($finalPlayerList, $playerInfo);

    // Totaux pour tous les joueurs
    $allCollections += $totalCollection;
    $allCollectionsPlayed += $totalCollectionPlayed;
    $allCollectionsWon += $totalCollectionWon;
    $allMissedCollections += $missedCollection;
    $allCardsEarned += $totalCardsEarned;
    $allWars += $totalWar;
    $allBattlePlayed += $totalBattlesPlayed;
    $allBattleWon += $totalBattlesWon;
    $allMissedWar += $missedWar;
}
$lastUpdated = getLastUpdated($db, "war_stats");

//TODO gerer les saisons de guerre
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
                    $('#navbar').collapse('hide');
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

            $('#tx_search').on("keyup paste", function () {
                let value = $(this).val().toLowerCase();
                const playerLine = $('.playerTr');
                if (value.length < 3) {
                    playerLine.show();
                    return;
                }

                playerLine.each(function () {
                    if ($(this).next().val().toLowerCase().indexOf(value) < 0)
                        $(this).hide();
                });
            });

            let orderSelect = $('#orderSelect');
            orderSelect.change(function () {
                const val = $(this).val();
                let url = "war_stats.php", order;
                switch (val) {
                    case '1':
                        order = "?order=total_collection_played1";
                        break;
                    case '2':
                        order = "?order=total_collection_won2";
                        break;
                    case '3':
                        order = "?order=total_cards_earned3";
                        break;
                    case '4':
                        order = "?order=total_battle_played4";
                        break;
                    case '5':
                        order = "?order=total_battle_won5";
                        break;
                    default:
                        order = "";
                        break;
                }
                if (parseInt(val) >= 0) {
                    url = url + order;
                    window.location = url;
                }
            });

            orderSelect.val($('#hd_selectValue').val());
        });
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <?php if ($lastUpdated['updated'] != null):
        $time = strtotime($lastUpdated['updated']);
        ?>
        <span class="pageIndexSubtitle whiteShadow pull-right">Dernière mise à jour le : <b><?php echo '' . date('d/m/Y', $time) ?></b> à <b><?php echo '' . date('H:i', $time) ?></span>
    <?php else: ?>
        <span class="pageIndexSubtitle whiteShadow pull-right">Nécessite une mise à jour</span>
    <?php endif; ?>
    <h1 class="whiteShadow">Statistiques des guerres</h1>
    <span class="whiteShadow">Première guerre : <b><?php echo '' . date('d/m/Y', $firstWarDate['created']) ?></b></span>
    <br>
    <br><br>
    <!-- Nav tabs -->
    <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#collect" aria-controls="collect" role="tab" data-toggle="tab"
                                                  class="tab-link">Collections</a></li>
        <li role="presentation"><a href="#war" aria-controls="war" role="tab" data-toggle="tab" class="tab-link">Batailles</a>
        </li>
        <input type="hidden" id="hd_selectValue" value="<?php print $selectValue; ?>"/>
        <input type="text" id="tx_search" class="pull-right" placeholder="Trier par nom"/>
        <select id="orderSelect" class="pull-right">
            <option value="-1">Trier par colonne</option>
            <option value="0">Rang</option>
            <option value="1">Collections jouées</option>
            <option value="2">Collections gagnées</option>
            <option value="3">Cartes gagnées</option>
            <option value="4">Batailles jouées</option>
            <option value="5">Batailles gagnées</option>
        </select>
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
                            % victoire<br>
                            <?php echo ($allCollectionsPlayed != 0) ? round((($allCollectionsWon / $allCollectionsPlayed) * 100)) : '--'; ?>
                        </td>
                        <td class="whiteShadow text-center">Absences<br><?php echo $allMissedCollections; ?></td>
                        <td class="whiteShadow text-center">% présence<br>
                            <?php echo ($allCollections != 0) ? round(($allCollectionsPlayed / $allCollections) * 100) : '--'; ?>
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
            <div class="table-responsive">
                <table class="table js-player-table" id="tableIndex">
                    <tbody>
                    <?php foreach ($finalPlayerList as $player) : ?>
                        <tr class="pointerHand playerTr">
                            <td class="whiteShadow text-center rank">
                                <span><?php echo utf8_encode($player['rank']); ?></span></td>
                            <td class="whiteShadow"><a class="linkToPlayer"
                                                       href="player.php?tag=<?php echo $player['tag']; ?>">
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
                                <?php echo ($player['totalCollection'] != 0) ? round(($player['totalCollectionPlayed'] / $player['totalCollection']) * 100) . '%' : '--'; ?>
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
                        <input type="hidden" class="hd_playerName"
                               value="<?php print utf8_encode($player['name']); ?>"/>
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
                        <td class="whiteShadow text-center">% victoire<br>
                            <?php if ($allBattlePlayed != 0) echo round((($allBattleWon / $allBattlePlayed) * 100));
                            else echo '--'; ?>
                        </td>
                        <td class="whiteShadow text-center">Abscences<br><?php echo $allMissedWar; ?></td>
                        <td class="whiteShadow text-center">% présence<br>
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
                                                       href="player.php?tag=<?php echo $player['tag']; ?>">
                                    <?php echo utf8_encode($player['name']); ?></a></td>
                            <td class="whiteShadow text-center">jouées<br><?php echo $player['totalBattlesPlayed']; ?>
                            </td>
                            <td class="whiteShadow text-center">gagnées<br><?php echo $player['totalBattlesWon']; ?>
                            </td>
                            <td class="whiteShadow text-center">Victoires<br>
                                <?php echo ($player['totalBattlesPlayed'] != 0) ? round((($player['totalBattlesWon'] / $player['totalBattlesPlayed']) * 100)) . '%' : '-'; ?>
                            </td>
                            <td class="whiteShadow text-center">Absence<br><?php echo $player['missedWar'] ?></td>

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
                        <input type="hidden" class="hd_playerName"
                               value="<?php print utf8_encode($player['name']); ?>"/>
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