<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 28/06/2018
 * Time: 16:08
 */

include(__DIR__ . "/tools/database.php");
include(__DIR__ . "/tools/api_conf.php");

$playerTag = explode("/", substr($_SERVER['REQUEST_URI'], 1))[1];
if (empty($playerTag)) {
    header('Location: /clan');
}

$player = getPlayerInfos($db, $playerTag);
$crIds = explode(",", $player['cr_ids']);
$deckLinkPattern = "https://link.clashroyale.com/deck/fr?deck=%d;%d;%d;%d;%d;%d;%d;%d";
$deckLink = sprintf(
    $deckLinkPattern, $crIds[0], $crIds[1], $crIds[2],
    $crIds[3], $crIds[4], $crIds[5], $crIds[6],
    $crIds[7]
);

// last updated
$lastUpdated = getLastUpdatedPlayer($db, $playerTag);
//TODO faire le design de l'affichage des cartes (elixir, niveau, couleur...)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Détail du joueur</title>
    <?php include("head.php"); ?>
    <script>
        $(document).ready(function () {
            let playerTag = $('#playerTagHidden').val();

            $.ajax({
                type: "GET",
                url: "/query/ajax/get_player_chests.php",
                data: {
                    tag: playerTag
                },
                success(data) {
                    if (data !== 'false') {
                        $('#chestsDiv').fadeOut(function () {
                            $(this).html(data);
                            $(this).fadeIn();
                        });
                    }
                }
            });
        });
    </script>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <!--    TODO refaire le design de la page (ci dessous, le bandeau du nom du joueur/trophées) -->
    <!--    <div class="row">-->
    <!--        <div class="col-md-5 player-badge">-->
    <!--            <h3 class="pull-left whiteShadow"><img src="images/coin.png" height="40px"/>&nbsp;-->
    <?php //print $player['playerName']; ?><!--</h3>-->
    <!--            <div class="pull-right whiteShadow trophy-div">-->
    <!--                <img src="images/ui/trophy.png" height="30px" class="trophy-img"/>&nbsp;--><?php //print $player['trophies']; ?>
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <div class="row">
        <div class="col-md-5">
            <h3 class="whiteShadow">Coffres à venir</h3>
            <div class="row text-center" id="chestsDiv">
                <?php for ($i = 0; $i <= 7; $i++): ?>
                    <div class="col-xs-3">
                        <div class="img-responsive chests-placeholder">
                            <img src="/images/chests/legendary-chest.png" alt="failed to load img"
                                 class="img-responsive chests"/>
                            <span class="chestNumber whiteShadow">+</span>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="col-md-5 col-md-offset-2">
            <h3 class="whiteShadow">Deck du moment</h3>
            <div class="row">
                <?php
                $cardKeys = explode(",", $player['card_keys']);
                if (sizeof($cardKeys) > 0):
                    for ($i = 0; $i <= 7; $i++):?>
                        <div class="col-xs-3">
                            <div class="img-responsive">
                                <img src="/images/cards/<?php print $cardKeys[$i]; ?>.png"
                                     alt="failed to load img" class="img-responsive cards"/>
                            </div>
                        </div>
                    <?php endfor; ?>
                    <div id="deckLinkDiv" class="text-center pointerHand">
                        <a href="<?php print $deckLink; ?>" class="text-center">
                            <img src="/images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                            <span id="spanDeckLink" class="whiteShadow text-center">Copier le deck</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div>
                        <span class="whiteShadow text-center">Actualisez les informations pour voir votre deck</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <br><br>
    <h3 class="whiteShadow">Joueur</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Rang</th>
                <th class="headIndex text-center table-border" colspan="3">Joueur</th>
                <th class="headIndex text-center table-border" colspan="2">Trophées</th>
                <th class="headIndex text-center">Arène</th>
                <th class="headIndex text-center table-border-left" colspan="2">Dons</th>
            </tr>
            </thead>
            <tbody>
            <?php
            echo '<tr>';
            echo '<input id="playerTagHidden" type="hidden" name="playerTagHidden" value="' . $player['tag'] . '" />';
            echo '<td class="whiteShadow">' . $player['rank'] . '</td>';
            echo '<td id="playerTag" class="whiteShadow text-center table-border-left">' . $player['tag'] . '</td>';
            echo '<td class="whiteShadow text-center">' . utf8_encode($player['playerName']) . '<br>' . utf8_encode($player['playerRole']) . '</td>';
            echo '<td class="whiteShadow text-center">Niveau<br>' . $player['level'] . '</td>';
            echo '<td class="whiteShadow text-center table-border-left">Courant<br>' . $player['trophies'] . '</td>';
            echo '<td class="whiteShadow text-center">Max<br>' . $player['max_trophies'] . '</td>';
            echo '<td class="whiteShadow text-center table-border">' . $player['arena'] . '</td>';
            echo '<td class="whiteShadow text-center">Données<br>' . $player['donations'] . '</td>';
            echo '<td class="whiteShadow text-center">Reçues<br>' . $player['received'] . '</td>';
            echo '</tr>';
            ?>
            </tbody>
        </table>
    </div>
    <h3 class="whiteShadow">Guerres</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Guerres</th>
                <th class="headIndex text-center table-border" colspan="3">Collections</th>
                <th class="headIndex text-center" colspan="2">Batailles</th>
            </tr>
            </thead>
            <tbody>
            <?php
            echo '<tr>';
            echo '<td class="whiteShadow">Jouées<br>' . $player['total_war_played'] . '</td>';
            echo '<td class="whiteShadow text-center table-border-left">Jouées<br>' . $player['total_collection_played'] . '</td>';
            echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['total_collection_won'] . '</td>';
            echo '<td class="whiteShadow text-center"><img src="/images/ui/deck.png" height="35px"/>&nbsp;' . $player['total_cards_earned'] . '</td>';
            echo '<td class="whiteShadow text-center table-border-left">Jouées<br>' . $player['total_battle_played'] . '</td>';
            echo '<td class="whiteShadow text-center">Gagnées<br>' . $player['total_battle_won'] . '</td>';
            echo '</tr>';
            ?>
            </tbody>
        </table>
    </div>
    <h3 class="whiteShadow">Absences</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tbody>
            <?php
            echo '<tr>';
            echo '<td class="whiteShadow text-center">Collections<br>' . $player['missed_collection'] . '</td>';
            echo '<td class="whiteShadow text-center">Batailles<br>' . $player['missed_war'] . '</td>';
            echo '</tr>';
            ?>
            </tbody>
        </table>
    </div>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="/images/loader.gif"/>
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
