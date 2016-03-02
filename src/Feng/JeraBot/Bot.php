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

// Telegram Bot SDK
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class Bot {
	protected $api = null;
	protected $apiOffset = 0;
	protected $config = array();
	protected $status = array(
		"users" => array(),
		"memory" => array(),
	);

	public function __construct( $config ) {
		$this->config = $config;
		$this->api = new Api( $this->getConfig( "key" ) );
		$this->initializeCommands();
		$this->loadStatus();
	}

	public function loadStatus() {
		if ( file_exists( $this->getConfig( "statusFile" ) ) ) {
			$this->status = json_decode( file_get_contents( $this->getConfig( "statusFile" ) ), true );
		} else {
			$this->saveStatus();
		}
	}

	public function saveStatus() {
		file_put_contents( $this->getConfig( "statusFile" ), json_encode( $this->status ) );
	}

	public function sanityCheck() {
		try {
			$this->api->getMe();
		} catch ( \Exception $e ) {
			return false;
		}
		return true;
	}

	public function run() {
		for ( ; ; ) {
			$this->api->commandsHandler( false );
		}
	}

	public function isAdmin( $id ) {
		return in_array( $id, $this->getConfig( "admins" ) );
	}

	public function isUser( $id ) {
		return in_array( $id, $this->status['users'] ) || $this->isAdmin( $id );
	}

	public function getAccessLevel( $id ) {
		if ( in_array( $id, $this->getConfig( "developers" ) ) )
			return Access::DEVELOPER;
		if ( in_array( $id, $this->getConfig( "admins" ) ) )
			return Access::ADMIN;
		if ( in_array( $id, $this->status['users'] ) )
			return Access::USER;
		return Access::EVERYONE;

	}
	
	public function getConfig( $key ) {
		return $this->config[$key];
	}

	protected function initializeCommands() {
		$commands = $this->getConfig( "commands" );
		foreach ( $commands as $command ) {
			$o = new $command();
			$o->bot = &$this;
			$this->api->addCommand( $o );
		}
	}
}
