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

class SendCommand extends Command {
	protected $name = "send";

	protected $description = "发送信息";

	protected $access = Access::ADMIN;

	protected $hidden = true;


	public function initOptions() {
		$this
			->addOption( "i" )
			->describedAs( "会话 ID" )
		;
        $this
            ->addOption( "all" )
            ->describedAs( "通知所有注册人" )
            ->boolean()
        ;
		$this
			->addOption( 0 )
			->describedAs( "信息" )
			->required()
		;
		$this
			->addOption( "group" )
			->aka( "g" )
			->describedAs( "群" )
			->boolean()
		;
	}

	public function handle( $arguments ) {
	    global $counts;
        $counts = 0;
		$id = $this->getOption( "i" );
        $text = $this->getOption( 0 );
        $bridge = new PanelBridge();
        if ( $this->getOption( "all" )  ){
            $alls = $bridge->getAll();
            foreach ( $alls as $all) {
                $tgid = $all->telegram_id;
                if (!$tgid) {
                    $counts = $counts + 1;
                    $all->is_telegram_disabled = 0;
                    try {
                        $this->getTelegram()->sendMessage(array(
                            "chat_id" => $tgid,
                            "text" => $text
                        ));
                    } catch (\Exception $e) {
                        $this->logger->addInfo("Exception catch!, {$e->getMessage()}, TGID: $tgid");
                        $counts = $counts - 1;
                        $all->is_telegram_disabled = 1;
                    }
                    $all->save();
                    usleep(10);
                }
            }
            $this->replyWithMessage( array(
                "text" => "ends,{$counts}sent",
                "parse_mode" => "Markdown"
            ) );

        } else {
            if ($id) {
                if ($this->getOption("group")) $id = -1 * $id;
                $this->getTelegram()->sendMessage(array(
                    "chat_id" => $id,
                    "text" => $this->getOption(0)
                ));
            }Else{
                $this->replyWithMessage( array(
                    "text" => "error,no id gets",
                    "parse_mode" => "Markdown"
                ) );
            }
        }
	}
}
