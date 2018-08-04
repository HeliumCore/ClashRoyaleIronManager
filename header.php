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
            <a class="navbar-brand" href="index.php">Iron</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="clan.php">Clan</a></li>
                <li class="dropdown pointerHand">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="war.php">Guerre<span
                                class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-li"><a href="war.php">Guerre en cours</a></li>
                        <li class="dropdown-li"><a href="war_decks.php">Decks</a></li>
                        <li class="dropdown-li"><a href="war_stats.php">Statistiques</a></li>
                    </ul>
                </li>
                <li><a href="rules.php">Réglement</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right pointerHand">

                <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])): ?>
                    <li class="dropdown pointerHand">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Compte<span
                                    class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-li"><a href="account_manager.php">Gestion du compte</a></li>
                            <li class="dropdown-li"><a href="index.php?logout">Se deconnecter</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Se connecter</a></li>
                <?php endif; ?>
                <li>
                    <a class="pointerHand" onclick="update()">
                        Mise à jour&nbsp;<img src="images/ui/reload.png" class="reload-image">
                    </a>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>