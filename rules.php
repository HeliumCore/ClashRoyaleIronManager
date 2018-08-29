<?php
require('tools/bootstrap.php');

$isLogged = false;
$isAdmin = false;

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
}

MVCEngine::setTitle('Réglement');
MVCEngine::assign('isAdmin',    true);
MVCEngine::assign('isLogged',    true);
MVCEngine::render();
