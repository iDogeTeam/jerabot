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

class GiftcodeCommand extends Command
{
    protected $name = "gift";

    protected $description = "礼品码";

    protected $access = Access::EVERYONE;

    /*protected $find = null;*/


    public function initOptions()
    {
        $this
            ->addOption( 0 )
            ->describedAs("直接输礼品码在后面就好了");
        
    }

    public function handle($arguments)
    {
        $bridge = new PanelBridge();
        $tid = $this->getUpdate()->getMessage()->getFrom()->getId();
        $tuser = $this->getUpdate()->getMessage()->getFrom()->getUsername();
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

        $code = $bridge->getGiftCode($get_code);

        if ($code->code == null) {
            $this->replyWithMessage(array(
                "text" => "礼品码不存在!"
            ));
            return;
        }

        $get_date = $code->expire_at;

        if ($get_date !== null) {
            $get_date = strtotime($get_date);
            $now = strtotime("now");
            if ($get_date - $now < 0) {
                $this->replyWithMessage(array(
                    "text" => "礼品码已经过期!"
                ));
                return;
            }
        }

        $user_type = $user->user_type;
        $level = $code->level;
        if ( $level > $user_type ) {
            $this->replyWithMessage(array(
                "text" => "Doge等级不足以使用邀请码"
            ));
            return;
        }

        $user_id = $user->id;
        $users = $code->used_users;
        $ids = explode("|", $users);
        if ( in_array($user_id, $ids) ) {
            $this->replyWithMessage(array(
                "text" => "这只Doge使用过这个礼品码了呢!"
            ));
            return;
        }

        //Start Dash!
        $code_type = $code->code_type;
        switch ($code_type) {
            case 0:  //流量
                $traffic = $code->traffic;
                $user->transfer_enable = $user->transfer_enable + $bridge->gbToBytes($traffic);
                break;

            case 1:  //等级
                $level = $code->gift_level;
                $this->user->user_type = $level;
                break;

            case 2:  //Anyconnect
                if( $this->user->ac_enable) continue;
                $acname = Tools::genRandomChar(16);
                while(User::where("ac_user_name","=",$acname)->count())
                    $acname = $bridge->genRandomChar(16);
                $acpasswd = $bridge->genRandomChar(16);
                $user->ac_enable = 1;
                $user->ac_user_name = $acname;
                $user->ac_passwd = $acpasswd;
                break;

            case 3:  //Telegram 用户验证需要
                $telegram_id = $user->telegram_id;
                if ($telegram_id <= 0) {
                    $this->replyWithMessage(array(
                        "text" => "这个礼品码要Doge绑定telegram才能用!"
                    ));
                    return;  //没有绑定telegram
                }
                $traffic = $code->traffic;
                $this->user->transfer_enable = $this->user->transfer_enable + Tools::toGB($traffic);
        }
        $user->gift_count += 1;
        $code->counts += 1;
        $code->used_users .= "|" . $user->id;

        if (!$code->save()){
            $this->replyWithMessage(array(
                "text" => "应用信息到礼品码时发生错误,请重试!"
            ));
            return;
        }
        if (!$user->save()) {
            $this->replyWithMessage(array(
                "text" => "应用信息失败!请联系管理员"
            ));
            return;
        }
        $response = "邀请码应用成功!";
        switch ($code_type){
            case 0:
                $response.="\r\n_{$traffic}_GB流量已经生效。" ;
                break;
            case 1;
                $response.="\r\nDoge等级目前是_{$level}_";
                break;
            case 2:
                $response.="\r\nAnyConnect 已开通, /myinfo 获得连接信息, /change 更改用户名密码。";
                break;
        }
        $this->replyWithMessage(array(
            "text" => $response,
            "parse_mode" => "Markdown"
        ));
        $this->logger->addInfo( "使用礼品码：Doge {$user->id}，Name:{$user->user_name},TGID:{$user->telegram_id}, username: {$tuser} 参数: {$get_code}");
    }
    }