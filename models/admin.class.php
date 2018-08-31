<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 31/08/18
 * Time: 14:51
 */

require 'player.class.php';

class Admin {
    private $playerPauses = null;

    public function setPlayerPauses() {
        $query = "
            SELECT a.id, p.name, p.tag, GROUP_CONCAT(pp.pause) as pauses
            FROM player_pause pp
            JOIN account a ON pp.account_id = a.id
            JOIN players p ON a.player_id = p.id AND in_clan > 0
            WHERE pp.pause > UNIX_TIMESTAMP(CURRENT_TIMESTAMP - INTERVAL 1 DAY) * 1000
            GROUP BY account_id
        ";

        $playerList = array();
        foreach ($GLOBALS['db']->query($query)->fetchAll() as $res) {
            $pausesList = array();
            $player = new Player($res['tag']);
            $pauses = explode(',', $res['pauses']);
            sort($pauses);
            foreach ($pauses as $p) {
                array_push($pausesList, new Pause($res['id'], $res['name'], $p));
            }
            $player->setPauses($pausesList);
            $player->setName($res['name']);
            array_push($playerList, $player);
        }
        $this->playerPauses = $playerList;
    }

    public function getPlayerPauses() {
        return $this->playerPauses;
    }
}