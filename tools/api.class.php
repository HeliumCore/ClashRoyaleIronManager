<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 06/09/18
 * Time: 13:17
 */

class ClashRoyaleApi {
    private static $oInstance = null;
    private static $curl = null;

    // TODO conf base
    private static $base = "https://api.clashroyale.com/v1";

    // TODO revoir ca, Si on fait plusieurs appels consecutifs a getRequest, on close mais ne re-init pas.
    // donc j'ai tout mis dans getRequest pour le moment
//    public function __construct() {
//        self::$curl = curl_init();
//        $authorization = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6IjdhMDM0NjMyLTg0ZTktNDVmYS1hMzgzLTJjNDM5MWMyMTc5OCIsImlhdCI6MTUzNjIyNTQyMiwic3ViIjoiZGV2ZWxvcGVyL2Y5YmY3NTVmLTJiNWUtYzEzZi05NWQxLTBmMzQyNDlmZjc3ZSIsInNjb3BlcyI6WyJyb3lhbGUiXSwibGltaXRzIjpbeyJ0aWVyIjoiZGV2ZWxvcGVyL3NpbHZlciIsInR5cGUiOiJ0aHJvdHRsaW5nIn0seyJjaWRycyI6WyI4Ny45OC4xNTQuMTQ2IiwiMTg1LjExNy4zNy45OCIsIjkxLjEzNC4yNDguMjExIl0sInR5cGUiOiJjbGllbnQifV19.o_SsQBtUlsDTV5xaBlQss_K6FyK9yE5IIIVKaAMEtzcDlztpjHoiqX9hobB0CunMi8yYNQWqzv9M78dButF2SQ";
//        curl_setopt(self::$curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
//        curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, 1);
//    }

    public static function getRequest($path) {
        self::$curl = curl_init();
        $authorization = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6IjdhMDM0NjMyLTg0ZTktNDVmYS1hMzgzLTJjNDM5MWMyMTc5OCIsImlhdCI6MTUzNjIyNTQyMiwic3ViIjoiZGV2ZWxvcGVyL2Y5YmY3NTVmLTJiNWUtYzEzZi05NWQxLTBmMzQyNDlmZjc3ZSIsInNjb3BlcyI6WyJyb3lhbGUiXSwibGltaXRzIjpbeyJ0aWVyIjoiZGV2ZWxvcGVyL3NpbHZlciIsInR5cGUiOiJ0aHJvdHRsaW5nIn0seyJjaWRycyI6WyI4Ny45OC4xNTQuMTQ2IiwiMTg1LjExNy4zNy45OCIsIjkxLjEzNC4yNDguMjExIl0sInR5cGUiOiJjbGllbnQifV19.o_SsQBtUlsDTV5xaBlQss_K6FyK9yE5IIIVKaAMEtzcDlztpjHoiqX9hobB0CunMi8yYNQWqzv9M78dButF2SQ";
        curl_setopt(self::$curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$curl, CURLOPT_URL, rtrim(self::$base, "/") . "/" . ltrim($path, "/"));
        $result = curl_exec(self::$curl);
        curl_close(self::$curl);
        $json = json_decode($result, true);
        if (isset($json['reason']) && $json['reason'] == "inMaintenance")
            return false;

        return $json;
    }

    /**
     * Initialise le singleton
     */
    public static function create() {
        assert(self::$oInstance === null);
        self::$oInstance = new ClashRoyaleApi();
    }

    /**
     * Récupère l'instance du singleton (crée la nouvelle instance si nécessaire)
     */
    public static function getInstance() {
        assert(self::$oInstance != null);
        return self::$oInstance;
    }
}
