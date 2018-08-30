<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/08/18
 * Time: 14:24
 */

require 'player.class.php';
require 'standing.class.php';

class War {
    private $id = null;
    private $results = null;
    private $standings = null;
    private $endTime = null;
    private $state = null;
    private $stateName = null;
    private $maxBattle = null;

    /**
     * Permet de récupérer les resultats de la guerre
     */
    public function getWarPlayers() {
        $query = "
            SELECT players.rank, players.tag, players.name, player_war.battle_played,
            player_war.battle_won, player_war.collection_played, player_war.collection_won, player_war.cards_earned
            FROM player_war
            INNER JOIN war ON war.id = player_war.war_id
            INNER JOIN players ON players.id = player_war.player_id
            WHERE war.past_war = 0
            ORDER BY players.rank ASC
        ";

        $playerList = array();
        foreach ($GLOBALS['db']->query($query)->fetchAll() as $result) {
            $player = new Player($result['tag']);
            $player->setRank($result['rank']);
            $player->setName($result['name']);
            $player->setBattlePlayed($result['battle_played']);
            $player->setBattleWon($result['battle_won']);
            $player->setCollectionPlayed($result['collection_played']);
            $player->setCollectionWon($result['collection_won']);
            $player->setCardsEarned($result['cards_earned']);
            array_push($playerList, $player);
        }
        $this->results = $playerList;
    }

    /**
     * @return int le numéro de la dernière guerre
     */
    public function getLastWarNumber() {
        $result = $GLOBALS['db']->query("
            SELECT COUNT(w.id) as warNumber
            FROM war w
            WHERE w.id > 24
        ")->fetch();
        if (!empty($result['warNumber'])) {
            return $result['warNumber'];
        }
        return null;
    }

    /**
     * @return string Date de dernière mise à jour de la guerre
     */
    public function getLastUpdated() {
        $result = $GLOBALS['db']->query("
            SELECT updated
            FROM last_updated
            WHERE page_name = 'war'
        ")->fetch();
        if (!empty($result['updated'])) {
            return strtotime($result['updated']);
        }
        return null;
    }

    /**
     * Permet de récupérer les résultats des autres clans pendant le jour de guerre
     */
    public function getCurrentWarStandings() {
        $query = "
            SELECT standings.name, participants, battles_played, battles_won, crowns, war_trophies
            FROM standings
            JOIN war ON standings.war_id = war.id AND war.past_war = 0
            ORDER BY battles_won DESC, crowns DESC
        ";

        $standingList = array();
        $maxBattles = 0;
        $rank = 1;
        $lastBattlesWon = 0;
        $lastCrowns = 0;

        foreach ($GLOBALS['db']->query($query)->fetchAll() as $res) {
            if ($lastCrowns == $res['crowns'] && $lastBattlesWon == $res['battles_won'])
                $rank--;

            $standing = new Standing($rank, $res['name'], $res['participants'], $res['battles_played'], $res['battles_won'], $res['crowns'], $res['war_trophies']);
            array_push($standingList, $standing);

            $rank++;
            $lastBattlesWon = $res['battles_won'];
            $lastCrowns = $res['crowns'];

            if ($res['participants'] > $maxBattles) {
                $maxBattles = $res['participants'];
            }
        }
        $this->maxBattle = $maxBattles;
        return $standingList;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getResults() {
        return $this->results;
    }

    public function setResults($results) {
        $this->results = $results;
    }

    public function getStandings() {
        return $this->standings;
    }

    public function setStandings($standings) {
        $this->standings = $standings;
    }

    public function getEndTime() {
        return $this->endTime;
    }

    public function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    public function getState() {
        return $this->state;
    }

    public function setState($state) {
        $this->state = $state;
    }

    public function getStateName() {
        return $this->stateName;
    }

    public function setStateName($stateName) {
        $this->stateName = $stateName;
    }

    public function getMaxBattle() {
        return $this->maxBattle;
    }

    public function setMaxBattle($maxBattle) {
        $this->maxBattle = $maxBattle;
    }
}