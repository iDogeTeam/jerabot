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
use Feng\JeraBot\Utils;
use Feng\JeraBot\Bot;
use Feng\JeraBot\PanelBridge;
use Feng\JeraBot\Commando;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Commands\Command as VanillaCommand;

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
	protected $options = "";

	/**
	 * Whether this command is hidden or not
	 *
	 * @var bool
	 */
	protected $hidden = false;

	/**
	 * Whether this command is PM-only or not
	 *
	 * @var bool
	 */
	protected $pmOnly = false;

	/**
	 * The Bot object
	 *
	 * @var Bot
	 */
	public $bot = null;

	/**
	 * The Commando object
	 *
	 * @var Commando
	 */
	protected $commando = null;

	/**
	 * The Logger object
	 *
	 * @var Logger
	 */
	protected $logger = null;

	public final function __construct( Bot &$bot ) {
		$this->bot = &$bot;
		$reflection = new \ReflectionClass( $this );
		$this->logger = $this->bot->getLogger( $reflection->getShortName() );
		$this->init();
		$this->logger->addDebug( "Initialized" );
	}

	/**
	 * Initialize the command
	 *
	 * Do not initialize the options here, as
	 * the Commando parser is not available
	 * yet.
	 */
	public function init() {
	}
	/**
	 * Initialize the options
	 *
	 * This method is called every time
	 * a user runs a command.
	 */
	public function initOptions() {
		$this->replyWitChatAction( array(
			"action" => "typing"
		) );
	}

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
		$this->telegram = $telegram;
		$this->arguments = $arguments;
		$this->update = $update;
		$this->commando = new Commando( array() );
		$this->commando->useDefaultHelp( false );
		if ( $this->autoHelp ) {
			$this
				->addOption( "help" )
				->describedAs( "显示帮助" )
				->boolean()
			;
		}
		$this->initOptions();
		$access = $this->bot->getAccessLevel( $update['message']['from']['id'] );
		if ( $access >= $this->access ) {
			// Access granted!
			if (
				$this->getUpdate()->getMessage()->getChat()->getId() !=
				$this->getUpdate()->getMessage()->getFrom()->getId()
			) {
				if ( $this->isPmOnly() ) {
					return $this->pmOnlyError( $arguments );
				}
			}
			$argv = explode( " ", $arguments );
			array_unshift( $argv, $this->name );
			$this->commando->setTokens( $argv );
			try {
				$this->commando->parse();
			} catch ( \Exception $e ) {
				$this->parseError( $arguments, $e );
				return;
			}
			if ( $this->autoHelp && $this->getOption( "help" ) ) {
				return $this->sendHelp( $arguments );
			} else {
				try {
					return $this->handle( $arguments );
				} catch ( \Exception $e ) {
					$this->logger->addCritical( $e );
				}
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
		$formatted = Utils::formatTelegramUser( $this->getUpdate()->getMessage()->getFrom() );
		$this->logger->addWarning( "药丸，权限不够：" . $formatted );
		$this->replyWithMessage( array(
			"text" => "你的权限不够啊，使用命令也要按照基本法好伐 \xF0\x9F\x8C\x9A"
		) );
	}

	/**
	 * Notify the user about a parse error
	 *
	 * @param string $arguments
	 * @param \Exception $e
	 */
	public function parseError( $arguments, $e ) {
		$this->replyWithMessage( array(
			"text" => "\xF0\x9F\x98\x93 在处理请求时发生了错误,是不是什么地方输错了?" . $e->getMessage()
		) );
		$this->replyWithMessage( array(
			"text" => "\xF0\x9F\x98\x93 想要看看帮助的话请在指令后面输入 -help。例如: /intro -help "
		) );
	}

	/**
	 * Notify the user that the command is PM-only
	 *
	 * @param string $arguments
	 */
	public function pmOnlyError( $arguments ) {
		$this->replyWithMessage( array(
			"text" => "请在私聊中使用该命令	\xF0\x9F\x98\x87"
		) );
	}

	/**
	 * Tick tock tick tock...
	 */
	public function tick() {
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

	/**
	 * Get required access level
	 *
	 * @return int
	 */
	public function getAccess() {
		return $this->access;
	}

	/**
	 * Get visibility
	 *
	 * @return bool
	 */
	public function isHidden() {
		return $this->hidden;
	}

	/**
	 * Get pmOnly
	 *
	 * @return bool
	 */
	public function isPmOnly() {
		return $this->pmOnly;
	}

	/**
	 * Add an option
	 */
	public function addOption( $name = "" ) {
		return $this->commando->option( $name );
	}

	/**
	 * Retrieve the value of an option
	 */
	public function getOption( $name = "" ) {
		return $this->commando[$name];
	}

	/**
	 * Send help information to the sender
	 *
	 * @param string $arguments
	 *
	 * @return mixed
	 */
	private function sendHelp( $arguments ) {
		// Generate usage information
		$banner = "/" . $this->name;
		if ( !empty( $this->description ) ) {
			$banner .= ": " . $this->description;
		}
		$help = $banner . $this->commando->getHelp();
		$this->replyWithMessage( array(
			"text" => $help,
			"parse_mode" => "Markdown"
		) );
	}
}
