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

namespace Feng\JeraBot\Commands;

use Telegram\Bot\Actions;
use Feng\JeraBot\Command;
use Feng\JeraBot\Access;

class HelpCommand extends Command {
	protected $name = "help";

	protected $description = "看看有什么命令";

	protected $access = Access::EVERYONE;

	protected $cache = array();

	protected $headings = array(
		Access::USER => "会员命令",
		Access::ADMIN => "管理员命令",
		Access::DEVELOPER => "开发者命令",
	);

	public function initOptions() {
		$this
			->addOption( 0 )
			->describedAs( "显示指定命令的帮助" )
		;
		$this
			->addOption( "access" )
			->aka( "l" )
			->describedAs( "只显示指定的权限可用的命令" )
			->must( function( $level ) {
				return Access::validateLevel( $level );
			} )
		;
		$this
			->addOption( "all" )
			->aka( "a" )
			->describedAs( "显示隐藏的命令" )
			->boolean()
		;
		$this
			->addOption( "refresh" )
			->aka( "r" )
			->describedAs( "刷新命令列表" )
			->boolean()
		;
	}

	public function handle( $arguments ) {
		$tid = $this->getUpdate()->getMessage()->getFrom()->getId();
        $tuser = $this->getUpdate()->getMessage()->getFrom()->getUsername();
		$access = $this->bot->getAccessLevel( $tid );
		if ( null !== $altaccess = $this->getOption( "access" ) ) {
			if ( $altaccess > $access ) {
				$this->replyWithMessage( array(
					"text" => "权限不够啊，看命令也要按照基本法好伐 \xF0\x9F\x8C\x9A"
				) );
				return;
			}
			$access = $altaccess;
		}
		if ( !$this->buildCache( $this->getOption( "force" ) ) ) {
			throw new \Exception( "无法构建缓存" );
		}
		// Provide help with a specific command
		$c = $this->getOption( 0 );
		if ( !empty( $c ) ) {
			$commands = $this->telegram->getCommands();
			if ( isset( $commands[$c] ) ) {
				$this->triggerCommand( $c, "--help" );
			} else {
				$this->replyWithMessage( array(
					"text" => "并没有这个命令",
					"parse_mode" => "Markdown"
				) );
			}
			return;
		}
		// List available commands
		$response = "";
		$list = $this->getOption( "all" ) ? $this->cache['all'] : $this->cache['common'];
		foreach ( $list as $requiredAccess => $help ) {
			if ( $access >= $requiredAccess ) {
				$response .= $help;
			}
		}
		$this->replyWithMessage( array(
			"text" => $response,
			"parse_mode" => "Markdown"
		) );
        $this->logger->addInfo("trigger!tuser{$tuser}, TGID{$tid}");
	}

	public function buildCache( $forced = false ) {
		// Check if we need to build the cache
		if ( !$forced && count( $this->cache ) ) {
			// Looks like the cache is already built :)
			return true;
		}

		// Generate cache structure
		$cache = array(
			"common" => array(), // Common commands
			"all" => array(), // All commands
		);
		foreach ( Access::$list as $access ) {
			$cache['all'][$access] = "";
			$cache['common'][$access] = "";
		}

		// Generate help strings
		$commands = $this->telegram->getCommands();
		foreach ( $commands as $name => $command ) {
			// Let's cache pre-formatted strings for performance
			$access = $command->getAccess();
			$help = sprintf(
				"/%s: %s\r\n",
				$name,
				$command->getDescription()
			);
			if ( !$command->isHidden() ) {
				$cache['common'][$access] .= $help;
			}
			$cache['all'][$access] .= $help;
		}

		// Generate headings
		foreach ( Access::$list as $access ) {
			$heading = $this->getHeading( $access );
			if ( empty( $heading ) ) {
				$heading = "";
			} else {
				$heading = "**$heading**\r\n";
			}
			if ( !empty( $cache['common'][$access] ) ) {
				$cache['common'][$access] = $heading . $cache['common'][$access] . "\r\n";
			}
			if ( !empty( $cache['all'][$access] ) ) {
				$cache['all'][$access] = $heading . $cache['all'][$access] . "\r\n";
			}
		}

		// Cache built!
		$this->cache = $cache;
		return true;
	}

	public function getHeading( $access ) {
		if ( isset( $this->headings[$access] ) ) {
			return $this->headings[$access];
		}
		return "";
	}
}
