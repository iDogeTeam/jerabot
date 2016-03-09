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

class CheckinCommand extends Command {
	protected $name = "checkin";

	protected $description = "汪一汪，十年少 \xF0\x9F\x90\xB6";

	protected $access = Access::EVERYONE;

	public function initOptions() {
		$this
			->addOption( "nonext" )
			->describedAs( "不抽取下月流量" )
			->boolean()
		;
		$this
			->addOption( "ingress" )
			->aka( "i" )
			->describedAs( "脑洞模式" )
			->boolean()
		;
		$this
			->addOption( "mh" )
			->aka( "f" )
			->describedAs( "使用 Multi-hack" )
			->boolean()
		;
		$this
			->addOption( "fracker" )
			->describedAs( "使用 Doge Fracker" )
			->boolean()
		;
	}

	public function handle( $arguments ) {
		$bridge = new PanelBridge();
		$ingress = $this->getOption( "ingress" ) || $this->getOption( "mh" ) || $this->getOption( "fracker" );
		$next = !$this->getOption( "nonext" ) && !$this->getOption( "fracker" );
		if ( false === $user = $this->getPanelUser() ) {
			if ( $ingress ) $message = "侦测器已停用：位置信息不准确";
			else $message = "你还没有绑定 Doge 账户呢！";
			$this->replyWithMessage( array(
				"text" => $message
			) );
			return;
		}
		if ( $this->getOption( "mh" ) && !$user->is_admin ) {
			$this->replyWithMessage( array(
				"text" => "Hack acquired no items."
			) );
			return;
		}
		if ( !$user->isAbleToCheckin() && !$this->getOption( "mh" ) ) {
			$last = $user->lastCheckInTime();
			if ( $ingress ) $message = "*(-i 在普通签到时加入有惊喜)* Portal 被烧毁！重建 Portal 可能需要大量时间。";
			else $message .= "您似乎已经签到过了...";
				 $message .= "\r\n上一次的签到时间: $last ";
			$this->replyWithMessage( array(
				"text" => $message,";上一次/现在的签到时间:",$last
			) );
			return;
		}
		if ( $this->getOption( "fracker" ) && 3 > $user->user_type ) {
			$this->replyWithMessage( array(
				"text" => "没有足够的 CMU。"
			) );
			return;
		}
		$lost = $ingress ? rand( 1, 10 ) : 0;
		$traffic = rand( $user->getCheckinMin(), $user->getCheckinMax() );
		$trafficnext = $next ? rand( $user->getCheckinMin(), $user->getCheckinMax() ) / 2 : 0;
		if ( $this->getOption( "fracker" ) ) $traffic *= 2;
		if ( $user->transfer_enable <= $bridge->mbToBytes( $lost ) ) {
			$this->replyWithMessage( array(
				"text" => "Scanner disabled: Collect more XM"
			) );
			return;
		}
		$user->transfer_enable -= $bridge->mbToBytes( $lost );
		$user->transfer_enable += $bridge->mbToBytes( $traffic );
		$user->transfer_enable_next += $bridge->mbToBytes( $trafficnext );
		if ( !$this->getOption( "mh" ) ) $user->last_check_in_time = time();
		$user->save();
		$response = $this->renderResults( $ingress, $lost, $traffic, $trafficnext );
		$this->replyWithMessage( array(
			"text" => $response,
			"parse_mode" => "Markdown"
		) );
	}

	protected function renderResults( $ingress, $lost, $traffic, $trafficnext ) {
		$response = "";
		if ( $ingress ) {
			if ( $lost ) $response .= "*你被击中了，损失 $lost MB!*\r\n";
			$response .= "Acquired:\r\n";
			if ( $traffic ) $response .= "*MB* x _{$traffic}_    ";
			if ( $trafficnext ) $response .= "*MB Next* x _{$trafficnext}_    ";
			$response .= "\r\n*Portal Key*";
		} else {
			if ( $lost ) $response .= "损失 $lost MB，";
			if ( $traffic ) $response .= "获得了本月 $traffic MB 流量";
			if ( $trafficnext ) $response .= "，下月 $trafficnext MB 流量";
		}
		return $response;
	}
}
