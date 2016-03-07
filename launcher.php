<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config.default.php";

echo "Loading ss-panel bootstrap.php...\n";
require_once $config['bootstrap'];

$bot = new Feng\JeraBot\Bot( $config );
if ( !$bot->sanityCheck() ) {
	echo "Bad API key.\n";
	exit;
}
$bot->getLogger()->addInfo( "API key okay! Hello, world!" );
$bot->run();

