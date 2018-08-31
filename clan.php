<?php
require('tools/bootstrap.php');
require('models/clan.class.php');

$isLogged = false;
$isAdmin = false;
$playerName = "";

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
    $playerName = getPlayerTagByAccountId($db, $accountId)['name'];
}

$oClan = new Clan();
MVCEngine::addScript("main");
MVCEngine::setTitle('Clan');
MVCEngine::assign('clan', $oClan);
MVCEngine::assign('clanPlayers', $oClan->getPlayers());
MVCEngine::assign('lastUpdated', $oClan->getLastUpdated());
MVCEngine::assign('allowUpdate', true);
MVCEngine::assign('isAdmin', $isAdmin);
MVCEngine::assign('isLogged', $isLogged);
MVCEngine::assign('playerName', $playerName);
MVCEngine::render();
