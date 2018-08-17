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
            <a class="navbar-brand" href="/index">Iron</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="/clan">Clan</a></li>
                <li class="dropdown pointerHand">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="/war">Guerre<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-li"><a href="/war">Guerre en cours</a></li>
                        <li class="dropdown-li"><a href="/war_decks">Decks</a></li>
                        <li class="dropdown-li"><a href="/war_stats">Statistiques</a></li>
                    </ul>
                </li>
                <li><a href="/rules">Réglement</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right pointerHand">
                <?php
                $path = explode("/", substr($_SERVER['REQUEST_URI'], 1))[0];
                $allowedUpdate = array("clan", "player", "war", "war_stats", "war_decks");
                if (in_array($path, $allowedUpdate)):
                    ?>
                    <li>
                        <a class="pointerHand" onclick="update()">
                            Mise à jour&nbsp;<img src="/images/ui/reload.png" class="reload-image">
                        </a>
                    </li>
                <?php endif;

                /* Err -> headers already been sent !
                if (session_status() == PHP_SESSION_NONE)
                    session_start();
                */

                if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])):
                    $accountId = intval($_SESSION['accountId']);
                    $isAdmin = isAccountAdmin($db, $accountId);

                    if ($isAdmin):
                        ?>
                        <li><a href="/admin">Admin</a></li>
                    <?php endif; ?>
                    <li class="dropdown pointerHand">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="/account_manager">Compte<span
                                    class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-li"><a href="/account_manager">Gestion du compte</a></li>
                            <li class="dropdown-li"><a href="/index/logout">Se deconnecter</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="/login">Se connecter</a></li>
                <?php endif; ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>