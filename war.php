<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 16:41
 */

include("tools/database.php");
include("tools/api_conf.php");

if (isset($_GET['order']) && !empty($_GET['order'])) {
    $order = $_GET['order'];
    $selectValue = substr($order, -1);
    $order = substr($order, 0, -1);
    $warPlayers = getWarPlayers($db, $order);
} else {
    $selectValue = -1;
    $warPlayers = getWarPlayers($db);
}
$lastUpdated = getLastUpdated($db, "war");

// API
$state = getWarStateFromApi($api);
if ($state == "collectionDay") {
    $stateName = "Jour de collection";
    $endTime = getWarFromApi($api)['collectionEndTime'];
} else {
    $stateName = "Jour de guerre";
    $standings = getAllStandings($db);
    $endTime = getWarFromApi($api)['warEndTime'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guerre en cours</title>
    <?php include("head.php"); ?>
    <script>
        $(document).ready(function () {
            $('#tableIndex').on('click', 'tbody td', function () {
                $("body").css("cursor", "wait");
                window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
            });

            $('#numberOfParticipant').html($('#hd_numberOfParticipants').val());
            $('#numberOfMissing').html($('#hd_numberOfMissing').val());
            $('#numberOfCollectionPlayed').html($('#hd_numberOfCollectionPlayed').val());
            $('#numberOfCollectionWon').html($('#hd_numberOfCollectionWon').val());
            $('#numberOfCardsEarned').html($('#hd_numberOfCardsEarned').val());

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
                let url = "war.php", order;
                switch (val) {
                    case '1':
                        order = "?order=collection_played1";
                        break;
                    case '2':
                        order = "?order=collection_won2";
                        break;
                    case '3':
                        order = "?order=cards_earned3";
                        break;
                    case '4':
                        order = "?order=battle_played4";
                        break;
                    case '5':
                        order = "?order=battle_won5";
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
    <div>
        <h1 class="whiteShadow">Guerre en cours</h1>
        <span class="whiteShadow"><?php echo $stateName ?></span><br>
        <span class="whiteShadow">Fin le <b><?php echo '' . date('d/m/Y', $endTime) ?></b> à <b><?php echo '' . date('H:i', $endTime) ?></b></span>
        <?php if ($state == 'warDay'): ?>
            <a href="war_decks.php" class="whiteShadow pull-right">Voir les decks de guerres utilisés</a>
        <?php endif; ?>
    </div>
    <br><br>
    <div>
        <span class="pageSubtitle whiteShadow">Résultats du clan</span>
        <select id="orderSelect" class="pull-right">
            <option value="-1">Trier par colonne</option>
            <option value="0">Rang</option>
            <option value="1">Collections jouées</option>
            <option value="2">Collections gagnées</option>
            <option value="3">Cartes gagnées</option>
            <option value="4">Batailles jouées</option>
            <option value="5">Batailles gagnées</option>
        </select>
        <input type="hidden" id="hd_selectValue" value="<?php print $selectValue; ?>"/>
    </div>
    <br>
    <?php
    if ($state == "warDay") { ?>
        <div class="divStandings table-responsive">
            <table class="table">
                <tbody>
                <?php
                if (isset($standings)) {
                    $pos = 1;
                    $lastBattlesWon = 0;
                    $lastCrowns = 0;
                    foreach ($standings as $clan) {
                        if ($lastCrowns == $clan['crowns'] && $lastBattlesWon == $clan['battles_won']) {
                            $pos--;
                        }
                        echo '<tr>';
                        echo '<td class="whiteShadow text-center rank"><span>' . $pos . '</span></td>';
                        echo '<td class="whiteShadow text-center">' . utf8_encode($clan['name']) . '</td>';
                        echo '<td class="whiteShadow text-center">Participants<br>' . $clan['participants'] . '</td>';
                        echo '<td class="whiteShadow text-center">Jouées<br>' . $clan['battles_played'] . '</td>';
                        echo '<td class="whiteShadow text-center">Gagnées<br>' . $clan['battles_won'] . '</td>';
                        echo '<td class="whiteShadow text-center">Couronnes<br>' . $clan['crowns'] . '</td>';
                        echo '<td class="whiteShadow text-center">Trophées<br>' . $clan['war_trophies'] . '</td>';
                        echo '</tr>';
                        echo '<input type="hidden" value=""/>';
                        $pos++;
                        $lastBattlesWon = $clan['battles_won'];
                        $lastCrowns = $clan['crowns'];
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <td class="whiteShadow text-center">Participants<br><span id="numberOfParticipant"></span></td>
                    <td class="whiteShadow text-center">Absents<br><span id="numberOfMissing"></span></td>
                    <td class="whiteShadow text-center">Jouées<br><span id="numberOfCollectionPlayed"></span></td>
                    <td class="whiteShadow text-center">Gagnées<br><span id="numberOfCollectionWon"></span></td>
                    <td class="whiteShadow text-center"><img src="images/ui/deck.png" height="35px"/>&nbsp;<span
                                id="numberOfCardsEarned"></span></td>
                </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <br>
    <div>
        <span class="pageSubtitle whiteShadow">Résultats par joueurs</span>
        <input type="text" id="tx_search" class="pull-right" placeholder="Trier par nom"/>
    </div>
    <br>
    <div class="divCurrentWar table-responsive">
        <table id="tableIndex" class="table">
            <thead>
            <tr class="rowIndex">
                <th class="text-center headIndex">Rang</th>
                <th class="headIndex">Joueur</th>
                <th class="text-center headIndex" colspan="3">Collections</th>
                <th class="text-center headIndex" colspan="2">Batailles</th>
            </tr>
            </thead>
            <tbody>
            <?php
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

                echo '<tr class="pointerHand playerTr">';
                echo '<td class="whiteShadow text-center rank"><span>' . utf8_encode($player['rank']) . '</span></td>';
                echo '<td class="whiteShadow"><a class="linkToPlayer" href="player.php?tag=' . $player['tag'] . '">' . utf8_encode($player['name']) . '</a></td>';
                echo '<td class="whiteShadow text-center">Jouées<br>' . $player['collection_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['collection_won'] . '</td>';
                echo '<td class="whiteShadow"><img src="images/ui/deck.png" height="35px"/>&nbsp;' . $player['cards'] . '</td>';
                echo '<td class="whiteShadow text-center">Jouées<br>' . $player['battle_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['battle_won'] . '</td>';
                echo '</tr>';
                echo '<input type="hidden" class="hd_playerName" value="' . utf8_encode($player['name']) . '"/>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php if ($state == "collectionDay") {
        $numberOfParticipant = intval(sizeof($warPlayers) - $minusParticipant); ?>
        <input type="hidden" id="hd_numberOfParticipants" value="<?php print $numberOfParticipant; ?>"/>
        <input type="hidden" id="hd_numberOfMissing" value="<?php print $minusParticipant; ?>"/>
        <input type="hidden" id="hd_numberOfCollectionPlayed" value="<?php print $totalCollectionPlayed; ?>"/>
        <input type="hidden" id="hd_numberOfCollectionWon" value="<?php print $totalCollectionWon; ?>"/>
        <input type="hidden" id="hd_numberOfCardsEarned" value="<?php print $totalCardsEarned; ?>"/>
    <?php } ?>
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
