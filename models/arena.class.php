<?php

class Arena {
    private $arenaId = null;
    private $name = null;
    private $league = null;
    private $trophyLimit = null;

    public function __construct($sId, $sName, $sLeague, $sTrophyLimit) {
        $this->arenaId = $sId;
        $this->name = $sName;
        $this->league = $sLeague;
        $this->trophyLimit = $sTrophyLimit;
    }

    public function getId() {
        return $this->arenaId;
    }

    public function getName() {
        return $this->name;
    }

    public function getLeague() {
        return $this->league;
    }

    public function getTrophyLimit() {
        return $this->trophyLimit;
    }
}