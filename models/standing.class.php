<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/08/18
 * Time: 14:23
 */

class Standing {
    private $rank = null;
    private $name = null;
    private $participants = null;
    private $battlePlayed = null;
    private $battleWon = null;
    private $crowns = null;
    private $warTrophies = null;

    public function __construct($rank, $name, $participants, $battlePlayed, $battleWon, $crowns, $warTrophies) {
        $this->rank = $rank;
        $this->name = $name;
        $this->participants = $participants;
        $this->battlePlayed = $battlePlayed;
        $this->battleWon = $battleWon;
        $this->crowns = $crowns;
        $this->warTrophies = $warTrophies;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getParticipants() {
        return $this->participants;
    }

    public function setParticipants($participants) {
        $this->participants = $participants;
    }

    public function getBattlePlayed() {
        return $this->battlePlayed;
    }

    public function setBattlePlayed($battlePlayed) {
        $this->battlePlayed = $battlePlayed;
    }

    public function getBattleWon() {
        return $this->battleWon;
    }

    public function setBattleWon($battleWon) {
        $this->battleWon = $battleWon;
    }

    public function getCrowns() {
        return $this->crowns;
    }

    public function setCrowns($crowns) {
        $this->crowns = $crowns;
    }

    public function getWarTrophies() {
        return $this->warTrophies;
    }

    public function setWarTrophies($warTrophies) {
        $this->warTrophies = $warTrophies;
    }

    public function getRank() {
        return $this->rank;
    }

    public function setRank($rank) {
        $this->rank = $rank;
    }
}