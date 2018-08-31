<?php

class Account {
    private $id = null;
    private $playerId = null;
    private $password = null;
    private $lastVisit = null;

    public function __construct($id, $playerId) {
        $this->id = $id;
        $this->playerId = $playerId;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getPlayerId() {
        return $this->playerId;
    }

    public function setPlayerId($playerId) {
        $this->playerId = $playerId;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getLastVisit() {
        return $this->lastVisit;
    }

    public function setLastVisit($lastVisit) {
        $this->lastVisit = $lastVisit;
    }
}