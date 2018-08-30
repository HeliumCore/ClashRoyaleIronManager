<?php
require('tools/bootstrap.php');
require('models/clan.class.php');
require("check_login.php");

$isLogged = false;
$isAdmin = false;
if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
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
MVCEngine::render();
