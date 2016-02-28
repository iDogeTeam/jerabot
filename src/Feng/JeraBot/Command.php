<?php
/*
	Copyright (c) 2016, Zhaofeng Li
	All rights reserved.
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	* Redistributions of source code must retain the above copyright notice, this
	list of conditions and the following disclaimer.
	* Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
	CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
	OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
	OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

namespace Feng\JeraBot;

use Feng\JeraBot\Access;
use Feng\JeraBot\Bot;
use Feng\JeraBot\PanelBridge;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Commands\Command as VanillaCommand;
use Ulrichsg\Getopt\Getopt;

/**
 * The good'ol Command class, with some Doge magic :)
 */
abstract class Command extends VanillaCommand {
	/**
	 * Access level the command requires
	 *
	 * Can be an Access::* constant, like Access::ADMIN for admin-only commands.
	 * The default value is Access::USER.
	 *
	 * @var int
	 */
	protected $access = Access::USER;

	/**
	 * Whether to attempt to provide help when `--help` is given or not
	 */
	protected $autoHelp = true;

	/**
	 * Options this command expects/accepts
	 */
	public $options = "";

	/**
	 * The Bot object
	 *
	 * @var Bot
	 */
	public $bot = null;

	/**
	 * Process an inbound command
	 *
	 * This will check the sender against the access level requirement,
	 * and call handle() if passed. Otherwise, denied() will be called
	 * (if implemented).
	 *
	 * @param Api $telegram
	 * @param string $arguments
	 * @param Update $update
	 *
	 * @return mixed
	 */
	public function make( $telegram, $arguments, $update ) {
		$access = $this->bot->getAccessLevel( $update['message']['from']['id'] );
		if ( $access >= $this->access ) {
			// Access granted!
			if ( $this->autoHelp && "--help" == $arguments ) {
				// Generate usage information
				// TODO: Implement php-getopt support
				if ( !empty( $this->description ) ) {
					$help = $this->name . ": " . $this->description;
				} else {
					$help = $this->name . ": " . "_貌似我也不知道这玩意到底做什么的（捂脸_";
				}
				$telegram->sendMessage( array(
					"chat_id" => $update->getMessage()->getChat()->getId(),
					"text" => $help,
					"parse_mode" => "Markdown"
				) );
			} else {
				return parent::make( $telegram, $arguments, $update );
			}
		} else {
			// Naive!
			return $this->denied( $arguments );
		}
	}

	/**
	 * Do something if the access level check failed
	 *
	 * What about filling the chat with cat pics? Sounds like an
	 * excellent idea to me.
	 *
	 * @param string $arguments
	 */
	public function denied( $arguments ) {
		// guys where is dat cat pic sending code i cant find it
		// srsly i need some kitten love after all this dog hassle
	}

	/**
	 * Get the Getopt.php parser
	 *
	 * @return Getopt
	 */
	public function getGetopt() {
		return new Getopt( $this->options );
	}

	/**
	 * Get the User object associated with the sender
	 *
	 * This method leverages `PanelBridge`. You should never
	 * type-hint the returned object as it will be an instance
	 * of an undetermined class.
	 *
	 * `false` is returned when no associated user is found, or
	 * when there are more than one.
	 *
	 * @return object|false
	 */
	public function getPanelUser() {
		if ( null === $this->update ) return false;
		if ( $user = $this->update->getMessage()->getFrom() ) {
			$tid = $user->getId();
			$bridge = new PanelBridge();
			$users = $bridge->getUsersByTelegramId( $tid );
			if ( false === $users || 1 < $users->count() ) {
				return false;
			} else {
				return $users->get()->first();
			}
		}
		return false; // Sender cannot be determined
	}
}
