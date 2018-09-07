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

    public function __construct($crId = null, $key = null) {
        $this->crId = $crId;
        $this->key = $key;
    }

    public function getCrId() {
        return $this->crId;
    }

    public function getKey() {
        return $this->key;
    }

    public function updateAllCards() {
        $cards = $this->getAllCardsFromApi();
        if ($cards == false)
            return;

        foreach ($cards as $card) {
            $key = $this->formatCardKey($card['name']);
            if (is_array($this->getCardByKey($key))) {
                $this->updateCard($key, $card['name'], $card['id']);
            } else {
                $this->insertCard($key, $card['name'], $card['id']);
            }
        }
    }

    public function formatCardKey($cardName) {
        $key = str_replace(".", "", $cardName);
        $key = str_replace(" ", "-", $key);
        return strtolower($key);
    }

    public function getAllCardsFromApi() {
        $url = "cards";
        return ClashRoyaleApi::getRequest($url)['items'];
    }

    public function getCardByKey($key) {
        $pattern = "
            SELECT id
            FROM cards
            WHERE cards.card_key = \"%s\"
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $key))->fetch();
    }

    public function updateCard($key, $name, $crId) {
        $pattern = "
            UPDATE cards
            SET cards.name = \"%s\",
            cr_id = %d
            WHERE cards.card_key = \"%s\"
        ";

        $GLOBALS['db']->query(utf8_decode(sprintf($pattern, $name, $crId, $key)));
    }

    public function insertCard($key, $name, $crId) {
        $pattern = "
            INSERT INTO cards (card_key, cards.name, cr_id)
            VALUES(\"%s\", \"%s\", %d)
        ";

        $GLOBALS['db']->query()->execute(utf8_decode(sprintf($pattern, $key, $name, $crId)));
    }
}