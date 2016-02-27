<?php
// Please put your own configurations in config.php
$config = array(
	"username" => "Put bot username here - affects command parsing",
	"explicitaddress" => false,
	"key" => "Put Telegram Bot API key here",
	"admins" => array( "Put ops' IDs in this array" ),
	"developers" => array(),
	"bootstrap" => "The path of ss-panel's bootstrap file",
);

@include( __DIR__ . "/config.php" );
