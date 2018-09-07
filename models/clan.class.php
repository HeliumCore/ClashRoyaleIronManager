<?php

class Clan {
    private $tag = "9RGPL8PC";

    /**
     * Récupère la liste des joueurs du clan
     * @return array liste des joueur
     */
    public function getPlayers() {
        return $GLOBALS['db']->query("
            SELECT
                players.id,
                players.tag,
                players.name as playerName,
                players.rank,
                players.trophies,
                role.name as playerRole,
                arena.arena as arena,
                arena.arena_id as arena_id,
                players.donations,
                players.donations_received
            FROM
                players
                INNER JOIN role ON role.id = players.role_id
                INNER JOIN arena ON arena.arena_id = players.arena
            WHERE
                players.in_clan = 1
            ORDER BY players.rank ASC
        ")->fetchAll();
    }

    /**
     * Date de dernière mise à jour du clan
     */
    public function getLastUpdated() {
        $result = $GLOBALS['db']->query("
            SELECT updated
            FROM last_updated
            WHERE page_name = 'index'
        ")->fetch();
        if (!empty($result['updated'])) {
            return strtotime($result['updated']);
        }
        return null;
    }

    public function getMembersFromApi() {
        $url = "clans/%23" . $this->tag . "/members";
        return ClashRoyaleApi::getRequest($url)['items'];
    }

    public function removePlayerFromClan($tag) {
        $pattern = "
            UPDATE players
            SET in_clan = 0
            WHERE tag = \"%s\"
        ";
        $GLOBALS['db']->query(sprintf($pattern, $tag));
    }

    public function setLastUpdated() {
        $query = "
            UPDATE last_updated
            SET updated = NOW()
            WHERE page_name = \"index\"
        ";
        $GLOBALS['db']->query($query);
    }
}