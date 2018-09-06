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

    public function getWarFromApi() {
        $url = "clans/%239RGPL8PC/currentwar";
        return ClashRoyaleApi::getRequest($url);
    }

    public function getNumberOfCurrentPlayersInWar() {
        $query = "
            SELECT COUNT(player_war.id) as numberOfCurrentPlayers
            FROM player_war
            JOIN war ON player_war.war_id = war.id
            WHERE war.past_war = 0
        ";

        return intval($GLOBALS['db']->query($query)->fetch(['numberOfCurrentPlayers']));
    }

    public function getNotEligiblePlayers() {
        $query = "
            SELECT players.id
            FROM players
            WHERE players.id NOT IN
            (
              SELECT pw.player_id
              FROM player_war pw
              JOIN war ON pw.war_id = war.id
              WHERE war.past_war = 0
            )
            AND players.in_clan = 1
        ";
        return $GLOBALS['db']->query($query)->fetchAll();
    }

    public function getCurrentWarId() {
        $currentWar = 0;
        if ($this->isWarStarted())
            $currentWar = $this->getCurrentWar();

        if (is_array($currentWar))
            $this->id = intval($currentWar['id']);
        else
            $this->id = $this->insertNewWar();
    }

    public function isWarStarted() {
        $query = "
            SELECT COUNT(player_war.id) as numberOfCurrentPlayers
            FROM player_war
            JOIN war ON player_war.war_id = war.id
            WHERE war.past_war = 0
        ";
        return $GLOBALS['db']->query($query)->fetch();
    }

    public function getCurrentWar() {
        $query = "
            SELECT id
            FROM war
            WHERE past_war = 0
            LIMIT 1
        ";
        return $GLOBALS['db']->query($query)->fetch();
    }

    public function insertNewWar() {
        $query = "
            INSERT INTO war
            VALUES ('', 0, 0, 0)
        ";
        $GLOBALS['db']->query($query)->execute();
        return $GLOBALS['db']->lastInsertId();
    }

    public function getPlayerWar($playerId) {
        $pattern = "
            SELECT player_war.id as player_war_id, cards_earned, collection_played, collection_won, battle_played, battle_won
            FROM player_war
            WHERE player_id = %d
            AND war_id = %d
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $playerId, $this->id))->fetch();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function insertCollectionDay($cardsEarned, $battlesPlayed, $wins, $playerId) {
        $pattern = "
            INSERT INTO player_war (cards_earned, collection_played, collection_won, player_id, war_id)
            VALUES (%d, %d, %d, %d, %d)
        ";
        $GLOBALS['db']->query(sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $this->id))->execute();
    }

    public function updateCollectionDay($cardsEarned, $battlesPlayed, $wins, $id) {
        $pattern = "
            UPDATE player_war
            SET cards_earned = %d, collection_played = %d, collection_won = %d
            WHERE id = %d
        ";
        $GLOBALS['db']->query(sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $id))->execute();
    }

    public function updateWarDay($battlesPlayed, $wins, $id) {
        $pattern = "
            UPDATE player_war
            SET battle_played = %d, battle_won = %d
            WHERE id = %d
        ";
        $GLOBALS['db']->query(sprintf($pattern, $battlesPlayed, $wins, $id))->execute();
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

    public function setLastUpdated() {
        $query = "
            UPDATE last_updated
            SET updated = NOW()
            WHERE page_name = \"war\"
        ";
        $GLOBALS['db']->query($query)->execute();
    }

    public function getStanding($tag) {
        $pattern = "
            SELECT id
            FROM standings
            WHERE tag = \"%s\"
            AND war_id = %d
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $tag, $this->id))->fetch();
    }

    public function updateStanding($participants, $battlesPlayed, $wins, $crowns, $warTrophies, $id) {
        $pattern = "
            UPDATE standings
            SET participants = %d,
            battles_played = %d,
            battles_won = %d,
            crowns = %d,
            war_trophies = %d
            WHERE id = %d
        ";
        $GLOBALS['db']->query(sprintf($pattern, $participants, $battlesPlayed, $wins, $crowns, $warTrophies, $id))->execute();
    }

    public function insertStanding($tag, $name, $participants, $battlesPlayed, $wins, $crowns, $warTrophies) {
        $pattern = "
            INSERT INTO standings (tag, standings.name, participants, battles_played, battles_won, crowns, war_trophies, war_id)
            VALUES (\"%s\", \"%s\", %d, %d, %d, %d, %d, %d)
        ";
        $clanName = utf8_decode($name);
        if (strpos(trim($clanName), '???') !== false)
            $clanName = "Arabe/Chinois";

        $GLOBALS['db']->query(sprintf($pattern, $tag, $clanName, $participants, $battlesPlayed, $wins, $crowns, $warTrophies, $this->id))->execute();

    }


    public function updateStandings($standings) {
        foreach ($standings as $standing) {
            $getStanding = $this->getStanding($standing['tag']);

            if (is_array($getStanding)) {
                $this->updateStanding($standing['participants'], $standing['battlesPlayed'], $standing['wins'], $standing['crowns'],
                    $standing['warTrophies'], $getStanding['id']);
            } else {
                $this->insertStanding($standing['tag'], $standing['name'], $standing['participants'], $standing['battlesPlayed'],
                    $standing['wins'], $standing['crowns'], $standing['warTrophies']);
            }
        }
    }
}