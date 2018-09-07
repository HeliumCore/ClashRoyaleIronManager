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
    private $warDecksPage = null;
    private $allDecksPage = null;

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

    public function getWarLogFromApi() {
        $url = "clans/%239RGPL8PC/warlog";
        return ClashRoyaleApi::getRequest($url);
    }

    public function getWarFromApi() {
        $url = "clans/%239RGPL8PC/currentwar";
        return ClashRoyaleApi::getRequest($url);
    }

    public function getLastWarFromApi() {
        $url = "clans/%239RGPL8PC/warlog?limit=1";
        return ClashRoyaleApi::getRequest($url)['items'][0];
    }

    public function getWarId($war, $currentWar, $created, $season = null) {
        $insertWarPattern = "
            INSERT INTO war
            VALUES ('', %d, 0, %d)
        ";

        $updateCurrentWarPattern = "
            UPDATE war
            SET created = %d,
            past_war = 1,
            season = %d
            WHERE id = %d
        ";

        if (!is_array($war)) {
            if (!is_array($currentWar)) {
                $GLOBALS['db']->query(sprintf($insertWarPattern, $created, $season));
                return $GLOBALS['db']->lastInsertId();
            } else {
                $GLOBALS['db']->query(sprintf($updateCurrentWarPattern, $created, $season, intval($currentWar['id'])));
                return $this->getWarID($this->getWar($created), $currentWar, $created, $season);
            }
        } else {
            return intval($war['id']);
        }
    }

    public function getWarFromCreated($created) {
        $pattern = "
            SELECT id
            FROM war
            WHERE created = %d
        ";

        return $GLOBALS['db']->query(sprintf($pattern, $created))->fetch();
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
        $GLOBALS['db']->query($query);
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

    public function getLastWarEndDate() {
        $query = "
            SELECT id, created
            FROM war
            WHERE past_war = 1
            ORDER BY id DESC
            LIMIT 1
        ";
        return $GLOBALS['db']->query($query)->fetch()['created'];
    }

    public function insertCollectionDay($cardsEarned, $battlesPlayed, $wins, $playerId) {
        $pattern = "
            INSERT INTO player_war (cards_earned, collection_played, collection_won, player_id, war_id)
            VALUES (%d, %d, %d, %d, %d)
        ";
        $GLOBALS['db']->query(sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $this->id));
    }

    public function updateCollectionDay($cardsEarned, $battlesPlayed, $wins, $id) {
        $pattern = "
            UPDATE player_war
            SET cards_earned = %d, collection_played = %d, collection_won = %d
            WHERE id = %d
        ";
        $GLOBALS['db']->query(sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $id));
    }

    public function updateWarDay($battlesPlayed, $wins, $id) {
        $pattern = "
            UPDATE player_war
            SET battle_played = %d, battle_won = %d
            WHERE id = %d
        ";
        $GLOBALS['db']->query(sprintf($pattern, $battlesPlayed, $wins, $id));
    }

    public function updateWarLog($cardsEarned, $battlesPlayed, $wins, $playerId, $warId) {
        $updatePattern = "
            UPDATE player_war
            SET cards_earned = %d, battle_played = %d, battle_won = %d
            WHERE player_id = %d
            AND war_id = %d
        ";

        $insertPattern = "
            INSERT INTO player_war (cards_earned, battle_played, battle_won, player_id, war_id)
            VALUE (%d, %d, %d, %d, %d)
        ";

        $getPattern = "
            SELECT player_war.id as player_war_id, cards_earned, collection_played, collection_won, battle_played, battle_won
            FROM player_war
            WHERE player_id = %d
            AND war_id = %d
        ";

        $warResult = $GLOBALS['db']->query(sprintf($getPattern, $playerId, $warId))->fetch();
        if ($warResult == false) {
            $GLOBALS['db']->query(sprintf($insertPattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));
        } else {
            $GLOBALS['db']->query(sprintf($updatePattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));
        }
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
        $GLOBALS['db']->query($query);
    }

    public function setLastUpdatedWarLog() {
        $query = "
            UPDATE last_updated
            SET updated = NOW()
            WHERE page_name = \"war_stats\"
        ";
        $GLOBALS['db']->query($query);
    }

    public function setLastUpdatedWarDecks() {
        $query = "
            UPDATE last_updated
            SET updated = NOW()
            WHERE page_name = \"war_decks\"
        ";
        $GLOBALS['db']->query($query);
    }

    public function updateStandings($standings) {
        foreach ($standings as $standing) {
            $getStanding = $this->getStanding(ltrim($standing['tag'], "#"));

            if (is_array($getStanding)) {
                $this->updateStanding($standing['participants'], $standing['battlesPlayed'], $standing['wins'], $standing['crowns'],
                    $standing['clanScore'], $getStanding['id']);
            } else {
                $this->insertStanding(ltrim($standing['tag'], "#"), $standing['name'], $standing['participants'], $standing['battlesPlayed'],
                    $standing['wins'], $standing['crowns'], $standing['clanScore']);
            }
        }
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
        $GLOBALS['db']->query(sprintf($pattern, $participants, $battlesPlayed, $wins, $crowns, $warTrophies, $id));
    }

    public function insertStanding($tag, $name, $participants, $battlesPlayed, $wins, $crowns, $warTrophies) {
        $pattern = "
            INSERT INTO standings (tag, standings.name, participants, battles_played, battles_won, crowns, war_trophies, war_id)
            VALUES (\"%s\", \"%s\", %d, %d, %d, %d, %d, %d)
        ";
        $clanName = utf8_decode($name);
        if (strpos(trim($clanName), '???') !== false)
            $clanName = "Arabe/Chinois";

        $GLOBALS['db']->query(sprintf($pattern, $tag, $clanName, $participants, $battlesPlayed, $wins, $crowns, $warTrophies, $this->id));

    }

    public function isDeckUsedInCurrentWar($deckId) {
        $pattern = "
            SELECT id
            FROM war_decks
            WHERE war_id = %d
            AND deck_id = %d
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $this->getId(), $deckId))->fetch() != null;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDeckResultsByTime($combatTime) {
        $pattern = "
            SELECT dr.id, dr.win, dr.crowns, dr.time
            FROM deck_results dr
            WHERE dr.time = %d
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $combatTime))->fetch();
    }

    public function insertDeckResults($deckId, $win, $crowns, $combatTime) {
        $pattern = "
            INSERT INTO deck_results (deck_id, win, crowns, time)
            VALUES (%d, %d, %d, %d)
        ";
        $GLOBALS['db']->query(sprintf($pattern, $deckId, $win, $crowns, $combatTime));
    }

    public function insertDeckWar($deckId) {
        $pattern = "
            INSERT INTO war_decks(deck_id, war_id)
            VALUES (%d, %d)
        ";
        $GLOBALS['db']->query(sprintf($pattern, $deckId, $this->id));
    }

    public function getLastUpdatedWarDecks() {
        $query = "
            SELECT updated
            FROM last_updated
            WHERE page_name = \"war_decks\"
        ";
        return $GLOBALS['db']->query($query)->fetch();
    }

    public function getAllWarDecksWithPagination($current, $page) {
        $pattern = "
            SELECT dr.deck_id, COUNT(dr.id) as played, SUM(win) as wins, SUM(crowns) as total_crowns,
            subQuery.elixir_cost, subQuery.card_keys, subQuery.cr_ids,
            (
                SELECT CEIL(COUNT(d.id) / 10)
                FROM decks d
                RIGHT JOIN war_decks wd ON d.id = wd.deck_id
                JOIN war w ON wd.war_id = w.id
                %s
            ) as number_of_pages
            FROM war_decks wd
            LEFT JOIN deck_results dr ON dr.deck_id = wd.deck_id
            LEFT JOIN decks d ON d.id = wd.deck_id
            LEFT JOIN war w ON w.id = wd.war_id
            LEFT JOIN
                (
                    SELECT cd.deck_id, GROUP_CONCAT(c.card_key) as card_keys, GROUP_CONCAT(c.cr_id) as cr_ids, ROUND(AVG(c.elixir), 1) as elixir_cost
                    FROM card_deck cd
                    LEFT JOIN cards c ON c.id = cd.card_id
                    GROUP BY cd.deck_id
                ) subQuery ON subQuery.deck_id = d.id
            %s
            GROUP BY wd.deck_id
            ORDER BY played DESC, wins DESC, crowns DESC
            LIMIT %d, 10
        ";
        $offset = intval(($page - 1) * 10);
        $condition = "";
        if ($current) {
            $condition = "WHERE w.past_war = 0";
        }

        $warDecks = array();
        foreach ($GLOBALS['db']->query(sprintf($pattern, $condition, $condition, $offset))->fetchAll() as $deck) {
            if ($this->warDecksPage == null && $current) {
                $this->warDecksPage = intval($deck['number_of_pages']);
            } else if ($this->allDecksPage == null && !$current) {
                $this->allDecksPage = intval($deck['number_of_pages']);
            }
            $d = new Deck($deck['cr_ids'], $deck['card_keys'], $deck['elixir_cost']);
            $d->setWins($deck['wins']);
            $d->setCrowns($deck['total_crowns']);
            $d->setPlayed($deck['played']);
            array_push($warDecks, $d);
        }
        return $warDecks;
    }

    public function getWarDecksPage() {
        return $this->warDecksPage;
    }

    public function setWarDecksPage($warDecksPage) {
        $this->warDecksPage = $warDecksPage;
    }

    public function getAllDecksPage() {
        return $this->allDecksPage;
    }

    public function setAllDecksPage($allDecksPage) {
        $this->allDecksPage = $allDecksPage;
    }

    public function getFavCards() {
        $query = "
            SELECT COUNT(c.id) as occurence, c.card_key
            FROM card_deck cd
            JOIN `cards` c ON c.id = cd.card_id
            GROUP BY c.cr_id
            ORDER BY occurence DESC
            LIMIT 9
        ";

        return $GLOBALS['db']->query($query)->fetchAll();
    }
}