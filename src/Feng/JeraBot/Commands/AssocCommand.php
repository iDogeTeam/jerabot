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
use Feng\JeraBot\PanelBridge;

class AssocCommand extends Command {
	protected $name = "assoc";

	protected $description = "绑定 DogeSpeed 帐号";

	protected $access = Access::EVERYONE;

	protected $hidden = true;

	protected $pmOnly = true;

	public function initOptions() {
		$this
			->addOption( 0 )
			->describedAs( "绑定码" )
		;
		$this
			->addOption( "remove" )
			->describedAs( "解除关联" )
			->boolean()
		;
		$this
			->addOption( "code" )
			->describedAs( "解除码" )
			->boolean()
		;
	}

	public function handle( $arguments ) {
		$tid = $this->getUpdate()->getMessage()->getFrom()->getId();
		if ( $this->getOption( "remove" ) ) {
			// deassociate
			if ( false === $user = $this->getPanelUser() ) {
				$this->replyWithMessage( array(
					"text" => "你还没有绑定 Doge 账户呢！"
				) );
				return;
			}
			if ( $this->getOption("code") != 666){
				$this->replyWithMessage( array(
					"text" => "解除码不正确!请联系管理员获取帮助。"
				) );
				return;
			}
			$user->telegram_id = 0;
			$user->save();
			$this->logger->addInfo( "解除关联：Doge {$user->id} <-X-> Telegram $tid" );
			$this->replyWithMessage( array(
				"text" => "GG！成功解除关联"
			) );
		} else {
			// associate
			if ( $this->getPanelUser() ) {
				$this->replyWithMessage( array(
					"text" => "已经绑定了 DogeSpeed 用户"
				) );
				return;
			}
			$bridge = new PanelBridge();
			$code = $this->getOption( 0 );
			if ( empty( $code ) ) return;
			if ( $user = $bridge->getUserByTelegramToken( $code ) ) {
				if ( $user->telegram_id ) {
					$this->replyWithMessage( array(
						"text" => "目标用户已被绑定"
					) );
					return;
				}
				$user->telegram_id = $tid;
				$user->telegram_token = "";
				$user->save();
				$this->logger->addInfo( "关联：Doge {$user->id} <---> Telegram $tid" );
				$this->replyWithMessage( array(
					"text" => "绑定成功！\xF0\x9F\x99\x8C"
				) );
			}
		}
	}
}
