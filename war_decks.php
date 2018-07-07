<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 07/07/2018
 * Time: 02:47
 */

include("tools/database.php");
include("tools/api_conf.php");

$warId = getCurrentWar($db)['id'];
$deckIds = getWarDecksId($db, $warId);
$deckId = $deckIds[0]['id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Decks de guerre</title>
    <?php include("head.php"); ?>
    <script>
        function update() {
            $.ajax({
                url: 'query/update_war_decks.php',
                beforeSend: function () {
                    $('#loaderDiv').show();
                },
                success: function () {
                    window.location.reload(true);
                }
            })
        }

        $(document).ready(function () {
            $('.js-result-div').each(function () {
                $(this).find('.js-span-deck-link').click(function () {
                    window.location = $(this).parent().find(".js-deck-link").data('link');
                });
            });
        });
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Decks de la guerre en cours</h1><br>
    <h2 class="whiteShadow">Attention, actualiser ces informations peut prendre beaucoup de temps</h2><br>
    <?php
    $counter = 0;
    foreach ($deckIds as $deckId):
        $deckId = intval($deckId['id']);
        if ($counter == 0) { ?>
            <div class="row">
            <div class="col-md-5">
                <div class="row">
                    <?php
                    foreach (getCardsNameByDeckId($db, $deckId) as $card): ?>
                        <div class="col-xs-3">
                            <div class="img-responsive">
                                <img src="images/cards/<?php print $card['card_key'] ?>.png" alt="failed to load img"
                                     class="img-responsive cards"/>
                            </div>
                        </div>
                    <?php endforeach;
                    $deckLink = getDeckLinkFromDeckId($db, $deckId);
                    $results = getDeckResultByDeckId($db, $deckId); ?>
                    <div id="resultsDiv" class="pointerHand text-center js-result-div">
                        <span class="whiteShadow">Joués : <?php print $results['played']; ?>&nbsp; - &nbsp;</span>
                        <span class="whiteShadow">Victoires : <?php print $results['wins']; ?>&nbsp; - &nbsp;</span>
                        <span class="whiteShadow">Couronnes : <?php print $results['crowns']; ?></span><br><br>
                        <input type="hidden" id="hd_deckLink" class="js-deck-link"
                               data-link="<?php print $deckLink ?>"/>
                        <span id="spanDeckLink" class="whiteShadow text-center js-span-deck-link">Copier le deck</span>
                    </div>
                </div>
            </div>
            <?php
            $counter++;
        } else { ?>
            <div class="col-md-5 col-md-offset-2">
                <div class="row">
                    <?php
                    foreach (getCardsNameByDeckId($db, $deckId) as $card): ?>
                        <div class="col-xs-3">
                            <div class="img-responsive">
                                <img src="images/cards/<?php print $card['card_key'] ?>.png" alt="failed to load img"
                                     class="img-responsive cards"/>
                            </div>
                        </div>
                    <?php endforeach;
                    $deckLink = getDeckLinkFromDeckId($db, $deckId);
                    $results = getDeckResultByDeckId($db, $deckId); ?>
                    <div id="resultsDiv" class="pointerHand text-center js-result-div">
                        <span class="whiteShadow">Joués : <?php print $results['played']; ?>&nbsp; - &nbsp;</span>
                        <span class="whiteShadow">Victoires : <?php print $results['wins']; ?>&nbsp; - &nbsp;</span>
                        <span class="whiteShadow">Couronnes : <?php print $results['crowns']; ?></span><br><br>
                        <input type="hidden" id="hd_deckLink" class="js-deck-link"
                               data-link="<?php print $deckLink ?>"/>
                        <span id="spanDeckLink" class="whiteShadow text-center js-span-deck-link">Copier le deck</span>
                    </div>
                </div>
            </div>
            </div>
            <br><br>
            <?php
            $counter = 0;
        }
    endforeach; ?>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>