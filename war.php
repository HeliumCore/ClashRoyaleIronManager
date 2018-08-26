<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 26/08/2018
 * Time: 15:04
 */

include(__DIR__ . "/tools/database.php");
include(__DIR__ . "/tools/api_conf.php");
include_once(__DIR__ . "/check_login.php");


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
$warNumber = getWarNumber($db);

// API
$war = getWarFromApi($api);
$state = $war['state'];
if ($state == "collectionDay") {
    $stateName = "Jour de collection";
    $endTime = $war['collectionEndTime'];
} else if ($state == "warDay") {
    $stateName = "Jour de guerre";
    $standings = getAllStandings($db);
    $endTime = $war['warEndTime'];
}
$currentTrophies = $war['clan']['warTrophies'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Iron - Guerre en cours</title>
    <?php include("head.php"); ?>
    <script>
        $(document).ready(function () {
            $('#playersTable').on('click', 'tbody td', function () {
                $("body").css("cursor", "wait");
                window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
            });

            $('#numberOfParticipant').html($('#hd_numberOfParticipants').val());
            $('#numberOfMissing').html($('#hd_numberOfMissing').val());
            $('#numberOfCollectionPlayed').html($('#hd_numberOfCollectionPlayed').val());
            $('#numberOfCollectionWon').html($('#hd_numberOfCollectionWon').val());
            $('#numberOfCardsEarned').html("&nbsp;".concat($('#hd_numberOfCardsEarned').val()));

            $('.clan-rank').each(function() {
                let pos = $(this).data('pos');
                if (pos === 1) {
                    $(this).addClass("first-place");
                } else if (pos === 2) {
                    $(this).addClass("second-place");
                } else if (pos === 3) {
                    $(this).addClass("third-place");
                } else {
                    $(this).addClass("last-place");
                }
            });

            // TODO refaire le tri et la recherche par joueur

        });
    </script>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <?php if ($state == "notInWar"): ?>
        <div class="row text-center">
            <h3 class="whiteShadow">Il n'y a pas de guerre en cours</h3>
        </div>
    <?php elseif ($state == "collectionDay" || $state == "warDay"): ?>
        <div class="war-badge badge-div">
            <div class="pull-right whiteShadow trophy-div hideOnUpdate">
                <img src="/images/ui/clan-trophies.png" height="30px" class="clan-trophies-img"/>
                <span><?php print $currentTrophies; ?></span>
            </div>
            <div class="flex">
                <h1 class="whiteShadow">
                    <?php print "Guerre n°" . $warNumber; ?><br>
                    <span class="small whiteShadow"><?php print $stateName; ?></span>
                </h1>
            </div>
            <div>
                <span class="whiteShadow">
                    Fin le <b><?php echo '' . date('d/m/Y', $endTime) ?></b> à <b><?php echo '' . date('H:i', $endTime) ?></b>
                    <?php
                    if ($state == "warDay"):
                        print '<br><a href="/war_decks">Voir les decks de guerres utilisés</a>';
                    endif;
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="container">
    <?php
    if ($state == "warDay"): ?>
        <h3 class="whiteShadow">Résultats des clans</h3>
        <div class="standings-badge badge-div">
            <?php
            if (isset($standings)):
                $i = 0;
                $pos = 1;
                $lastBattlesWon = 0;
                $lastCrowns = 0;
                $maxBattles = 0;
                foreach ($standings as $standing):
                    if ($standing['participants'] > $maxBattles):
                        $maxBattles = $standing['participants'];
                    endif;
                endforeach;

                foreach ($standings as $clan):
                    if ($lastCrowns == $clan['crowns'] && $lastBattlesWon == $clan['battles_won']):
                        $pos--;
                    endif;
                    ?>
                    <div class="standings-s-div">
                        <div class="standing-div">
                            <div class="flex">
                                <span class="whiteShadow text-center clan-rank" data-pos="<?php print $pos; ?>"><?php print $pos; ?></span>&nbsp;
                                <span class="whiteShadow clan-name"><?php print $clan['name']; ?></span>
                            </div>
                        </div>
                        <div class="results-div hideOnUpdate">
                            <div class="pull-right whiteShadow crowns-div trophy-div">
                                <img src="/images/ui/crowns.png" height="30px" class="crowns-img"/>
                                <span><?php print $clan['crowns']; ?></span>
                            </div>
                            <div class="pull-right whiteShadow wins-div trophy-div">
                                <img src="/images/ui/war-win.png" height="30px" class="wins-img"/>
                                <span><?php print $clan['battles_won']; ?></span>
                            </div>
                            <div class="pull-right whiteShadow battles-div trophy-div">
                                <img src="/images/ui/war-battle.png" height="30px" class="battles-img"/>
                                <span><?php print ($maxBattles - $clan['battles_played']); ?></span>
                            </div>
                            <div class="pull-right whiteShadow participants-div trophy-div">
                                <img src="/images/ui/participants.png" height="30px" class="participants-img"/>
                                <span><?php print $clan['participants']; ?></span>
                            </div>
                            <div class="pull-right whiteShadow clan-trophies-div trophy-div">
                                <img src="/images/ui/clan-trophies.png" height="30px" class="clan-trophies-img"/>
                                <span><?php print $clan['war_trophies']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php
                    if ($i < 4):
                        print "<hr>";
                    endif;
                    $i++;
                    $pos++;
                    $lastBattlesWon = $clan['battles_won'];
                    $lastCrowns = $clan['crowns'];
                endforeach;
            endif;
            ?>
        </div>
    <?php elseif ($state == "collectionDay"): ?>
        <h3 class="whiteShadow">Résultats du clan</h3>
        <div class="clan-result-badge badge-div hideOnUpdate">
            <div class="pull-right whiteShadow clan-cards-div trophy-div">
                <img src="/images/ui/deck.png" height="30px" class="clan-cards-img"/>
                <span id="numberOfCardsEarned"></span>
            </div>
            <div class="pull-right whiteShadow clan-wins-div trophy-div">
                <img src="/images/ui/war-win.png" height="30px" class="wins-img"/>
                <span id="numberOfCollectionWon"></span>
            </div>
            <div class="pull-right whiteShadow played-div trophy-div">
                <img src="/images/ui/battle.png" height="30px" class="played-img"/>
                <span id="numberOfCollectionPlayed"></span>
            </div>
            <div class="pull-right whiteShadow clan-non-participants-div trophy-div">
                <img src="/images/ui/non-participants.png" height="30px" class="clan-non-participants-img"/>
                <span id="numberOfMissing"></span>
            </div>
            <div class="pull-right whiteShadow clan-participants-div hideOnUpdate trophy-div">
                <img src="/images/ui/participants.png" height="30px" class="participants-img"/>
                <span id="numberOfParticipant"></span>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="container">
    <?php if ($state != "notInWar"): ?>
        <h3 class="whiteShadow">Résultats par joueur</h3>

        <div class="divCurrentWar table-responsive war-table-div">
            <table id="playersTable" class="table table-hover war-table">
                <thead>
                <tr class="rowIndex">
                    <th class="text-center warHeadIndex">Rang</th>
                    <th class="warHeadIndex">Joueur</th>
                    <?php if ($state == "warDay"): ?>
                        <th class="text-center warHeadIndex" colspan="2">Batailles</th>
                    <?php endif; ?>
                    <th class="text-center warHeadIndex" colspan="3">Collections</th>
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

                foreach ($warPlayers as $player):
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
                    echo '<td class="whiteShadow text-center rank"><div><span class="last-place">' . utf8_encode($player['rank']) . '</span></div></td>';
                    echo '<td class="whiteShadow"><a class="linkToPlayer" href="player/' . $player['tag'] . '">' . utf8_encode($player['name']) . '</a></td>';
                    if ($state == "warDay"):
                        echo '<td class="whiteShadow text-center">Jouées<br>' . $player['battle_played'] . '</td>';
                        echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['battle_won'] . '</td>';
                    endif;
                    echo '<td class="whiteShadow text-center">Jouées<br>' . $player['collection_played'] . '</td>';
                    echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['collection_won'] . '</td>';
                    echo '<td class="whiteShadow"><img src="/images/ui/deck.png" height="35px"/>&nbsp;' . $player['cards'] . '</td>';
                    echo '</tr>';
                    echo '<input type="hidden" class="hd_playerName" value="' . utf8_encode($player['name']) . '"/>';
                endforeach;
                ?>
                </tbody>
            </table>
        </div>
        <?php if ($state == "collectionDay"):
            $numberOfParticipant = intval(sizeof($warPlayers) - $minusParticipant); ?>
            <input type="hidden" id="hd_numberOfParticipants" value="<?php print $numberOfParticipant; ?>"/>
            <input type="hidden" id="hd_numberOfMissing" value="<?php print $minusParticipant; ?>"/>
            <input type="hidden" id="hd_numberOfCollectionPlayed" value="<?php print $totalCollectionPlayed; ?>"/>
            <input type="hidden" id="hd_numberOfCollectionWon" value="<?php print $totalCollectionWon; ?>"/>
            <input type="hidden" id="hd_numberOfCardsEarned" value="<?php print $totalCardsEarned; ?>"/>
        <?php endif; endif; ?>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="/images/loader.gif"/>
</div>
<div class="row text-center">
    <br>
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