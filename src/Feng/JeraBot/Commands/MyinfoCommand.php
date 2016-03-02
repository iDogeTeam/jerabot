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

class MyinfoCommand extends Command {
	protected $name = "myinfo";

	protected $description = "看看自己的 Doge 生涯";

	protected $access = Access::EVERYONE;

	public function initOptions() {
		$this
			->addOption( "privacy" )
			->aka( "p" )
			->describedAs( "隐藏私人信息"  )
			->boolean()
		;
	}

	public function handle( $arguments ) {
		if ( false === $user = $this->getPanelUser() ) {
			$this->replyWithMessage( array(
				"text" => "你还没有绑定 Doge 账户呢！"
			) );
			return;
		}
		$template = <<<EOF
*账户信息*
绑定邮箱：`%s`
AnyConnect：%s
[更多信息](https://dogespeed.ga/user/profile)
EOF;
		$response = sprintf(
			$template,
			$this->getOption( 'privacy' ) ? "隐藏" : $user->email,
			$user->ac_enable ? "已开通" : "未开通"
		);

		$this->replyWithMessage( array(
			"text" => $response,
			"parse_mode" => "Markdown"
		) );
	}
}
