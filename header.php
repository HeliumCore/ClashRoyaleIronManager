<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/index.php">Iron</a>
        </div>
        <?php
        $BASE = "https://ironmanager.fr";
        ?>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="<?php print $BASE; ?>/clan">Clan</a></li>
                <li class="dropdown pointerHand">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="<?php print $BASE; ?>/war">Guerre<span
                                class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-li"><a href="<?php print $BASE; ?>/war">Guerre en cours</a></li>
                        <li class="dropdown-li"><a href="<?php print $BASE; ?>/war_decks">Decks</a></li>
                        <li class="dropdown-li"><a href="<?php print $BASE; ?>/war_stats">Statistiques</a></li>
                    </ul>
                </li>
                <li><a href="<?php print $BASE; ?>/rules">Réglement</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right pointerHand">

                <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])): ?>
                    <li class="dropdown pointerHand">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="<?php print $BASE; ?>/account_manager">Compte<span
                                    class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-li"><a href="<?php print $BASE; ?>/account_manager">Gestion du
                                    compte</a></li>
                            <li class="dropdown-li"><a href="<?php print $BASE; ?>/index/logout">Se deconnecter</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="<?php print $BASE; ?>/login">Se connecter</a></li>
                <?php endif;
                $uri = $_SERVER['REQUEST_URI'];
                if (strpos($uri, 'player') !== false) {
                    $path = explode("/", substr($uri, 1))[0];
                } else {
                    //TODO revoir ca apres rewrite rule
                    $path = explode(".php", substr($uri, 1))[0];
                }
                $allowedUpdate = array("clan", "player", "war", "war_stats", "war_decks");
                if (in_array($path, $allowedUpdate)):
                    ?>
                    <li>
                        <a class="pointerHand" onclick="update()">
                            Mise à jour&nbsp;<img src="/images/ui/reload.png" class="reload-image">
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>