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

class LoginCommand extends Command
{
    protected $name = "login";

    protected $description = "登录";

    protected $access = Access::EVERYONE;

    protected $pmOnly = true;

    /*protected $find = null;*/


    public function initOptions()
    {
        $this
            ->addOption(0)
            ->describedAs("直接输安全码在后面就好了");

    }

    public function handle($arguments)
    {
        $tid = $this->getUpdate()->getMessage()->getFrom()->getId();
        $tuser = $this->getUpdate()->getMessage()->getFrom()->getUsername();
        $bridge = new PanelBridge();

        if (false === $user = $this->getPanelUser()) {
            $this->replyWithMessage(array(
                "text" => "你还没有绑定 Doge 账户呢！"
            ));
            return;
        }

        if ( empty( $this->getOption( 0 ) ) ) {
            $this->triggerCommand( $this->name, "-help" );
            return;
        }


        $get_code = $this->getOption(0);

        $code = $bridge->verifyTgCode( $get_code );

        //pre

        if ( $code == null ){
            $this->replyWithMessage(array(
                "text" => "安全码不存在!请确认!"
            ));
            return;
        }

        if ($code->is_verify == true ){
            $this->replyWithMessage(array(
                "text" => "安全码出现问题!请重试!"
            ));
            return;
        }

        $created_at = $code->created_at;
        $expire_at = strtotime('+180 seconds', $created_at);
        if ( $expire_at - strtotime("now") < 0 ){
            $this->replyWithMessage(array(
                "text" => "安全码过期了!请重试!"
            ));
            $code->delete();
            return;
        }

        $code->user_id = $user->id;
        $code->is_verify = 1;
        $code->created_at = strtotime("now");
        if ( $code->save() ){
            $this->replyWithMessage(array(
                "text" => "完成安全认证!请在页面上点击登录继续!"
            ));
            $this->logger->addInfo( "登录：Doge {$user->id}，Name:{$user->user_name},TGID:{$user->telegram_id}, tuser{$tuser}" );
            return;

        }Else{
            $this->replyWithMessage(array(
                "text" => "出现未知错误,请联系管理员"
            ));
            return;

        }
    }
}
