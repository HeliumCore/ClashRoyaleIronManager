<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 10:56
 */

include("tools/database.php");

$lastSeason = getLastSeason($db);
$previousSeason = ($lastSeason - 1);
$secondPreviousSeason = ($lastSeason - 2);

if (isset($_GET['order']) && !empty($_GET['order'])) {
    $order = $_GET['order'];
    $selectValue = substr($order, -1);
    $order = substr($order, 0, -1);
//    $allWarStats = getAllWarStats($db, $order);
    $lastWarStatsByPlayer = getWarStats($db, $lastSeason, $order);
    $previousWarStatsByPlayer = getWarStats($db, $previousSeason, $order);
    $secondPreviousWarStatsByPlayer = getWarStats($db, $secondPreviousSeason, $order);
} else {
    $selectValue = -1;
//    $allWarStats = getAllWarStats($db);
    $lastWarStatsByPlayer = getWarStats($db, $lastSeason);
    $previousWarStatsByPlayer = getWarStats($db, $previousSeason);
    $secondPreviousWarStatsByPlayer = getWarStats($db, $secondPreviousSeason);
}

$allPlayers = getAllPlayersByRank($db);
// TODO creer un graph avec les stats de guerre par jour de la semaine. get war_result group by war -> timestamp -> date -> day of the week
// TODO faire une seul requete qui choppe absolument tout - derniere page

// TODO faire un tableau d'en-tete qui regroupe toutes les guerres pour tous les joueurs
// cumuler dans les trois foreach pour avoir les valeurs
//$allCollections = 0;
//$allCollectionsPlayed = 0;
//$allCollectionsWon = 0;
//$allCardsEarned = 0;
//$allWars = 0;
//$allBattlePlayed = 0;
//$allBattleWon = 0;
//$allMissedCollections = 0;
//$allMissedWar = 0;
//$allBadStatus = 0;

$lastSeasonAllCollections = 0;
$lastSeasonAllCollectionsPlayed = 0;
$lastSeasonAllCollectionsWon = 0;
$lastSeasonAllCardsEarned = 0;
$lastSeasonAllWars = 0;
$lastSeasonAllBattlePlayed = 0;
$lastSeasonAllBattleWon = 0;
$lastSeasonAllMissedCollections = 0;
$lastSeasonAllMissedWar = 0;
$lastSeasonAllBadStatus = 0;
$lastSeasonFinalPlayerList = array();
foreach ($lastWarStatsByPlayer as $player) {
    $playerInfo = array();
    $playerInfo['rank'] = $player['rank'];
    $playerInfo['tag'] = $player['tag'];
    $playerInfo['name'] = $player['name'];
    $playerInfo['totalCollectionPlayed'] = $totalCollectionPlayed = $player['total_collection_played'];
    $playerInfo['totalCollectionWon'] = $totalCollectionWon = $player['total_collection_won'];
    $playerInfo['totalCardsEarned'] = $totalCardsEarned = $player['total_cards_earned'];
    $playerInfo['totalBattlesPlayed'] = $totalBattlesPlayed = $player['total_battle_played'];
    $playerInfo['totalBattlesWon'] = $totalBattlesWon = $player['total_battle_won'];
    $missedCollection = countMissedCollection($db, $player['id'], $lastSeason)['missed_collection'];
    $playerInfo['missedCollection'] = $missedCollection == null ? 0 : $missedCollection;
    $missedWar = countMissedWar($db, $player['id'], $lastSeason)['missed_war'];
    $playerInfo['missedWar'] = $missedWar == null ? 0 : $missedWar;
    $playerInfo['totalCollection'] = $totalCollection = $totalCollectionPlayed + $missedCollection;
    $totalWar = $totalBattlesPlayed + $missedWar;
    $eligibleWars = getNumberOfEligibleWarByPlayerId($db, $player['id'], $lastSeason);
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

    if ($playerInfo['warning'] || $playerInfo['ban']) {
        $lastSeasonAllBadStatus++;
    }
    array_push($lastSeasonFinalPlayerList, $playerInfo);

    // Totaux pour tous les joueurs
    $lastSeasonAllCollections += $totalCollection;
    $lastSeasonAllCollectionsPlayed += $totalCollectionPlayed;
    $lastSeasonAllCollectionsWon += $totalCollectionWon;
    $lastSeasonAllMissedCollections += $missedCollection;
    $lastSeasonAllCardsEarned += $totalCardsEarned;
    $lastSeasonAllWars += $totalWar;
    $lastSeasonAllBattlePlayed += $totalBattlesPlayed;
    $lastSeasonAllBattleWon += $totalBattlesWon;
    $lastSeasonAllMissedWar += $missedWar;
}

