<?php
require('tools/bootstrap.php');
require('models/war.class.php');

//TODO quand il y a un compte, mettre en haut du tableau la ligne concernant le joueur connecté

$isLogged = false;
$isAdmin = false;
$playerName = "";

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
    $isLogged = true;
    $playerName = getPlayerTagByAccountId($db, $accountId)['name'];
}

$war = new War();
$war->getWarPlayers();
$war->setId($war->getLastWarNumber());

// API
$apiWar = $war->getWarFromApi();
$war->setState($apiWar['state']);
if ($war->getState() == "collectionDay") {
    $war->setStateName("Jour de collection");
    $war->setEndTime($apiWar['collectionEndTime']);

    $totalCollectionPlayed = 0;
    $totalCollectionWon = 0;
    $totalCardsEarned = 0;
    $totalBattlesPlayed = 0;
    $totalBattlesWon = 0;
    $missingPlayers = 0;
    $clanResult = new Player("clan");
    foreach ($war->getResults() as $playerWar) {
        $totalCollectionPlayed += $playerWar->getCollectionPlayed();
        $totalCollectionWon += $playerWar->getCollectionWon();
        $totalCardsEarned += $playerWar->getCardsEarned();
        $totalBattlesPlayed += $playerWar->getBattlePlayed();
        $totalBattlesWon += $playerWar->getBattleWon();
        if ($playerWar->getCollectionPlayed() == 0) {
            $missingPlayers++;
        }
    }
    $clanResult->setCollectionPlayed($totalCollectionPlayed);
    $clanResult->setCollectionWon($totalCollectionWon);
    $clanResult->setCardsEarned($totalCardsEarned);
    $clanResult->setBattlePlayed($totalBattlesPlayed);
    $clanResult->setBattleWon($totalBattlesWon);
    $numberOfParticipants = sizeof($war->getResults()) - $missingPlayers;
} else if ($war->getState() == "warDay") {
    $war->setStateName("Jour de guerre");
    $war->setStandings($war->getCurrentWarStandings());
    $war->setEndTime($apiWar['warEndTime']);
}
$currentTrophies = $apiWar['clan']['clanScore'];

MVCEngine::addScript("main");
MVCEngine::setTitle('Guerre en cours');
MVCEngine::assign('war', $war);
MVCEngine::assign('clanResult', $clanResult);
MVCEngine::assign('currentTrophies', $currentTrophies);
MVCEngine::assign('numberOfParticipants', $numberOfParticipants);
MVCEngine::assign('missingPlayers', $missingPlayers);
MVCEngine::assign('lastUpdated', $war->getLastUpdated());
MVCEngine::assign('allowUpdate', true);
MVCEngine::assign('isAdmin', $isAdmin);
MVCEngine::assign('isLogged', $isLogged);
MVCEngine::assign('playerName', $playerName);
MVCEngine::render();