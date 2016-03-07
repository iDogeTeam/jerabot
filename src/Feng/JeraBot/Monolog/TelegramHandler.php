<?php
/*
	copyright (c) 2016, zhaofeng li
	all rights reserved.
	redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	* redistributions of source code must retain the above copyright notice, this
	list of conditions and the following disclaimer.
	* redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	this software is provided by the copyright holders and contributors "as is"
	and any express or implied warranties, including, but not limited to, the
	implied warranties of merchantability and fitness for a particular purpose are
	disclaimed. in no event shall the copyright holder or contributors be liable
	for any direct, indirect, incidental, special, exemplary, or consequential
	damages (including, but not limited to, procurement of substitute goods or
	services; loss of use, data, or profits; or business interruption) however
	caused and on any theory of liability, whether in contract, strict liability,
	or tort (including negligence or otherwise) arising in any way out of the use
	of this software, even if advised of the possibility of such damage.
*/

namespace Feng\JeraBot\Monolog;

use Feng\JeraBot\Bot;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class TelegramHandler extends AbstractProcessingHandler {
	protected $bot = null;

	public function __construct( Bot &$bot, $level = Logger::DEBUG, $bubble = true ) {
		$this->bot = &$bot;
		parent::__construct( $level, $bubble );
	}

	public function getBot() {
		return $this->bot;
	}

	protected function write( array $record ) {
		$message = "\xF0\x9F\x93\x93 _{$record['formatted']}_";
		$chats = $this->getBot()->getConfig( "internalChats" );
		if ( is_array( $chats ) ) {
			foreach ( $chats as $chatId ) {
				$this->getBot()->getApi()->sendMessage( array(
					"chat_id" => $chatId,
					"text" => $message,
					"parse_mode" => "Markdown",

				) );
			}
		}
	}
}

