<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 16:41
 */

include("tools/database.php");
include("tools/api_conf.php");

$warPlayers = getWarPlayers($db);
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
        });

        function update() {
            $.ajax({
                url: 'query/update_clan.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    $.ajax({
                        url: 'query/update_war.php',
                        success: function () {
                            window.location.reload(true);
                        }
                    });
                }
            })
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Guerre en cours</h1>
    <span class="whiteShadow"><?php echo $stateName ?></span><br>
    <span class="whiteShadow">Fin le <b><?php echo '' . date('d/m/Y', $endTime) ?></b> à <b><?php echo '' . date('H:i', $endTime) ?></b></span>
    <?php
    if ($state == "warDay") { ?>
        <br>
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
                        $pos++;
                        $lastBattlesWon = $clan['battles_won'];
                        $lastCrowns = $clan['crowns'];
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <br><br>
    <span class="pageSubtitle whiteShadow">Résultats par joueurs</span>
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

                echo '<tr class="pointerHand">';
                echo '<td class="whiteShadow text-center rank"><span>' . utf8_encode($player['rank']) . '</span></td>';
                echo '<td class="whiteShadow"><a class="linkToPlayer" href="view_player.php?tag=' . $player['tag'] . '">' . utf8_encode($player['name']) . '</a></td>';
                echo '<td class="whiteShadow text-center">Jouées<br>' . $player['collection_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['collection_won'] . '</td>';
                echo '<td class="whiteShadow"><img src="images/ui/deck.png" height="35px"/>&nbsp;' . $player['cards'] . '</td>';
                echo '<td class="whiteShadow text-center">Jouées<br>' . $player['battle_played'] . '</td>';
                echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['battle_won'] . '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php if ($state == "collectionDay") {
        $numberOfParticipant = intval(sizeof($warPlayers) - $minusParticipant);
        ?>
        <div class="table-responsive">
            <table id="tableIndex" class="table">
                <tbody>
                <tr>
                    <td class="whiteShadow text-center">Participants<br><?php echo $numberOfParticipant; ?></td>
                    <td class="whiteShadow text-center">Absent<br><?php echo $minusParticipant; ?></td>
                    <td class="whiteShadow text-center">Jouées<br><?php echo $totalCollectionPlayed ?></td>
                    <td class="whiteShadow text-center">Gagnées<br><?php echo $totalCollectionWon ?></td>
                    <td class="whiteShadow text-center"><img src="images/ui/deck.png" height="35px"/><?php echo $totalCardsEarned ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <br><br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>
