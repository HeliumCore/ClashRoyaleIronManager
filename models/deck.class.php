<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/08/18
 * Time: 14:15
 */

require 'card.class.php';

class Deck {
    private $id = null;
    private $cards = null;
    private $elixirCost = null;
    private $deckLink = null;

    public function __construct($crIds, $keys, $cost) {
        $crIdsArray = explode(",", $crIds);
        $keysArray = explode(",", $keys);
        $cards = array();

        for ($i = 0; $i <= 7; $i++) {
            $card = new Card($crIdsArray[$i], $keysArray[$i]);
            array_push($cards, $card);
        }

        $deckLinkPattern = "https://link.clashroyale.com/deck/fr?deck=%d;%d;%d;%d;%d;%d;%d;%d";
        $deckLink = sprintf(
            $deckLinkPattern, $crIdsArray[0], $crIdsArray[1], $crIdsArray[2],
            $crIdsArray[3], $crIdsArray[4], $crIdsArray[5], $crIdsArray[6],
            $crIdsArray[7]
        );
        
        $this->deckLink = $deckLink;
        $this->cards = $cards;
        $this->elixirCost = $cost;
    }

    public function getCards() {
        return $this->cards;
    }

    public function getElixirCost() {
        return $this->elixirCost;
    }

    public function getDeckLink() {
        return $this->deckLink;
    }
}