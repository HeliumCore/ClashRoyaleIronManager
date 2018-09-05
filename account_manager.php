<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 03/08/18
 * Time: 10:22
 */

require('tools/bootstrap.php');

//TODO refaire le design du calendrier

//TODO trouver un moyen d'envoyer des push sur mobile
// Settings de notif dans l'account manager (guerre, echanges, etc...)

if (!isset($_SESSION['accountId']) || empty($_SESSION['accountId']))
    header('Location: /login');

$accountId = intval($_SESSION['accountId']);
$isAdmin = isAccountAdmin($db, $accountId);
$playerInfos = getPlayerInfoByAccountId($db, $_SESSION['accountId']);

MVCEngine::addScript("main");
MVCEngine::setTitle('Gestion du compte');
MVCEngine::assign('playerTag', $playerInfos['tag']);
MVCEngine::assign('playerName', $playerInfos['name']);
MVCEngine::assign('isAdmin', $isAdmin);
MVCEngine::assign('isLogged', true);
MVCEngine::render();