$previousSeasonAllCollections = 0;
$previousSeasonAllCollectionsPlayed = 0;
$previousSeasonAllCollectionsWon = 0;
$previousSeasonAllCardsEarned = 0;
$previousSeasonAllWars = 0;
$previousSeasonAllBattlePlayed = 0;
$previousSeasonAllBattleWon = 0;
$previousSeasonAllMissedCollections = 0;
$previousSeasonAllMissedWar = 0;
$previousSeasonAllBadStatus = 0;
$previousSeasonFinalPlayerList = array();
foreach ($previousWarStatsByPlayer as $player) {
    $playerInfo = array();
    $playerInfo['rank'] = $player['rank'];
    $playerInfo['tag'] = $player['tag'];
    $playerInfo['name'] = $player['name'];
    $playerInfo['totalCollectionPlayed'] = $totalCollectionPlayed = $player['total_collection_played'];
    $playerInfo['totalCollectionWon'] = $totalCollectionWon = $player['total_collection_won'];
    $playerInfo['totalCardsEarned'] = $totalCardsEarned = $player['total_cards_earned'];
    $playerInfo['totalBattlesPlayed'] = $totalBattlesPlayed = $player['total_battle_played'];
    $playerInfo['totalBattlesWon'] = $totalBattlesWon = $player['total_battle_won'];
    $missedCollection = countMissedCollection($db, $player['id'], $previousSeason)['missed_collection'];
    $playerInfo['missedCollection'] = $missedCollection == null ? 0 : $missedCollection;
    $missedWar = countMissedWar($db, $player['id'], $previousSeason)['missed_war'];
    $playerInfo['missedWar'] = $missedWar == null ? 0 : $missedWar;
    $playerInfo['totalCollection'] = $totalCollection = $totalCollectionPlayed + $missedCollection;
    $totalWar = $totalBattlesPlayed + $missedWar;
    $eligibleWars = getNumberOfEligibleWarByPlayerId($db, $player['id'], $previousSeason);
    if ($missedWar >= 2) {
        $playerInfo['ban'] = true;
        $playerInfo['warning'] = false;
    } else if ($missedWar == 1) {
        $playerInfo['ban'] = false;
        $playerInfo['warning'] = true;
    } else if ($eligibleWars > 5) {
        $ratio = ($missedCollection / $eligibleWars);
        $playerInfo['warning'] = ($ratio >= 0.5 and $ratio < 0.75);
        $playerInfo['ban'] = $ratio >= 0.75;
    } else {
        $playerInfo['warning'] = false;
        $playerInfo['ban'] = false;
    }

    if ($playerInfo['warning'] || $playerInfo['ban']) {
        $previousSeasonAllBadStatus++;
    }
    array_push($previousSeasonFinalPlayerList, $playerInfo);

    // Totaux pour tous les joueurs
    $previousSeasonAllCollections += $totalCollection;
    $previousSeasonAllCollectionsPlayed += $totalCollectionPlayed;
    $previousSeasonAllCollectionsWon += $totalCollectionWon;
    $previousSeasonAllMissedCollections += $missedCollection;
    $previousSeasonAllCardsEarned += $totalCardsEarned;
    $previousSeasonAllWars += $totalWar;
    $previousSeasonAllBattlePlayed += $totalBattlesPlayed;
    $previousSeasonAllBattleWon += $totalBattlesWon;
    $previousSeasonAllMissedWar += $missedWar;
}


