<?php
// Please put your own configurations in config.php
$config = array(
	"username" => "Put bot username here - affects command parsing",
	"explicitaddress" => false,
	"key" => "Put Telegram Bot API key here",
	"admins" => array( "Put ops' IDs in this array" ),
	"developers" => array(),
	"bootstrap" => "",
	"commands" => array(
		"\\Feng\\JeraBot\\Commands\\StartCommand",
		"\\Feng\\JeraBot\\Commands\\HelpCommand",
		"\\Feng\\JeraBot\\Commands\\MyidCommand",
		"\\Feng\\JeraBot\\Commands\\MyinfoCommand",
		"\\Feng\\JeraBot\\Commands\\StatsCommand",
		"\\Feng\\JeraBot\\Commands\\CheckinCommand",
		"\\Feng\\JeraBot\\Commands\\AssocCommand",
		"\\Feng\\JeraBot\\Commands\\FindCommand",
		"\\Feng\\JeraBot\\Commands\\KdCommand",
		"\\Feng\\JeraBot\\Commands\\SendCommand",
		"\\Feng\\JeraBot\\Commands\\AnyConnectCloseCommand",
		"\\Feng\\JeraBot\\Commands\\AnyConnectCommand",
		"\\Feng\\JeraBot\\Commands\\InviteCodeUserCommand",
		"\\Feng\\JeraBot\\Commands\\IntroductionCommand",
		"\\Feng\\JeraBot\\Commands\\UserRightsCommand",
		"\\Feng\\JeraBot\\Commands\\DataCommand",
		"\\Feng\\JeraBot\\Commands\\NodeinfoCommand",
		"\\Feng\\JeraBot\\Commands\\RegCommand",
		"\\Feng\\JeraBot\\Commands\\ChangeCommand",
		"\\Feng\\JeraBot\\Commands\\UsernoteCommand",
		"\\Feng\\JeraBot\\Commands\\GiftcodeCommand",
		"\\Feng\\JeraBot\\Commands\\LoginCommand",
		"\\Feng\\JeraBot\\Commands\\PreloadCommand"

	),
);

@include( __DIR__ . "/config.php" );
