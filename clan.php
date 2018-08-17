<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 14:56
 */

include(__DIR__ . "/tools/database.php");
include_once(__DIR__ . "/check_login.php");

$lastUpdated = getLastUpdated($db, "index");

//TODO refaire les images d'arènes 1-12
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Clan</title>

    <?php include("head.php"); ?>
    <script>
        $(document).ready(function () {
            $('#tableIndex').on('click', 'tbody td', function () {
                $("body").css("cursor", "wait");
                window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
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
<?php include("header.php"); ?>
<div class="container">
    <h1 class="whiteShadow">Liste des joueurs</h1>
    <div class="row">
        <div class="col-md-9">
            <span class="whiteShadow">Vous pouvez cliquer sur une ligne pour voir le détail d'un joueur</span><br>
        </div>
        <div class="col-md-3">
            <input type="text" id="tx_search" class="pull-right form-control" placeholder="Filtrer par nom"
                   style="margin-left: 10px;"/><br><br>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tableIndex" class="table tableIndex">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Rang</th>
                <th class="headIndex">Nom</th>
                <th class="headIndex hidden-xs">Tag</th>
                <th class="headIndex">Trophée</th>
                <th class="headIndex">Arène</th>
                <th class="headIndex text-center" colspan="2">Dons</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (getAllPlayersForIndex($db) as $player) : ?>
                <tr class="pointerHand playerTr">
                    <td class="rank text-center"><span> <?php echo $player['rank']; ?></span></td>
                    <td class="whiteShadow">
                        <a class="linkToPlayer" href="/player/<?php print $player['tag']; ?>">
                            <?php print utf8_encode($player['playerName']); ?>
                        </a>
                        <br>
                        <span class="small">
                        <?php print utf8_encode($player['playerRole']); ?>
                    </span>
                    </td>
                    <td class=" whiteShadow hidden-xs"> <?php print $player['tag']; ?></td>
                    <td class=" whiteShadow">
                        <?php print $player['trophies'] ?> <img src="/images/ui/trophy.png" height="20px">
                    </td>
                    <td class="">
                        <?php if ($player['arena_id'] > 9): ?>
                            <img src="/images/arenas/arena-<?php print $player['arena_id']; ?>.png"
                                 title="<?php print $player['arena']; ?>" height="50px">
                        <?php else : ?>
                            <div>
                                <img src="/images/arenas/arena-.png" title="" height="50px">
                                <span class="whiteShadow arenaNumber"><?php print $player['arena_id']; ?></span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class=" text-center whiteShadow">
                        Reçues <br>
                        <?php print $player['donations_received'] ?>
                    </td>
                    <td class=" text-center whiteShadow">
                        Données <br>
                        <?php print $player['donations'] ?>
                    </td>
                </tr>
                <input type="hidden" class="hd_playerName" value="<?php print utf8_encode($player['playerName']); ?>"/>
            <?php endforeach; ?>
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