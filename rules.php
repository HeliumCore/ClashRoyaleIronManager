<?php
require('tools/bootstrap.php');

$isLogged = false;
$isAdmin = false;

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
}

MVCEngine::addScript("main");
MVCEngine::setTitle('Réglement');
MVCEngine::assign('isAdmin', $isAdmin);
MVCEngine::assign('isLogged', $isLogged);
MVCEngine::render();
