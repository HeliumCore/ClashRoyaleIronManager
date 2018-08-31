<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 07/08/18
 * Time: 17:02
 */

require('tools/bootstrap.php');
require('models/admin.class.php');

$isLogged = false;
$isAdmin = false;
$playerName = "";

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
    $playerName = getPlayerTagByAccountId($db, $accountId)['name'];
}

if (!$isAdmin)
    header("Location: /login");

$admin = new Admin();
$admin->setPlayerPauses();

MVCEngine::addScript("main");
MVCEngine::setTitle('Administration');
MVCEngine::assign('pauses', $admin->getPlayerPauses());
MVCEngine::assign('isAdmin', $isAdmin);
MVCEngine::assign('isLogged', $isLogged);
MVCEngine::assign('playerName', $playerName);
MVCEngine::render();

//TODO ajouter/calculer les status de stats de guerre pour les afficher ici directement, avoir une meilleure liste