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

class StatsCommand extends Command {
	protected $name = "stats";

	protected $description = "看看数字，积累一下成就感（doge脸";

	protected $access = Access::ADMIN;

	public function handle( $arguments ) {
		$bridge = new PanelBridge();
		$sts = $bridge->getAnalytics();
		$template = <<<EOF
*DogeSpeed 状态*
共有 %u 位用户，其中 %u 位在线。共产生了 %s 流量。

*JeraBot 状态*
表示存活～貌似吃掉了 %u 字节的内存（好吃！
EOF;
		$response = sprintf(
			$template,
			$sts->getTotalUser(),
			$sts->getOnlineUser( 3600 ),
			$sts->getTrafficUsage(),
			memory_get_usage(true)
		);
		$this->replyWithMessage( array(
			"text" => $response,
			"parse_mode" => "Markdown"
		) );
	}
}
