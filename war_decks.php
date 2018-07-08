<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 08/07/2018
 * Time: 14:38
 */

include("tools/database.php");
include("tools/api_conf.php");
if (getWarStateFromApi($api) == 'warDay')
    $tabName = "Guerre en cours";
else
    $tabName = "Dernière guerre";

$allCards = [];

function getDeckLink($deck)
{
    $pattern = "https://link.clashroyale.com/deck/fr?deck=%d;%d;%d;%d;%d;%d;%d;%d";
    return sprintf($pattern, $deck['crid1'], $deck['crid2'], $deck['crid3'], $deck['crid4'], $deck['crid5'],
        $deck['crid6'], $deck['crid7'], $deck['crid8']);
}

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
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Decks de guerre</h1><br>
    <h4 class="whiteShadow">Attention, actualiser ces informations peut prendre beaucoup de temps</h4>
    <br><br>
    <ul id="navUlWarDecks" class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#current" aria-controls="current" role="tab" data-toggle="tab"
               class="tab-link"><?php print $tabName; ?></a>
        </li>
        <li role="presentation">
            <a href="#allWar" aria-controls="allWar" role="tab" data-toggle="tab" class="tab-link">Toutes les
                guerres</a>
        </li>
<!--        TODO uncomment quand la tab des cartes favorites est terminée -->
<!--        <li role="presentation">-->
<!--            <a href="#favCards" aria-controls="favCards" role="tab" data-toggle="tab" class="tab-link">Cartes-->
<!--                favorites</a>-->
<!--        </li>-->
    </ul>
    <br><br>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="current">
            <?php
            $counter = 0;
            foreach (getAllWarDecks($db, true) as $deck):
                $deckLink = getDeckLink($deck);
                if ($counter == 0): ?>
                    <div class="row">
                    <div class="col-md-5">
                        <div class="row">
                            <?php
                            for ($i = 1; $i <= 8; $i++):?>
                                <div class="col-xs-3">
                                    <div class="img-responsive">
                                        <img src="images/cards/<?php print $deck['c' . $i . 'key'] ?>.png"
                                             alt="failed to load img"
                                             class="img-responsive"/>
                                    </div>
                                </div>
                            <?php
                            endfor; ?>
                        </div>
                        <div class="second-row">
                            <div id="resultsDiv" class="pointerHand text-center js-result-div">
                                <span class="whiteShadow">Joués : <?php print $deck['played']; ?>&nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                    &nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Couronnes : <?php print $deck['crowns']; ?></span><br><br>
                                <div id="deckLinkDiv" class="text-center pointerHand">
                                    <a href="<?php print $deckLink; ?>" class="text-center">
                                        <img src="images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                                        <span id="spanDeckLink" class="whiteShadow text-center">Copier le deck</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $counter++;
                else:?>
                    <div class="col-md-5 col-md-offset-2">
                        <div class="row">
                            <?php
                            for ($i = 1; $i <= 8; $i++):?>
                                <div class="col-xs-3">
                                    <div class="img-responsive">
                                        <img src="images/cards/<?php print $deck['c' . $i . 'key'] ?>.png"
                                             alt="failed to load img"
                                             class="img-responsive"/>
                                    </div>
                                </div>
                            <?php
                            endfor; ?>
                        </div>
                        <div class="second-row">
                            <div id="resultsDiv" class="pointerHand text-center js-result-div">
                                <span class="whiteShadow">Joués : <?php print $deck['played']; ?>&nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                    &nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Couronnes : <?php print $deck['crowns']; ?></span><br><br>
                                <div id="deckLinkDiv" class="text-center pointerHand">
                                    <a href="<?php print $deckLink; ?>" class="text-center">
                                        <img src="images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                                        <span id="spanDeckLink" class="whiteShadow text-center">Copier le deck</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <br><br>
                    <?php
                    $counter = 0;
                endif;
            endforeach;
            ?>
        </div>
        <div role="tabpanel" class="tab-pane" id="allWar">
            <?php
            $counter = 0;
            foreach (getAllWarDecks($db, false) as $deck):
                if ($counter == 0): ?>
                    <div class="row">
                    <div class="col-md-5">
                        <div class="row">
                            <?php
                            for ($i = 1; $i <= 8; $i++):
                                array_push($allCards, intval($deck['crid' . $i]));
                                ?>
                                <div class="col-xs-3">
                                    <div class="img-responsive">
                                        <img src="images/cards/<?php print $deck['c' . $i . 'key'] ?>.png"
                                             alt="failed to load img"
                                             class="img-responsive"/>
                                    </div>
                                </div>
                            <?php
                            endfor; ?>
                        </div>
                        <div class="second-row">
                            <div id="resultsDiv" class="pointerHand text-center js-result-div">
                                <span class="whiteShadow">Joués : <?php print $deck['played']; ?>&nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                    &nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Couronnes : <?php print $deck['crowns']; ?></span><br><br>
                                <div id="deckLinkDiv" class="text-center pointerHand">
                                    <a href="<?php print $deckLink; ?>" class="text-center">
                                        <img src="images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                                        <span id="spanDeckLink" class="whiteShadow text-center">Copier le deck</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $counter++;
                else:?>
                    <div class="col-md-5 col-md-offset-2">
                        <div class="row">
                            <?php
                            for ($i = 1; $i <= 8; $i++):
                                array_push($allCards, intval($deck['crid' . $i]));
                                ?>
                                <div class="col-xs-3">
                                    <div class="img-responsive">
                                        <img src="images/cards/<?php print $deck['c' . $i . 'key'] ?>.png"
                                             alt="failed to load img" class="img-responsive"/>
                                    </div>
                                </div>
                            <?php
                            endfor; ?>
                        </div>
                        <div class="second-row">
                            <div id="resultsDiv" class="pointerHand text-center js-result-div">
                                <span class="whiteShadow">Joués : <?php print $deck['played']; ?>&nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                    &nbsp; - &nbsp;</span>
                                <span class="whiteShadow">Couronnes : <?php print $deck['crowns']; ?></span><br><br>
                                <div id="deckLinkDiv" class="text-center pointerHand">
                                    <a href="<?php print $deckLink; ?>" class="text-center">
                                        <img src="images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                                        <span id="spanDeckLink" class="whiteShadow text-center">Copier le deck</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <br><br>
                    <?php
                    $counter = 0;
                endif;
            endforeach;
            ?>
        </div>
        <div role="tabpanel" class="tab-pane" id="favCards">
            <?php
            $bestCards = array_count_values($allCards);
            arsort($bestCards);
            $counter = 0;
            $pos = 0;
            foreach ($bestCards as $key => $bestCard):
                if ($pos > 7):
                    break;
                endif;
                $cardKey = getCardByCrId($db, $key)['card_key'];
                if ($counter == 0): ?>
                    <div class="row">
                    <div class="col-md-5 img-responsive">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage" class="img-responsive"/>
                            </div>
                            <div class="col-md-2">
                                <h4 class="whiteShadow"><?php print $bestCard; ?> decks</h4>
                            </div>
                        </div>
                    </div>
                    <?php
                    $counter++;
                else:?>
                    <div class="col-md-5 col-md-offset-2 img-responsive">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage" class="img-responsive"/>
                            </div>
                            <div class="col-md-2">
                                <h4 class="whiteShadow"><?php print $bestCard; ?> decks</h4>
                            </div>
                        </div>
                    </div>
                    </div>
                    <?php
                    $counter = 0;
                endif;
                $pos++;
            endforeach; ?>
        </div>
        <div id="loaderDiv">
            <img id="loaderImg" src="res/loader.gif"/>
        </div>
        <?php include("footer.html"); ?>
</body>
</html>
