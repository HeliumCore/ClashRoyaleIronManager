<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/08/18
 * Time: 14:15
 */

class Card {
    private $id = null;
    private $crId = null;
    private $key = null;
    private $name = null;
    private $cost = null;
    private $rarity = null;
    private $arena = null;
    private $type = null;

    public function __construct($crId, $key) {
        $this->crId = $crId;
        $this->key = $key;
    }

    public function getCrId() {
        return $this->crId;
    }

    public function getKey() {
        return $this->key;
    }
}