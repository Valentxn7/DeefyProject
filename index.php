<?php

// 1er autoloader devancé par le COMPOSER
/*require_once 'src/loader/Psr4ClassLoader.php';
$loader = new loader\Psr4ClassLoader("iutnc\\deefy\\", "." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR);
$loader->register();
echo $loader->loadClass("iutnc\\deefy\\audio\\tracks\\AlbumTrack");*/

require_once 'vendor/autoload.php';

use iutnc\deefy\render as R;
use iutnc\deefy\audio as A;
use iutnc\deefy\exception as E;
ob_start();
session_start();

$dispatcher = new iutnc\deefy\dispatch\Dispatcher();
try {
    $dispatcher->run();
} catch (Exception $e) {
    http_response_code(500);
    echo "Erreur 500 : " . $e->getMessage();
}

ob_end_flush();