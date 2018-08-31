<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/08/18
 * Time: 14:19
 */

class Pause {
    private $accountId = null;
    private $playerName = null;
    private $pauseDate = null;

    public function __construct($accountId, $playerName, $pauseDate) {
        $this->accountId = $accountId;
        $this->playerName = $playerName;
        $this->pauseDate = $pauseDate;
    }

    public function getAccountId() {
        return $this->accountId;
    }

    public function setAccountId($accountId) {
        $this->accountId = $accountId;
    }

    public function getPauseDate() {
        return $this->pauseDate;
    }

    public function setPauseDate($pauseDate) {
        $this->pauseDate = $pauseDate;
    }

    public function getPlayerName() {
        return $this->playerName;
    }

    public function setPlayerName($playerName) {
        $this->playerName = $playerName;
    }
}