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
use Feng\JeraBot\Utils;
use Feng\JeraBot\PanelBridge;

class StatsCommand extends Command {
	protected $name = "stats";

	protected $description = "看看数字，积累一下成就感（doge脸";

	protected $access = Access::EVERYONE;

	protected $bridge = null;
	protected $tickInterval = 10;
	protected $tickTimestamp = 0;
	protected $prevTraffic = false;
	protected $curTraffic = false;
	protected $speed = 0;

	public function init() {
		$this->bridge = new PanelBridge();
        $this->replyWithChatAction( array(
            "action" => "typing"
        ) );
	}

	public function initOptions() {
		$this
			->addOption( "all" )
			->aka( "a" )
			->describedAs( "显示所有信息" )
			->boolean()
		;
	}

	public function handle( $arguments )
    {
        $tid = $this->getUpdate()->getMessage()->getFrom()->getId();
        $access = $this->bot->getAccessLevel($tid);
        $sts = $this->bridge->getAnalytics();
        $template = <<<EOF
*DogeSpeed 状态*
共有 %u 位用户，其中 %u 位在线。共产生了 %s 流量。
瞬时速度：%s

EOF;
        $response = sprintf(
            $template,
            $sts->getTotalUser(),
            $sts->getOnlineUser(3600),
            $sts->getTrafficUsage(),
            $this->getSpeed()
        );
        if ($this->getOption("all")) {
            if (Access::ADMIN <= $access) {
                $template = <<<EOF
*DogeBot 状态*
表示存活～貌似吃掉了 %u 字节的内存（好吃！
EOF;
                $response .= sprintf(
                    $template,
                    memory_get_usage(true)
                );
            }
        }
        $this->replyWithMessage(array(
            "text" => $response,
            "parse_mode" => "Markdown"
        ));
        if (false === $user = $this->getPanelUser()) {
            $this->logger->addInfo("stats was trigger! non-panel user! TGID: {$tid}");
        } Else {
            $this->logger->addInfo("stats was trigger! Doge: {$user->id}, nickname:{$user->user_name}, TGID: {$tid}");
        }
    }

	public function tick() {
		if ( time() < $this->tickTimestamp + $this->tickInterval ) return;
		$this->prevTraffic = $this->curTraffic;
		$this->curTraffic = $this->getTotalTraffic();
		if ( false === $this->prevTraffic ) {
			$this->prevTraffic = $this->curTraffic;
		}
		$this->logger->addDebug( "prev: {$this->prevTraffic} cur: {$this->curTraffic}" );
		$this->tickTimestamp = time();
	}

	protected function getSpeed() {
		$delta = $this->curTraffic - $this->prevTraffic;
		$pdelta = $delta / $this->tickInterval;
		return Utils::formatBytes( $pdelta ) . "/s";
	}

	protected function getTotalTraffic() {
		$fields = array( "u", "d" );
		$bytes = 0;
		foreach ( $fields as $field ) {
			$bytes += call_user_func(
				array(
					$this->bridge->getModel( "User" ),
					"sum"
				),
				$field
			);
		}
		return $bytes;
	}
}
