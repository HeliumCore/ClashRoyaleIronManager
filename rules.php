<?php
require('tools/bootstrap.php');

$isLogged = false;
$isAdmin = false;
$playerName = "";

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
    $playerName = getPlayerTagByAccountId($db, $accountId)['name'];
}

MVCEngine::addScript("main");
MVCEngine::setTitle('Réglement');
MVCEngine::assign('isAdmin', $isAdmin);
MVCEngine::assign('isLogged', $isLogged);
MVCEngine::assign('playerName', $playerName);
MVCEngine::render();
