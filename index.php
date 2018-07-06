<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 14:56
 */

include("tools/database.php");
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
        });
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Liste des joueurs</h1>
    <span class="pageIndexSubtitle whiteShadow">Vous pouvez cliquer sur une ligne pour voir le détail d'un joueur</b></span>
    <br><br>
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
            <tr class="pointerHand">
                <td class="rank text-center"><span> <?php echo $player['rank']; ?></span></td>
                <td class=" whiteShadow">
                    <a class="linkToPlayer" href="view_player.php?tag=<?php print $player['tag']?>">
                        <?php print utf8_encode($player['playerName']); ?>
                    </a>
                    <br>
                    <span class="small">
                        <?php print utf8_encode($player['playerRole']) ?>
                    </span>
                </td>
                <td class=" whiteShadow"> <?php print $player['tag']; ?></td>
                <td class=" whiteShadow">
                    <?php print $player['trophies'] ?> <img src="res/trophy.png" height="20px">
                </td>
                <td class="">
                    <?php if($player['arena_id'] > 12): ?>
                    <img src="res/arena/arena-<?php print $player['arena_id'] ?>.png"
                         title="<?php print $player['arena'] ?>" height="50px">
                    <?php else : ?>
                    <div>
                        <img src="res/arena/arena-.png" title="" height="50px">
                        <span class="whiteShadow arenaNumber"><?php print $player['arena_id'];?></span>
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
        <?php endforeach; ?>
        </tbody>
    </table>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>