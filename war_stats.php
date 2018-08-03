<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 10:56
 */

include("tools/database.php");

// TODO creer un graph avec les stats de guerre par jour de la semaine. get war_result group by war -> timestamp -> date -> day of the week

$allWarStats = getAllWarStats($db);
$seasons = array_unique(array_column($allWarStats, "season"));
rsort($seasons);

// Derniere saison
$lsAllCollections = 0;
$lsAllCollectionsPlayed = 0;
$lsAllCollectionsWon = 0;
$lsAllCardsEarned = 0;
$lsAllWars = 0;
$lsAllBattlePlayed = 0;
$lsAllBattleWon = 0;
$lsAllMissedCollections = 0;
$lsAllMissedWar = 0;
$lsAllBadStatus = 0;
$lsPlayerList = array();

// Avant derniere saison
$psAllCollections = 0;
$psAllCollectionsPlayed = 0;
$psAllCollectionsWon = 0;
$psAllCardsEarned = 0;
$psAllWars = 0;
$psAllBattlePlayed = 0;
$psAllBattleWon = 0;
$psAllMissedCollections = 0;
$psAllMissedWar = 0;
$psAllBadStatus = 0;
$psPlayerList = array();

// Avant avant derniere saison
$spsAllCollections = 0;
$spsAllCollectionsPlayed = 0;
$spsAllCollectionsWon = 0;
$spsAllCardsEarned = 0;
$spsAllWars = 0;
$spsAllBattlePlayed = 0;
$spsAllBattleWon = 0;
$spsAllMissedCollections = 0;
$spsAllMissedWar = 0;
$spsAllBadStatus = 0;
$spsPlayerList = array();

// Toutes les saisons
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
$playerList = array();

