<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 08/07/2018
 * Time: 14:38
 */

include(__DIR__."/tools/database.php");
include(__DIR__."/tools/api_conf.php");

if (isset($_GET['tab']) && !empty($_GET['tab'])) {
    $currentTab = $_GET['tab'];
    if (isset($_GET['page']) && !empty($_GET['page'])) {
        if ($currentTab == "current") {
            $currentPageNumber = $_GET['page'];
            $allWarsPageNumber = 1;
        } else {
            $currentPageNumber = 1;
            $allWarsPageNumber = $_GET['page'];
        }
    } else {
        $currentPageNumber = 1;
        $allWarsPageNumber = 1;
    }
} else {
    $currentTab = "current";
    $currentPageNumber = 1;
    $allWarsPageNumber = 1;
}
$lastUpdated = getLastUpdated($db, "war_decks");
$state = getWarStateFromApi($api);

function getDeckLink($deck)
{
    $pattern = "https://link.clashroyale.com/deck/fr?deck=%d;%d;%d;%d;%d;%d;%d;%d";
    $crIds = explode(",", $deck['cr_ids']);
    return sprintf($pattern, $crIds[0], $crIds[1], $crIds[2], $crIds[3], $crIds[4], $crIds[5], $crIds[6], $crIds[7]);
}

