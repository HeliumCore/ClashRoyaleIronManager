<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 17:11
 */

$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTAxNywiaWRlbiI6IjI3MjUwMDMyNzcyOTU5NDM3MCIsIm1kIjp7fSwidHMiOjE1MzAwOTk3ODY1MDl9.sySMAikUSevKMvV4wOkwW3zRkhzU32JptyR65Cl4JLk";
$opts = [
    "http" => [
        "header" => "auth:" . $token
    ]
];

$context = stream_context_create($opts);