$secondPreviousSeasonAllCollections = 0;
$secondPreviousSeasonAllCollectionsPlayed = 0;
$secondPreviousSeasonAllCollectionsWon = 0;
$secondPreviousSeasonAllCardsEarned = 0;
$secondPreviousSeasonAllWars = 0;
$secondPreviousSeasonAllBattlePlayed = 0;
$secondPreviousSeasonAllBattleWon = 0;
$secondPreviousSeasonAllMissedCollections = 0;
$secondPreviousSeasonAllMissedWar = 0;
$secondPreviousSeasonAllBadStatus = 0;
$secondPreviousSeasonFinalPlayerList = array();
foreach ($secondPreviousWarStatsByPlayer as $player) {
    $playerInfo = array();
    $playerInfo['rank'] = $player['rank'];
    $playerInfo['tag'] = $player['tag'];
    $playerInfo['name'] = $player['name'];
    $playerInfo['totalCollectionPlayed'] = $totalCollectionPlayed = $player['total_collection_played'];
    $playerInfo['totalCollectionWon'] = $totalCollectionWon = $player['total_collection_won'];
    $playerInfo['totalCardsEarned'] = $totalCardsEarned = $player['total_cards_earned'];
    $playerInfo['totalBattlesPlayed'] = $totalBattlesPlayed = $player['total_battle_played'];
    $playerInfo['totalBattlesWon'] = $totalBattlesWon = $player['total_battle_won'];
    $missedCollection = countMissedCollection($db, $player['id'], $secondPreviousSeason)['missed_collection'];
    $playerInfo['missedCollection'] = $missedCollection == null ? 0 : $missedCollection;
    $missedWar = countMissedWar($db, $player['id'], $secondPreviousSeason)['missed_war'];
    $playerInfo['missedWar'] = $missedWar == null ? 0 : $missedWar;
    $playerInfo['totalCollection'] = $totalCollection = $totalCollectionPlayed + $missedCollection;
    $totalWar = $totalBattlesPlayed + $missedWar;
    $eligibleWars = getNumberOfEligibleWarByPlayerId($db, $player['id'], $secondPreviousSeason);
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
        $secondPreviousSeasonAllBadStatus++;
    }
    array_push($secondPreviousSeasonFinalPlayerList, $playerInfo);

    // Totaux pour tous les joueurs
    $secondPreviousSeasonAllCollections += $totalCollection;
    $secondPreviousSeasonAllCollectionsPlayed += $totalCollectionPlayed;
    $secondPreviousSeasonAllCollectionsWon += $totalCollectionWon;
    $secondPreviousSeasonAllMissedCollections += $missedCollection;
    $secondPreviousSeasonAllCardsEarned += $totalCardsEarned;
    $secondPreviousSeasonAllWars += $totalWar;
    $secondPreviousSeasonAllBattlePlayed += $totalBattlesPlayed;
    $secondPreviousSeasonAllBattleWon += $totalBattlesWon;
    $secondPreviousSeasonAllMissedWar += $missedWar;
}
//
//$lastSeason = 999999;
//$counter = 0;
//foreach ($allWarStats as $player) {
//    if ($lastSeason > $player['season']) {
//        $counter++;
//        $lastSeason = $player['season'];
//    }
//
//    $playerInfo = array();
//    $playerInfo['rank'] = $player['rank'];
//    $playerInfo['tag'] = $player['tag'];
//    $playerInfo['name'] = $player['name'];
//    $playerInfo['totalCollectionPlayed'] = $totalCollectionPlayed = $player['total_collection_played'];
//    $playerInfo['totalCollectionWon'] = $totalCollectionWon = $player['total_collection_won'];
//    $playerInfo['totalCardsEarned'] = $totalCardsEarned = $player['total_cards_earned'];
//    $playerInfo['totalBattlesPlayed'] = $totalBattlesPlayed = $player['total_battle_played'];
//    $playerInfo['totalBattlesWon'] = $totalBattlesWon = $player['total_battle_won'];
//    $missedCollection = countMissedCollection($db, $player['id'], $secondPreviousSeason)['missed_collection'];
//    $playerInfo['missedCollection'] = $missedCollection == null ? 0 : $missedCollection;
//    $missedWar = countMissedWar($db, $player['id'], $secondPreviousSeason)['missed_war'];
//    $playerInfo['missedWar'] = $missedWar == null ? 0 : $missedWar;
//    $playerInfo['totalCollection'] = $totalCollection = $totalCollectionPlayed + $missedCollection;
//    $totalWar = $totalBattlesPlayed + $missedWar;
//    $eligibleWars = getNumberOfEligibleWarByPlayerId($db, $player['id'], $secondPreviousSeason);
//
//    if ($missedWar >= 2) {
//        $playerInfo['ban'] = true;
//        $playerInfo['warning'] = false;
//    } else if ($missedWar == 1) {
//        $playerInfo['ban'] = false;
//        $playerInfo['warning'] = true;
//    } else if ($eligibleWars > 10) {
//        $ratio = ($missedCollection / $eligibleWars);
//        $playerInfo['warning'] = ($ratio >= 0.5 and $ratio < 0.75);
//        $playerInfo['ban'] = $ratio >= 0.75;
//    } else {
//        $playerInfo['warning'] = false;
//        $playerInfo['ban'] = false;
//    }
//
//
//    if ($counter == 1) {
//        if ($playerInfo['warning'] || $playerInfo['ban'])
//            $lastSeasonAllBadStatus++;
//
//        $lastSeasonAllCollections += $totalCollection;
//        $lastSeasonAllCollectionsPlayed += $totalCollectionPlayed;
//        $lastSeasonAllCollectionsWon += $totalCollectionWon;
//        $lastSeasonAllMissedCollections += $missedCollection;
//        $lastSeasonAllCardsEarned += $totalCardsEarned;
//        $lastSeasonAllWars += $totalWar;
//        $lastSeasonAllBattlePlayed += $totalBattlesPlayed;
//        $lastSeasonAllBattleWon += $totalBattlesWon;
//        $lastSeasonAllMissedWar += $missedWar;
//        array_push($lastSeasonFinalPlayerList, $playerInfo);
//    } else if ($counter == 2) {
//        if ($playerInfo['warning'] || $playerInfo['ban'])
//            $previousSeasonAllBadStatus++;
//
//        $previousSeasonAllCollections += $totalCollection;
//        $previousSeasonAllCollectionsPlayed += $totalCollectionPlayed;
//        $previousSeasonAllCollectionsWon += $totalCollectionWon;
//        $previousSeasonAllMissedCollections += $missedCollection;
//        $previousSeasonAllCardsEarned += $totalCardsEarned;
//        $previousSeasonAllWars += $totalWar;
//        $previousSeasonAllBattlePlayed += $totalBattlesPlayed;
//        $previousSeasonAllBattleWon += $totalBattlesWon;
//        $previousSeasonAllMissedWar += $missedWar;
//        array_push($previousSeasonFinalPlayerList, $playerInfo);
//    } else if ($counter == 3) {
//        if ($playerInfo['warning'] || $playerInfo['ban'])
//            $secondPreviousSeasonAllBadStatus++;
//
//        $secondPreviousSeasonAllCollections += $totalCollection;
//        $secondPreviousSeasonAllCollectionsPlayed += $totalCollectionPlayed;
//        $secondPreviousSeasonAllCollectionsWon += $totalCollectionWon;
//        $secondPreviousSeasonAllMissedCollections += $missedCollection;
//        $secondPreviousSeasonAllCardsEarned += $totalCardsEarned;
//        $secondPreviousSeasonAllWars += $totalWar;
//        $secondPreviousSeasonAllBattlePlayed += $totalBattlesPlayed;
//        $secondPreviousSeasonAllBattleWon += $totalBattlesWon;
//        $secondPreviousSeasonAllMissedWar += $missedWar;
//        array_push($secondPreviousSeasonFinalPlayerList, $playerInfo);
//    }
//}

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
    <h1 class="whiteShadow">Statistiques des guerres</h1>
    <br>
    <ul id="navUlWarSeason" class="nav nav-tabs" role="tablist">
        <?php
        print '<li role="presentation" class="active"><a href="#season' . $lastSeason . '" aria-controls="season' . $lastSeason . '" role="tab" data-toggle="tab" class="tab-link">Saison ' . $lastSeason . '</a></li>';
        print '<li role="presentation"><a href="#season' . $previousSeason . '" aria-controls="season' . $previousSeason . '" role="tab" data-toggle="tab" class="tab-link">Saison ' . $previousSeason . '</a></li>';
        print '<li role="presentation"><a href="#season' . $secondPreviousSeason . '" aria-controls="season' . $secondPreviousSeason . '" role="tab" data-toggle="tab" class="tab-link">Saison ' . $secondPreviousSeason . '</a></li>';
        ?>
        <input type="hidden" id="hd_selectValue" value="<?php print $selectValue; ?>"/>
        <input type="text" id="tx_search" class="pull-right" placeholder="Trier par nom" style="margin-left: 10px;"/>
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
    <br>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="season<?php print $lastSeason; ?>">
            <!-- Nav tabs -->
            <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#lastSeasonCollect" aria-controls="lastSeasonCollect" role="tab"
                                                          data-toggle="tab"
                                                          class="tab-link">Collections</a></li>
                <li role="presentation"><a href="#lastSeasonWar" aria-controls="lastSeasonWar" role="tab" data-toggle="tab"
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
                                    Joueurs<br><?php echo sizeof($lastSeasonFinalPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $lastSeasonAllCollectionsPlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $lastSeasonAllCollectionsWon; ?></td>
                                <td class="whiteShadow text-center">
                                    % victoire<br>
                                    <?php echo ($lastSeasonAllCollectionsPlayed != 0) ? round((($lastSeasonAllCollectionsWon / $lastSeasonAllCollectionsPlayed) * 100)) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Absences<br><?php echo $lastSeasonAllMissedCollections; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php echo ($lastSeasonAllCollections != 0) ? round(($lastSeasonAllCollectionsPlayed / $lastSeasonAllCollections) * 100) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center"><img src="images/ui/deck.png"
                                                                         height="35px"/>&nbsp;<?php echo $lastSeasonAllCardsEarned; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $lastSeasonAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($lastSeasonFinalPlayerList as $player) : ?>
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
                                    Joueurs<br><?php echo sizeof($lastSeasonFinalPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">Jouées<br><?php echo $lastSeasonAllBattlePlayed; ?>
                                </td>
                                <td class="whiteShadow text-center">Gagnées<br><?php echo $lastSeasonAllBattleWon; ?>
                                </td>
                                <td class="whiteShadow text-center">% victoire<br>
                                    <?php if ($lastSeasonAllBattlePlayed != 0) echo round((($lastSeasonAllBattleWon / $lastSeasonAllBattlePlayed) * 100));
                                    else echo '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">Abscences<br><?php echo $lastSeasonAllMissedWar; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php if ($lastSeasonAllWars != 0) echo '' . round(($lastSeasonAllBattlePlayed / $lastSeasonAllWars) * 100);
                                    else echo '--'; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $lastSeasonAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($lastSeasonFinalPlayerList as $player) : ?>
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
        <div role="tabpanel" class="tab-pane" id="season<?php print $previousSeason; ?>">
            <!-- Nav tabs -->
            <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#previousSeasonCollect" aria-controls="previousSeasonCollect" role="tab"
                                                          data-toggle="tab"
                                                          class="tab-link">Collections</a></li>
                <li role="presentation"><a href="#previousSeasonWar" aria-controls="previousSeasonWar" role="tab" data-toggle="tab"
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
                                    Joueurs<br><?php echo sizeof($previousSeasonFinalPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $previousSeasonAllCollectionsPlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $previousSeasonAllCollectionsWon; ?></td>
                                <td class="whiteShadow text-center">
                                    % victoire<br>
                                    <?php echo ($previousSeasonAllCollectionsPlayed != 0) ? round((($previousSeasonAllCollectionsWon / $previousSeasonAllCollectionsPlayed) * 100)) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Absences<br><?php echo $previousSeasonAllMissedCollections; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php echo ($previousSeasonAllCollections != 0) ? round(($previousSeasonAllCollectionsPlayed / $previousSeasonAllCollections) * 100) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center"><img src="images/ui/deck.png"
                                                                         height="35px"/>&nbsp;<?php echo $previousSeasonAllCardsEarned; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $previousSeasonAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($previousSeasonFinalPlayerList as $player) : ?>
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
                                    Joueurs<br><?php echo sizeof($lastSeasonFinalPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $previousSeasonAllBattlePlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $previousSeasonAllBattleWon; ?></td>
                                <td class="whiteShadow text-center">% victoire<br>
                                    <?php if ($previousSeasonAllBattlePlayed != 0) echo round((($previousSeasonAllBattleWon / $previousSeasonAllBattlePlayed) * 100));
                                    else echo '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Abscences<br><?php echo $previousSeasonAllMissedWar; ?></td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php if ($previousSeasonAllWars != 0) echo '' . round(($previousSeasonAllBattlePlayed / $previousSeasonAllWars) * 100);
                                    else echo '--'; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $previousSeasonAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($previousSeasonFinalPlayerList as $player) : ?>
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
        <div role="tabpanel" class="tab-pane" id="season<?php print $secondPreviousSeason; ?>">
            <!-- Nav tabs -->
            <ul id="navUlWarStats" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#secondPreviousSeasonCollect" aria-controls="secondPreviousSeasonCollect" role="tab"
                                                          data-toggle="tab"
                                                          class="tab-link">Collections</a></li>
                <li role="presentation"><a href="#secondPreviousSeasonWar" aria-controls="secondPreviousSeasonWar" role="tab" data-toggle="tab"
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
                                    Joueurs<br><?php echo sizeof($secondPreviousSeasonFinalPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $secondPreviousSeasonAllCollectionsPlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $secondPreviousSeasonAllCollectionsWon; ?></td>
                                <td class="whiteShadow text-center">
                                    % victoire<br>
                                    <?php echo ($secondPreviousSeasonAllCollectionsPlayed != 0) ? round((($secondPreviousSeasonAllCollectionsWon / $secondPreviousSeasonAllCollectionsPlayed) * 100)) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Absences<br><?php echo $secondPreviousSeasonAllMissedCollections; ?>
                                </td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php echo ($secondPreviousSeasonAllCollections != 0) ? round(($secondPreviousSeasonAllCollectionsPlayed / $secondPreviousSeasonAllCollections) * 100) : '--'; ?>
                                </td>
                                <td class="whiteShadow text-center"><img src="images/ui/deck.png"
                                                                         height="35px"/>&nbsp;<?php echo $secondPreviousSeasonAllCardsEarned; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $secondPreviousSeasonAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($secondPreviousSeasonFinalPlayerList as $player) : ?>
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
                                    Joueurs<br><?php echo sizeof($lastSeasonFinalPlayerList); ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Jouées<br><?php echo $secondPreviousSeasonAllBattlePlayed; ?></td>
                                <td class="whiteShadow text-center">
                                    Gagnées<br><?php echo $secondPreviousSeasonAllBattleWon; ?></td>
                                <td class="whiteShadow text-center">% victoire<br>
                                    <?php if ($secondPreviousSeasonAllBattlePlayed != 0) echo round((($secondPreviousSeasonAllBattleWon / $secondPreviousSeasonAllBattlePlayed) * 100));
                                    else echo '--'; ?>
                                </td>
                                <td class="whiteShadow text-center">
                                    Abscences<br><?php echo $secondPreviousSeasonAllMissedWar; ?></td>
                                <td class="whiteShadow text-center">% présence<br>
                                    <?php if ($secondPreviousSeasonAllWars != 0) echo '' . round(($secondPreviousSeasonAllBattlePlayed / $secondPreviousSeasonAllWars) * 100);
                                    else echo '--'; ?>
                                </td>
                                <td bgcolor="#D42F2F"><span class="whiteShadow text-center"
                                                            style="display:block;width: 41px;margin:auto"><?php echo $secondPreviousSeasonAllBadStatus; ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table js-player-table" id="tableIndex">
                            <tbody>
                            <?php foreach ($secondPreviousSeasonFinalPlayerList as $player) : ?>
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