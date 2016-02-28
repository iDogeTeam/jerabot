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
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option as GetoptOption;

class HelpCommand extends Command {
	protected $name = "help";

	protected $description = "看看有什么命令";

	protected $access = Access::EVERYONE;

	public function __construct() {
		$this->options = array(
			( new GetoptOption( "a", "access", Getopt::REQUIRED_ARGUMENT ) )
				->setDescription( "只显示指定的权限可用的命令" )
		);
	}

	public function handle( $arguments ) {
		$getopt = $this->getGetopt();
		$tid = $this->getUpdate()->getMessage()->getFrom()->getId();
		$access = $this->bot->getAccessLevel( $tid );
		if ( null !== $altaccess = $getopt->getOption( "access" ) ) {
			if ( $altaccess > $access ) {
				$this->replyWithMessage( array(
					"text" => "权限不够啊，看命令也要按照基本法好伐 \xF0\x9F\x8C\x9A"
				) );
				return;
			}
			$access = $altaccess;
		}
		$commands = $this->telegram->getCommands();
		$response = "";
		foreach ( $commands as $name => $command ) {
			if ( $access >= $command->getAccess() ) {
				$response .= sprintf(
					"/%s: %s\r\n",
					$name,
					$command->getDescription()
				);
			}
		}
		$this->replyWithMessage( array(
			"text" => $response
		) );
	}
}
