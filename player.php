<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 28/06/2018
 * Time: 16:08
 */

include(__DIR__ . "/tools/bootstrap.php");
include(__DIR__ . "/check_login.php");

$playerTag = explode("/", substr($_SERVER['REQUEST_URI'], 1))[1];

if (isset($_GET['tag'])) $playerTag = $_GET['tag'];

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
// TODO : Faire le "glow" multicolor autour des légendaires

// TODO voir le probleme de la typo selon la taille

// TODO revoir le tableau de guerre (style /war)

// TODO afficher un graph de progression trophée

// TODO enregistrer les coffres en bases systematiquement pour pouvoir ressortir une liste en cas d'API DOWN
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Détail du joueur</title>
    <?php include("head.php"); ?>
    <script type="text/javascript" src="/js/player.js"></script>
</head>
<body>
<?php include("header.php"); ?>
<input type="hidden" id="hd_playerId" value="<?php print $player['playerId']; ?>"/>
<div class="container">
    <div class="player-badge badge-div">
        <div class="pull-right whiteShadow trophy-div hideOnUpdate">
            <img src="/images/ui/trophy.png" height="30px" class="trophy-img"/>
            <span><?php print $player['trophies']; ?></span>
            <div class="max-trophy">Max <?php print $player['max_trophies'] ?></div>
        </div>
        <div class="flex">
            <div class="level-container">
                <span><?php print $player['level'] ?></span>
                <img src="/images/ui/level-big.png" class="player-level"/>
            </div>
            <h1 class="whiteShadow">
                <?php print utf8_encode($player['playerName']); ?>
                <br>
                <span class="small whiteShadow"><?php print utf8_encode($player['playerRole']) ?></span>&nbsp;<span
                        class="tiny"><?php print utf8_encode($player['tag']); ?></span>
            </h1>
        </div>
        <div>
            <span class="whiteShadow">
                Rang: <?php print $player['rank']; ?> <br>
                <?php print $player['arenaName']; ?>
            </span>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-5 player-chests">
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
        <div class="col-md-5 col-md-offset-2 player-deck">
            <div class="wrapper-current-deck">
                <h3 class="whiteShadow">Deck du moment</h3>
                <?php
                $cardKeys = explode(",", $player['card_keys']);
                if (sizeof($cardKeys) > 0): ?>
                <div class="row">
                    <?php for ($i = 0; $i <= 7; $i++): ?>
                        <div class="col-xs-3">
                            <div class="img-responsive">
                                <img src="/images/cards/<?php print $cardKeys[$i]; ?>.png"
                                     alt="failed to load img" class="img-responsive cards"/>
                                <div class="card-level">
                                    <span class="greyShadow card-level-span"
                                          data-card="<?php print $cardKeys[$i]; ?>"></span>
                                </div>
                                <div class="card-shadow"></div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="text-center pointerHand deckLinkHand">
                    <a href="<?php print $deckLink; ?>" class="text-center">
                        <img src="/images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                        <span id="spanDeckLink" class="whiteShadow text-center">Copier</span>
                    </a>
                </div>
                <div class="elixir-average">
                    Cout moyen en élixir: <?php print $player['elixir_cost']; ?>
                </div>
            </div>
            <?php else: ?>
                <div>
                    <span class="whiteShadow text-center">Actualisez les informations pour voir votre deck</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <h3 class="whiteShadow">Dons de la semaine</h3>
    <div class="table-responsive player-table-div">
        <table class="table player-table">
            <thead>
            <tr class="rowIndex">
            </tr>
            </thead>
            <tbody>
            <?php
            echo '<tr>';
            echo '<input id="playerTagHidden" type="hidden" name="playerTagHidden" value="' . $player['tag'] . '" />';
            echo '<td class="whiteShadow text-center">Données<br>' . $player['donations'] . '</td>';
            echo '<td class="whiteShadow text-center">Reçues<br>' . $player['received'] . '</td>';
            echo '</tr>';
            ?>
            </tbody>
        </table>
    </div>
    <h3 class="whiteShadow">Guerres <span class="small whiteShadow">(&nbsp;<?php print $player['total_war_played'] ?>
            jouées&nbsp;)</span></h3>
    <div class="table-responsive player-table-div">
        <table class="table player-table">
            <thead>
            <tr class="rowIndex">
                <th class="playerHeadIndex text-center" colspan="6">Collections</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="whiteShadow text-center" colspan="2">
                    Jouées<br><?php print $player['total_collection_played'] ?></td>
                <td class="whiteShadow text-center" colspan="2">
                    Gagnées<br><?php print $player['total_collection_won'] ?></td>
                <td class="whiteShadow text-center" colspan="2"><img src="/images/ui/deck.png"
                                                                     height="35px"/><?php print $player['total_cards_earned'] ?>
                </td>
            </tr>
            </tbody>
            <thead>
            <tr class="rowIndex">
                <th class="playerHeadIndex text-center" colspan="6">Batailles</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="whiteShadow text-center" colspan="3">Jouées<br><?php print $player['total_battle_played'] ?>
                </td>
                <td class="whiteShadow text-center" colspan="3">Gagnées<br><?php print $player['total_battle_won'] ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <h3 class="whiteShadow">Absences</h3>
    <div class="table-responsive player-table-div">
        <table class="table player-table">
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
<div class="text-center">
    <?php if ($lastUpdated['updated'] != null):
        $time = strtotime($lastUpdated['updated']);
        ?>
        <span class="whiteShadow">Dernière mise à jour le : <b><?php print date('d/m/Y', $time); ?></b> à <b><?php print date('H:i', $time); ?></span>
    <?php else: ?>
        <span class="whiteShadow">Nécessite une mise à jour</span>
    <?php endif; ?>
</div>
<?php include("footer.html"); ?>
</body>
</html>
