<?php
require('tools/bootstrap.php');

require('models/clan.class.php');

$oClan = new Clan();
MVCEngine::setTitle('Clan');
MVCEngine::assign('clan',           $oClan);
MVCEngine::assign('clanPlayers',    $oClan->getPlayers());
MVCEngine::assign('lastUpdated',    $oClan->getLastUpdated());
MVCEngine::render();
