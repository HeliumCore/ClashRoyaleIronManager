<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 08/07/2018
 * Time: 14:38
 */

require('tools/bootstrap.php');
require('models/war.class.php');

$isLogged = false;
$isAdmin = false;
$playerName = "";

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
    $playerName = getPlayerTagByAccountId($db, $accountId)['name'];
}

if (isset($_GET['tab']) && !empty($_GET['tab'])) {
    $currentTab = $_GET['tab'];
    if (isset($_GET['page']) && !empty($_GET['page'])) {
        if ($currentTab == "current") {
            $currentPageNumber = $_GET['page'];
            $allWarsPageNumber = 1;
        } else {
            $currentPageNumber = 1;
            $allWarsPageNumber = $_GET['page'];
        }
    } else {
        $currentPageNumber = 1;
        $allWarsPageNumber = 1;
    }
} else {
    $currentTab = "current";
    $currentPageNumber = 1;
    $allWarsPageNumber = 1;
}

$war = new War();
$apiWar = $war->getWarFromApi();
$war->setState($apiWar['state']);
$warDecks = $war->getAllWarDecksWithPagination(true, $currentPageNumber);
$allDecks = $war->getAllWarDecksWithPagination(false, $allWarsPageNumber);

MVCEngine::addScript("main");
MVCEngine::setTitle('Decks de guerre');
MVCEngine::assign('war', $war);
MVCEngine::assign('currentTab', $currentTab);
MVCEngine::assign('warDecks', $warDecks);
MVCEngine::assign('warDecksSize', sizeof($warDecks));
MVCEngine::assign('warDeckPages', $war->getWarDecksPage());
MVCEngine::assign('currentPageNumber', $currentPageNumber);
MVCEngine::assign('allDecks', $allDecks);
MVCEngine::assign('allDecksSize', sizeof($allDecks));
MVCEngine::assign('allDeckPages', $war->getAllDecksPage());
MVCEngine::assign('allWarsPageNumber', $allWarsPageNumber);
MVCEngine::assign('favCards', $war->getFavCards());
MVCEngine::assign('lastUpdated', $war->getLastUpdatedWarDecks());
MVCEngine::assign('allowUpdate', true);
MVCEngine::assign('isAdmin', $isAdmin);
MVCEngine::assign('isLogged', $isLogged);
MVCEngine::assign('playerName', $playerName);
MVCEngine::render();
