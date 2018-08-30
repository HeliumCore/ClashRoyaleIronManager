<?php
require('tools/bootstrap.php');
require('models/player.class.php');

// TODO : Faire le "glow" multicolor autour des légendaires

// TODO voir le probleme de la typo selon la taille

// TODO revoir le tableau de guerre (style /war)

// TODO afficher un graph de progression trophée

// TODO enregistrer les coffres en bases systematiquement pour pouvoir ressortir une liste en cas d'API DOWN

$playerTag = explode("/", substr($_SERVER['REQUEST_URI'], 1))[1];
if (isset($_GET['tag'])) $playerTag = $_GET['tag'];

if (empty($playerTag)) header('Location: /clan');

$isLogged = false;
$isAdmin = false;

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
}

$player = new Player($playerTag);
MVCEngine::addScript("main");
MVCEngine::setTitle('Infos du joueur');
MVCEngine::assign('player',         $player->getPlayerInfos());
MVCEngine::assign('lastUpdated',    $player->getLastUpdated());
MVCEngine::assign('allowUpdate',    true);
MVCEngine::assign('isAdmin',    $isAdmin);
MVCEngine::assign('isLogged',    $isLogged);
MVCEngine::render();