function getAllCards($db)
{
    $allCards = [];
    foreach (getAllWarDecks($db) as $deck) {
        $crIds = explode(",", $deck['cr_ids']);
        for ($i = 0; $i <= 7; $i++) {
            array_push($allCards, intval($crIds[$i]));
        }
    }
    return $allCards;
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
                    $('#navbar').collapse('hide');
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
        <?php
        if ($state == 'warDay' && $currentTab == "current"):
            print '<li role="presentation" class="active"><a href="#current" data-toggle="tab" class="tab-link">Guerre en cours</a></li>';
        elseif ($state == 'warDay'):
            print '<li role="presentation"><a href="#current" data-toggle="tab" class="tab-link">Guerre en cours</a></li>';
        endif;

        if ($state != 'warDay' || $currentTab == "allWar"):
            print '<li role="presentation" class="active"><a href="#allWar" data-toggle="tab" class="tab-link">Toutes les guerres</a></li>';
        else:
            print '<li role="presentation"><a href="#allWar" data-toggle="tab" class="tab-link">Toutes les guerres</a></li>';
        endif;

        if ($currentTab == "favCards"):
            print '<li role="presentation" class="active"><a href="#favCards" data-toggle="tab" class="tab-link">Cartes favorites</a></li>';
        else:
            print '<li role="presentation"><a href="#favCards" data-toggle="tab" class="tab-link">Cartes favorites</a></li>';
        endif;
        ?>
    </ul>
    <br>
    <div class="tab-content">
        <?php
        if ($state == 'warDay'):
        if ($currentTab == "current"):
            print '<div class="tab-pane active" id="current">';
        else:
            print '<div class="tab-pane" id="current">';
        endif;

        $counter = 0;
        $pos = 1;
        $allDecks = getAllWarDecksWithPagination($db, true, $currentPageNumber);
        $size = sizeof($allDecks);
        foreach ($allDecks as $deck):
            $cardKeys = explode(",", $deck['card_keys']);
            $deckLink = getDeckLink($deck);
            if ($counter == 0): ?>
                <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <?php
                        for ($i = 0; $i <= 7; $i++): ?>
                            <div class="col-xs-3">
                                <div class="img-responsive">
                                    <img src="images/cards/<?php print $cardKeys[$i] ?>.png"
                                         alt="failed to load img"
                                         class="img-responsive"/>
                                </div>
                            </div>
                        <?php
                        endfor; ?>
                    </div>
                    <div class="second-row">
                        <div id="resultsDiv" class="pointerHand text-center js-result-div">
                            <span class="whiteShadow">Joués : <?php print $deck['played']; ?><br></span>
                            <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                - <?php print round($deck['wins'] / $deck['played'] * 100) ?> %<br></span>
                            <span class="whiteShadow">Couronnes : <?php print $deck['total_crowns']; ?><br></span>
                            <span class="whiteShadow">Coût : <?php print $deck['elixir_cost']; ?></span><br><br>
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
            else: ?>
                <div class="col-md-5 col-md-offset-2">
                    <div class="row">
                        <?php
                        for ($i = 0; $i <= 7; $i++): ?>
                            <div class="col-xs-3">
                                <div class="img-responsive">
                                    <img src="images/cards/<?php print $cardKeys[$i] ?>.png"
                                         alt="failed to load img"
                                         class="img-responsive"/>
                                </div>
                            </div>
                        <?php
                        endfor; ?>
                    </div>
                    <div class="second-row">
                        <div id="resultsDiv" class="pointerHand text-center js-result-div">
                            <span class="whiteShadow">Joués : <?php print $deck['played']; ?><br></span>
                            <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                - <?php print round($deck['wins'] / $deck['played'] * 100) ?> %<br></span>
                            <span class="whiteShadow">Couronnes : <?php print $deck['total_crowns']; ?><br></span>
                            <span class="whiteShadow">Coût : <?php print $deck['elixir_cost']; ?></span><br><br>
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

        $numberOfPages = getNumberOfPages($db, true);
        print '<div class="row">';
        print '<div class="col-md-12 text-center">';
        print '<ul class="pagination pagination-lg">';
        for ($i = 1; $i <= $numberOfPages; $i++):
            if ($i == $currentPageNumber):
                print '<li class="active"><a href="war_decks.php?tab=current&page=' . $i . '">' . $i . '</a></li>';
            else:
                print '<li><a href="war_decks.php?tab=current&page=' . $i . '">' . $i . '</a></li>';
            endif;
        endfor;
        print '</ul>';
        print '</div>';
        print '</div>';
        ?>
    </div>
<?php endif ?>
    <div class="tab-pane <?php if ($state != 'warDay' || $currentTab == "allWar"): print 'active';
    endif; ?>" id="allWar">
        <?php
        $counter = 0;
        $pos = 1;
        $allDecks = getAllWarDecksWithPagination($db, false, $allWarsPageNumber);
        $size = sizeof($allDecks);
        foreach ($allDecks as $deck):
            $cardKeys = explode(",", $deck['card_keys']);
            $deckLink = getDeckLink($deck);
            if ($counter == 0): ?>
                <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <?php
                        for ($i = 0; $i <= 7; $i++): ?>
                            <div class="col-xs-3">
                                <div class="img-responsive">
                                    <img src="images/cards/<?php print $cardKeys[$i] ?>.png"
                                         alt="failed to load img"
                                         class="img-responsive"/>
                                </div>
                            </div>
                        <?php
                        endfor; ?>
                    </div>
                    <div class="second-row">
                        <div id="resultsDiv" class="pointerHand text-center js-result-div">
                            <span class="whiteShadow">Joués : <?php print $deck['played']; ?><br></span>
                            <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                - <?php print round($deck['wins'] / $deck['played'] * 100) ?> %<br></span>
                            <span class="whiteShadow">Couronnes : <?php print $deck['total_crowns']; ?><br></span>
                            <span class="whiteShadow">Coût : <?php print $deck['elixir_cost']; ?></span><br><br>
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
            else: ?>
                <div class="col-md-5 col-md-offset-2">
                    <div class="row">
                        <?php
                        for ($i = 0; $i <= 7; $i++): ?>
                            <div class="col-xs-3">
                                <div class="img-responsive">
                                    <img src="images/cards/<?php print $cardKeys[$i] ?>.png"
                                         alt="failed to load img"
                                         class="img-responsive"/>
                                </div>
                            </div>
                        <?php
                        endfor; ?>
                    </div>
                    <div class="second-row">
                        <div id="resultsDiv" class="pointerHand text-center js-result-div">
                            <span class="whiteShadow">Joués : <?php print $deck['played']; ?><br></span>
                            <span class="whiteShadow">Victoires : <?php print $deck['wins']; ?>
                                - <?php print round($deck['wins'] / $deck['played'] * 100) ?> %<br></span>
                            <span class="whiteShadow">Couronnes : <?php print $deck['total_crowns']; ?><br></span>
                            <span class="whiteShadow">Coût : <?php print $deck['elixir_cost']; ?></span><br><br>
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

        $numberOfPages = getNumberOfPages($db, false);
        print '<div class="row">';
        print '<div class="col-md-12 text-center">';
        print '<ul class="pagination pagination-md">';
        for ($i = 1; $i <= $numberOfPages; $i++):
            if ($i == $allWarsPageNumber):
                print '<li class="active"><a href="war_decks.php?tab=allWar&page=' . $i . '">' . $i . '</a></li>';
            else:
                print '<li><a href="war_decks.php?tab=allWar&page=' . $i . '">' . $i . '</a></li>';
            endif;
        endfor;
        print '</ul>';
        print '</div>';
        print '</div>';
        ?>
    </div>
    <div class="tab-pane <?php if ($currentTab == "favCards"): print 'active'; endif; ?>" id="favCards">
        <?php
        $bestCards = array_count_values(getAllCards($db));
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
                    <div class="row">
                        <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage"
                             class="img-responsive center-block"/>
                    </div>
                    <div class="row">
                        <span class="whiteShadow text-center center-block">Présente dans <?php print $bestCard; ?>
                            decks</span>
                        <br>
                    </div>
                </div>
                <?php
                $counter++;
            elseif ($counter == 1): ?>
                <div class="col-lg-4">
                    <div class="row">
                        <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage"
                             class="img-responsive center-block"/>
                    </div>
                    <div class="row">
                        <span class="whiteShadow text-center center-block">Présente dans <?php print $bestCard; ?>
                            decks</span>
                        <br>
                    </div>
                </div>
                <?php
                $counter++;
            else: ?>
                <div class="col-lg-4">
                    <div class="row">
                        <img src="images/cards/<?php print $cardKey; ?>.png" alt="cardImage"
                             class="img-responsive center-block"/>
                    </div>
                    <div class="row">
                        <span class="whiteShadow text-center center-block">Présente dans <?php print $bestCard; ?>
                            decks</span>
                        <br>
                    </div>
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
    <img id="loaderImg" src="images/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>
