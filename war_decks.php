<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 08/07/2018
 * Time: 14:38
 */

include("tools/database.php");
include("tools/api_conf.php");

$allCards = [];
$lastUpdated = getLastUpdated($db, "war_decks");
$state = getWarStateFromApi($api);
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
    <?php if ($lastUpdated['updated'] != null):
        $time = strtotime($lastUpdated['updated']);
        ?>
        <span class="pageIndexSubtitle whiteShadow pull-right">Dernière mise à jour le : <b><?php echo '' . date('d/m/Y', $time) ?></b> à <b><?php echo '' . date('H:i', $time) ?></span>
    <?php else: ?>
        <span class="pageIndexSubtitle whiteShadow pull-right">Nécessite une mise à jour</span>
    <?php endif; ?>
    <h1 class="whiteShadow">Decks de guerre</h1><br>
    <h4 class="whiteShadow">Attention, actualiser ces informations peut prendre beaucoup de temps</h4>
    <br><br>

    <ul class="nav nav-tabs">
        <?php if ($state == 'warDay'): ?>
            <li role="presentation" class="active"><a href="#current" data-toggle="tab"
                                                      class="tab-link">Guerre en cours</a></li>
        <? endif; ?>
        <li role="presentation" <?php print 'class="active"'; ?>><a href="#allWar" data-toggle="tab" class="tab-link">Toutes
                les guerres</a></li>
        <li role="presentation"><a href="#favCards" data-toggle="tab" class="tab-link">Cartes favorites</a></li>
    </ul>
    <br>
    <div class="tab-content">
        <?php if ($state == 'warDay'): ?>
            <div class="tab-pane active" id="current">
                <?php
                $counter = 0;
                $pos = 1;
                $allDecks = getAllWarDecks($db, true);
                $size = sizeof($allDecks);
                foreach ($allDecks as $deck):
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
                                    <span class="whiteShadow">Joués : <?php print $deck['played']; ?>
                                        &nbsp; - &nbsp;</span>
                                    <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                        &nbsp; - &nbsp;</span>
                                    <span class="whiteShadow">Couronnes : <?php print $deck['crowns']; ?></span><br><br>
                                    <div id="deckLinkDiv" class="text-center pointerHand">
                                        <a href="<?php print $deckLink; ?>" class="text-center">
                                            <img src="images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                                            <span id="spanDeckLink"
                                                  class="whiteShadow text-center">Copier le deck</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($pos == $size)
                            echo '</div>';

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
                                    <span class="whiteShadow">Joués : <?php print $deck['played']; ?>
                                        &nbsp; - &nbsp;</span>
                                    <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                        &nbsp; - &nbsp;</span>
                                    <span class="whiteShadow">Couronnes : <?php print $deck['crowns']; ?></span><br><br>
                                    <div id="deckLinkDiv" class="text-center pointerHand">
                                        <a href="<?php print $deckLink; ?>" class="text-center">
                                            <img src="images/ui/copy-deck.png" class="deckLink" alt="Copier le lien">
                                            <span id="spanDeckLink"
                                                  class="whiteShadow text-center">Copier le deck</span>
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
                    $pos++;
                endforeach;
                ?>
            </div>
        <?php endif ?>
        <div class="tab-pane <?php if($state != 'warDay'): print 'active'; endif;?>" id="allWar">
            <?php
            $counter = 0;
            $pos = 1;
            $allDecks = getAllWarDecks($db, false);
            $size = sizeof($allDecks);
            foreach ($allDecks as $deck):
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
                    if ($pos == $size)
                        echo '</div>';

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
                $pos++;
            endforeach;
            ?>
        </div>
        <div class="tab-pane" id="favCards">
            <?php
            $bestCards = array_count_values($allCards);
            arsort($bestCards);
            $counter = 0;
            $pos = 0;
            foreach ($bestCards as $key => $bestCard):
                if ($pos > 8):
                    break;
                endif;
                $cardKey = getCardByCrId($db, $key)['card_key'];
                if ($counter == 0): ?>
                    <div class="row">
                    <div class="col-lg-4">
                        <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage"
                             class="img-responsive center-block"/>
                        <span class="whiteShadow text-center center-block">Présente dans <?php print $bestCard; ?>
                            decks</span>
                    </div>
                    <?php
                    $counter++;
                elseif ($counter == 1):?>
                    <div class="col-lg-4">
                        <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage"
                             class="img-responsive center-block"/>
                        <span class="whiteShadow text-center center-block">Présente dans <?php print $bestCard; ?>
                            decks</span>
                    </div>
                    <?php
                    $counter++;
                else:?>
                    <div class="col-lg-4">
                        <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage"
                             class="img-responsive center-block"/>
                        <span class="whiteShadow text-center center-block">Présente dans <?php print $bestCard; ?>
                            decks</span>
                    </div>
                    </div>
                    <?php
                    $counter = 0;
                endif;
                $pos++;
            endforeach; ?>
        </div>
    </div>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="res/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>
