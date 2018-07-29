<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 14:56
 */

include(__DIR__."/tools/database.php");
$lastUpdated = getLastUpdated($db, "index");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron</title>

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
                    window.location.reload(true);
                }
            })
        }

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
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Liste des joueurs</h1>
    <span class="pageIndexSubtitle whiteShadow">Vous pouvez cliquer sur une ligne pour voir le détail d'un joueur</span><br>
    <input type="text" id="tx_search" class="pull-right" placeholder="Trier par nom" style="margin-left: 10px;"/>
    <br><br>
    <div class="table-responsive">
        <table id="tableIndex" class="table tableIndex">
            <thead>
            <tr class="rowIndex">
                <th class="headIndex">Rang</th>
                <th class="headIndex">Nom</th>
                <th class="headIndex">Tag</th>
                <th class="headIndex">Trophée</th>
                <th class="headIndex">Arène</th>
                <th class="headIndex text-center" colspan="2">Dons</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (getAllPlayersForIndex($db) as $player) : ?>
                <tr class="pointerHand playerTr">
                    <td class="rank text-center"><span> <?php echo $player['rank']; ?></span></td>
                    <td class=" whiteShadow">
                        <a class="linkToPlayer" href="player.php?tag=<?php print $player['tag']; ?>">
                            <?php print utf8_encode($player['playerName']); ?>
                        </a>
                        <br>
                        <span class="small">
                        <?php print utf8_encode($player['playerRole']); ?>
                    </span>
                    </td>
                    <td class=" whiteShadow"> <?php print $player['tag']; ?></td>
                    <td class=" whiteShadow">
                        <?php print $player['trophies'] ?> <img src="images/ui/trophy.png" height="20px">
                    </td>
                    <td class="">
                        <?php if ($player['arena_id'] > 12): ?>
                            <img src="images/arenas/arena-<?php print $player['arena_id']; ?>.png"
                                 title="<?php print $player['arena']; ?>" height="50px">
                        <?php else : ?>
                            <div>
                                <img src="images/arenas/arena-.png" title="" height="50px">
                                <span class="whiteShadow arenaNumber"><?php print $player['arena_id']; ?></span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class=" text-center whiteShadow">
                        Reçues <br>
                        <?php print $player['donations'] ?>
                    </td>
                    <td class=" text-center whiteShadow">
                        Données <br>
                        <?php print $player['donations_received'] ?>
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
    <img id="loaderImg" src="images/loader.gif"/>
</div>
<div class="row text-center">
    <?php if ($lastUpdated['updated'] != null):
        $time = strtotime($lastUpdated['updated']);
        ?>
        <span class="pageIndexSubtitle whiteShadow">Dernière mise à jour le : <b><?php echo '' . date('d/m/Y', $time) ?></b> à <b><?php echo '' . date('H:i', $time) ?></span>
    <?php else: ?>
        <span class="pageIndexSubtitle whiteShadow">Nécessite une mise à jour</span>
    <?php endif; ?>
</div>
<?php include("footer.html"); ?>
</body>
</html>