foreach ($allWarStats as $player) {
    $season = intval($player['season']);
    $playerInfo = array();
    $playerInfo['rank'] = $player['rank'];
    $playerInfo['tag'] = $player['tag'];
    $playerInfo['name'] = $player['name'];
    $playerInfo['totalCollectionPlayed'] = $totalCollectionPlayed = $player['total_collection_played'];
    $playerInfo['totalCollectionWon'] = $totalCollectionWon = $player['total_collection_won'];
    $playerInfo['totalCardsEarned'] = $totalCardsEarned = $player['total_cards_earned'];
    $playerInfo['totalBattlesPlayed'] = $totalBattlesPlayed = $player['total_battle_played'];
    $playerInfo['totalBattlesWon'] = $totalBattlesWon = $player['total_battle_won'];
    $missedCollection = countMissedCollection($db, $player['id'], $season)['missed_collection'];
    $playerInfo['missedCollection'] = $missedCollection == null ? 0 : $missedCollection;
    $missedWar = countMissedWar($db, $player['id'], $season)['missed_war'];
    $playerInfo['missedWar'] = $missedWar == null ? 0 : $missedWar;
    $playerInfo['totalCollection'] = $totalCollection = $totalCollectionPlayed + $missedCollection;
    $totalWar = $totalBattlesPlayed + $missedWar;
    $eligibleWars = getNumberOfEligibleWarByPlayerId($db, $player['id'], $season);

    if ($missedCollection >= 5) {
        $playerInfo['ban'] = true;
        $playerInfo['warning'] = false;
    } else if ($missedCollection >= 2) {
        $playerInfo['ban'] = false;
        $playerInfo['warning'] = true;
    } else if ($missedWar >= 2) {
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

    if ($season == intval($seasons[0])) {
        if ($playerInfo['warning'] || $playerInfo['ban']) {
            $lsAllBadStatus++;
        }
        array_push($lsPlayerList, $playerInfo);

        // Totaux pour tous les joueurs
        $lsAllCollections += $totalCollection;
        $lsAllCollectionsPlayed += $totalCollectionPlayed;
        $lsAllCollectionsWon += $totalCollectionWon;
        $lsAllMissedCollections += $missedCollection;
        $lsAllCardsEarned += $totalCardsEarned;
        $lsAllWars += $totalWar;
        $lsAllBattlePlayed += $totalBattlesPlayed;
        $lsAllBattleWon += $totalBattlesWon;
        $lsAllMissedWar += $missedWar;
    } else if ($season == intval($seasons[1])) {
        if ($playerInfo['warning'] || $playerInfo['ban']) {
            $psAllBadStatus++;
        }
        array_push($psPlayerList, $playerInfo);

        // Totaux pour tous les joueurs
        $psAllCollections += $totalCollection;
        $psAllCollectionsPlayed += $totalCollectionPlayed;
        $psAllCollectionsWon += $totalCollectionWon;
        $psAllMissedCollections += $missedCollection;
        $psAllCardsEarned += $totalCardsEarned;
        $psAllWars += $totalWar;
        $psAllBattlePlayed += $totalBattlesPlayed;
        $psAllBattleWon += $totalBattlesWon;
        $psAllMissedWar += $missedWar;
    } else if ($season == intval($seasons[2])) {
        if ($playerInfo['warning'] || $playerInfo['ban']) {
            $spsAllBadStatus++;
        }
        array_push($spsPlayerList, $playerInfo);

        // Totaux pour tous les joueurs
        $spsAllCollections += $totalCollection;
        $spsAllCollectionsPlayed += $totalCollectionPlayed;
        $spsAllCollectionsWon += $totalCollectionWon;
        $spsAllMissedCollections += $missedCollection;
        $spsAllCardsEarned += $totalCardsEarned;
        $spsAllWars += $totalWar;
        $spsAllBattlePlayed += $totalBattlesPlayed;
        $spsAllBattleWon += $totalBattlesWon;
        $spsAllMissedWar += $missedWar;
    } else if ($season == intval($seasons[3])) {
        if ($playerInfo['warning'] || $playerInfo['ban']) {
            $allBadStatus++;
        }
        array_push($playerList, $playerInfo);
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
}

$lastUpdated = getLastUpdated($db, "war_stats");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Historique des guerres</title>
    <?php include("head.php"); ?>
    <script>
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
                if (value.length === 0) {
                    playerLine.show();
                    return;
                }

                playerLine.each(function () {
                    if ($(this).next().val().toLowerCase().indexOf(value) < 0)
                        $(this).hide();
                    else
                        $(this).show();
                });
            });
        });
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Statistiques des guerres</h1>
    <br>
    <ul id="navUlWarSeason" class="nav nav-tabs" role="tablist">
        <?php
        print '<li role="presentation" class="active"><a href="#season' . $seasons[0] . '" aria-controls="season' . $seasons[0] . '" role="tab" data-toggle="tab" class="tab-link">Saison ' . $seasons[0] . '</a></li>';
        print '<li role="presentation"><a href="#season' . $seasons[1] . '" aria-controls="season' . $seasons[1] . '" role="tab" data-toggle="tab" class="tab-link">Saison ' . $seasons[1] . '</a></li>';
        print '<li role="presentation"><a href="#season' . $seasons[2] . '" aria-controls="season' . $seasons[2] . '" role="tab" data-toggle="tab" class="tab-link">Saison ' . $seasons[2] . '</a></li>';
        print '<li role="presentation"><a href="#allSeasons" aria-controls="allSeasons" role="tab" data-toggle="tab" class="tab-link">Toutes les guerres</a></li>';
        ?>
        <input type="text" id="tx_search" class="pull-right" placeholder="Trier par nom" style="margin-left: 10px;"/>
    </ul>
    <br>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="season<?php print $seasons[0]; ?>">
            <!-- Nav tabs -->
            <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#lastSeasonCollect" aria-controls="lastSeasonCollect"
                                                          role="tab"
                                                          data-toggle="tab"
                                                          class="tab-link">Collections</a></li>
                <li role="presentation"><a href="#lastSeasonWar" aria-controls="lastSeasonWar" role="tab"
                                           data-toggle="tab"
                                           class="tab-link">Batailles</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="lastSeasonCollect">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($lsPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $lsAllCollectionsPlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $lsAllCollectionsWon; ?></td>
                                <td class="whiteShadow text-center">
                                    % victoire<br>
                                    <?php echo ($lsAllCollectionsPlayed != 0) ? round((($lsAllCollectionsWon / $lsAllCollectionsPlayed) * 100)) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Absences<br><?php echo $lsAllMissedCollections; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php echo ($lsAllCollections != 0) ? round(($lsAllCollectionsPlayed / $lsAllCollections) * 100) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center"><img src="images/ui/deck.png"
                                                                         height="35px"/>&nbsp;<?php echo $lsAllCardsEarned; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $lsAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($lsPlayerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalCollectionPlayed']; ?></td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalCollectionWon']; ?></td>
                                    <td class="whiteShadow text-center">Victoires <br>
                                        <?php echo ($player['totalCollectionPlayed'] != 0) ? round((($player['totalCollectionWon'] / $player['totalCollectionPlayed']) * 100)) . '%' : '--'; ?>
                                    <td class="whiteShadow  text-center">
                                        Absence<br><?php echo $player['missedCollection'] ?></td>
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
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
                <div role="tabpanel" class="tab-pane" id="lastSeasonWar">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($lsPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">Jouées<br><?php echo $lsAllBattlePlayed; ?>
                                </td>
                                <td class="whiteShadow text-center">Gagnées<br><?php echo $lsAllBattleWon; ?>
                                </td>
                                <td class="whiteShadow text-center">% victoire<br>
                                    <?php if ($lsAllBattlePlayed != 0) echo round((($lsAllBattleWon / $lsAllBattlePlayed) * 100));
                                    else echo '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">Abscences<br><?php echo $lsAllMissedWar; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php if ($lsAllWars != 0) echo '' . round(($lsAllBattlePlayed / $lsAllWars) * 100);
                                    else echo '--'; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $lsAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($lsPlayerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalBattlesPlayed']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalBattlesWon']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Victoires<br>
                                        <?php echo ($player['totalBattlesPlayed'] != 0) ? round((($player['totalBattlesWon'] / $player['totalBattlesPlayed']) * 100)) . '%' : '-'; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Absence<br><?php echo $player['missedWar'] ?>
                                    </td>

                                    <!-- Status -->
                                    <?php if ($player['ban']) : ?>
                                        <td bgcolor="#D42F2F" class="text-center"><img src="images/ui/no-cancel.png"
                                                                                       height="35px"/></td>
                                    <?php elseif ($player['warning']): ?>
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
        </div>
        <div role="tabpanel" class="tab-pane" id="season<?php print $seasons[1]; ?>">
            <!-- Nav tabs -->
            <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#previousSeasonCollect"
                                                          aria-controls="previousSeasonCollect" role="tab"
                                                          data-toggle="tab"
                                                          class="tab-link">Collections</a></li>
                <li role="presentation"><a href="#previousSeasonWar" aria-controls="previousSeasonWar" role="tab"
                                           data-toggle="tab"
                                           class="tab-link">Batailles</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="previousSeasonCollect">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($psPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $psAllCollectionsPlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $psAllCollectionsWon; ?></td>
                                <td class="whiteShadow text-center">
                                    % victoire<br>
                                    <?php echo ($psAllCollectionsPlayed != 0) ? round((($psAllCollectionsWon / $psAllCollectionsPlayed) * 100)) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Absences<br><?php echo $psAllMissedCollections; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php echo ($psAllCollections != 0) ? round(($psAllCollectionsPlayed / $psAllCollections) * 100) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center"><img src="images/ui/deck.png"
                                                                         height="35px"/>&nbsp;<?php echo $psAllCardsEarned; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $psAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($psPlayerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalCollectionPlayed']; ?></td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalCollectionWon']; ?></td>
                                    <td class="whiteShadow text-center">Victoires <br>
                                        <?php echo ($player['totalCollectionPlayed'] != 0) ? round((($player['totalCollectionWon'] / $player['totalCollectionPlayed']) * 100)) . '%' : '--'; ?>
                                    <td class="whiteShadow  text-center">
                                        Absence<br><?php echo $player['missedCollection'] ?></td>
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
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
                <div role="tabpanel" class="tab-pane" id="previousSeasonWar">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($psPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $psAllBattlePlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $psAllBattleWon; ?></td>
                                <td class="whiteShadow text-center">% victoire<br>
                                    <?php if ($psAllBattlePlayed != 0) echo round((($psAllBattleWon / $psAllBattlePlayed) * 100));
                                    else echo '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Abscences<br><?php echo $psAllMissedWar; ?></td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php if ($psAllWars != 0) echo '' . round(($psAllBattlePlayed / $psAllWars) * 100);
                                    else echo '--'; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $psAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($psPlayerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalBattlesPlayed']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalBattlesWon']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Victoires<br>
                                        <?php echo ($player['totalBattlesPlayed'] != 0) ? round((($player['totalBattlesWon'] / $player['totalBattlesPlayed']) * 100)) . '%' : '-'; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Absence<br><?php echo $player['missedWar'] ?>
                                    </td>

                                    <!-- Status -->
                                    <?php if ($player['ban']) : ?>
                                        <td bgcolor="#D42F2F" class="text-center"><img src="images/ui/no-cancel.png"
                                                                                       height="35px"/></td>
                                    <?php elseif ($player['warning']): ?>
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
        </div>
        <div role="tabpanel" class="tab-pane" id="season<?php print $seasons[2]; ?>">
            <!-- Nav tabs -->
            <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#secondPreviousSeasonCollect"
                                                          aria-controls="secondPreviousSeasonCollect" role="tab"
                                                          data-toggle="tab"
                                                          class="tab-link">Collections</a></li>
                <li role="presentation"><a href="#secondPreviousSeasonWar" aria-controls="secondPreviousSeasonWar"
                                           role="tab" data-toggle="tab"
                                           class="tab-link">Batailles</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="secondPreviousSeasonCollect">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($spsPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $spsAllCollectionsPlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $spsAllCollectionsWon; ?></td>
                                <td class="whiteShadow text-center">
                                    % victoire<br>
                                    <?php echo ($spsAllCollectionsPlayed != 0) ? round((($spsAllCollectionsWon / $spsAllCollectionsPlayed) * 100)) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Absences<br><?php echo $spsAllMissedCollections; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php echo ($spsAllCollections != 0) ? round(($spsAllCollectionsPlayed / $spsAllCollections) * 100) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center"><img src="images/ui/deck.png"
                                                                         height="35px"/>&nbsp;<?php echo $spsAllCardsEarned; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $spsAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($spsPlayerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalCollectionPlayed']; ?></td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalCollectionWon']; ?></td>
                                    <td class="whiteShadow text-center">Victoires <br>
                                        <?php echo ($player['totalCollectionPlayed'] != 0) ? round((($player['totalCollectionWon'] / $player['totalCollectionPlayed']) * 100)) . '%' : '--'; ?>
                                    <td class="whiteShadow  text-center">
                                        Absence<br><?php echo $player['missedCollection'] ?></td>
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
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
                <div role="tabpanel" class="tab-pane" id="secondPreviousSeasonWar">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($spsPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $spsAllBattlePlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $spsAllBattleWon; ?></td>
                                <td class="whiteShadow text-center">% victoire<br>
                                    <?php if ($spsAllBattlePlayed != 0) echo round((($spsAllBattleWon / $spsAllBattlePlayed) * 100));
                                    else echo '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Abscences<br><?php echo $spsAllMissedWar; ?></td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php if ($spsAllWars != 0) echo '' . round(($spsAllBattlePlayed / $spsAllWars) * 100);
                                    else echo '--'; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $spsAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($spsPlayerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalBattlesPlayed']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalBattlesWon']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Victoires<br>
                                        <?php echo ($player['totalBattlesPlayed'] != 0) ? round((($player['totalBattlesWon'] / $player['totalBattlesPlayed']) * 100)) . '%' : '-'; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Absence<br><?php echo $player['missedWar'] ?>
                                    </td>

                                    <!-- Status -->
                                    <?php if ($player['ban']) : ?>
                                        <td bgcolor="#D42F2F" class="text-center"><img src="images/ui/no-cancel.png"
                                                                                       height="35px"/></td>
                                    <?php elseif ($player['warning']): ?>
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
        </div>
        <div role="tabpanel" class="tab-pane" id="allSeasons">
            <!-- Nav tabs -->
            <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#allSeasonsCollect"
                                                          aria-controls="allSeasonsCollect" role="tab"
                                                          data-toggle="tab"
                                                          class="tab-link">Collections</a></li>
                <li role="presentation"><a href="#allSeasonsWar" aria-controls="allSeasonsWar"
                                           role="tab" data-toggle="tab"
                                           class="tab-link">Batailles</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="allSeasonsCollect">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($playerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $allCollectionsPlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $allCollectionsWon; ?></td>
                                <td class="whiteShadow text-center">
                                    % victoire<br>
                                    <?php echo ($allCollectionsPlayed != 0) ? round((($allCollectionsWon / $allCollectionsPlayed) * 100)) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Absences<br><?php echo $allMissedCollections; ?>
                                </td>
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
                            <?php foreach ($playerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalCollectionPlayed']; ?></td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalCollectionWon']; ?></td>
                                    <td class="whiteShadow text-center">Victoires <br>
                                        <?php echo ($player['totalCollectionPlayed'] != 0) ? round((($player['totalCollectionWon'] / $player['totalCollectionPlayed']) * 100)) . '%' : '--'; ?>
                                    <td class="whiteShadow  text-center">
                                        Absence<br><?php echo $player['missedCollection'] ?></td>
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
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
                <div role="tabpanel" class="tab-pane" id="allSeasonsWar">
                    <div class="table-responsive">
                        <table class="table" id="tableIndex">
                            <tbody>
                            <tr>
                                <td class="whiteShadow text-center">
                                    Joueurs<br><?php echo sizeof($playerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $allBattlePlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $allBattleWon; ?></td>
                                <td class="whiteShadow text-center">% victoire<br>
                                    <?php if ($allBattlePlayed != 0) echo round((($allBattleWon / $allBattlePlayed) * 100));
                                    else echo '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Abscences<br><?php echo $allMissedWar; ?></td>
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
                            <?php foreach ($playerList as $player) : ?>
                                <tr class="pointerHand playerTr">
                                    <td class="whiteShadow text-center rank">
                                        <span><?php echo utf8_encode($player['rank']); ?></span></td>
                                    <td class="whiteShadow"><a class="linkToPlayer"
                                                               href="player.php?tag=<?php echo $player['tag']; ?>"><?php echo utf8_encode($player['name']); ?></a>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        jouées<br><?php echo $player['totalBattlesPlayed']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">
                                        gagnées<br><?php echo $player['totalBattlesWon']; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Victoires<br>
                                        <?php echo ($player['totalBattlesPlayed'] != 0) ? round((($player['totalBattlesWon'] / $player['totalBattlesPlayed']) * 100)) . '%' : '-'; ?>
                                    </td>
                                    <td class="whiteShadow text-center">Absence<br><?php echo $player['missedWar'] ?>
                                    </td>

                                    <!-- Status -->
                                    <?php if ($player['ban']) : ?>
                                        <td bgcolor="#D42F2F" class="text-center"><img src="images/ui/no-cancel.png"
                                                                                       height="35px"/></td>
                                    <?php elseif ($player['warning']): ?>
                                        <td bgcolor="#FFB732" class="text-center"><img src="images/ui/watch.png"
                                                                                       height="35px"/></td>
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
        </div>
    </div>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="images/loader.gif"/>
</div>
<div class="row text-center">
    <?php if ($lastUpdated['updated'] != null):
        $time = strtotime($lastUpdated['updated']);
        ?>
        <span class="whiteShadow">Dernière mise à jour le : <b><?php echo '' . date('d/m/Y', $time) ?></b> à <b><?php echo '' . date('H:i', $time) ?></span>
    <?php else: ?>
        <span class="whiteShadow">Nécessite une mise à jour</span>
    <?php endif; ?>
</div>
<?php include("footer.html"); ?>
</body>
</html>