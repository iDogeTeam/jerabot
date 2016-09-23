<?php
/*
	Copyright (c) 2016, NeverBehave
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

class RegCommand extends Command
{
    protected $name = "reg";

    protected $description = "注册一个Doge账号";

    protected $access = Access::EVERYONE;
    
    protected  $pmOnly = true;

    public function initOptions()
    {
        $this
            ->addOption("y")
            ->describedAs("同意服务条款")
            ->boolean();
        $this
            ->addOption("upgrade")
            ->describedAs("升级用户")
            ->boolean();
    }

    public function handle($arguments)
    {

        if ($this->getOption("upgrade")) { //waiting for implement
            $this->replyWithMessage(array(
                "text" => "还未完善。"
            ));
            return;
        }
        $bridge = new PanelBridge();

        if ( !(false === $user = $this->getPanelUser()) ) {
            $this->replyWithMessage(array(
                "text" => "作弊Hi! 你已经绑定了 Doge 账户呢！"
            ));
            return;
        }

        if ($this->getOption("y")) {
            $this->replyWithMessage(array( //warning
                "text" => "看来你已经确认了我们的服务条款,欢迎你加入我们! 正在为你创建账号.."
            ));

            //init
            $random = $bridge->genRandomChar(32);
            $port = $bridge->getAvailablePort();
            //start dash!
            $user = $bridge->createUser();
            $user->user_name = $bridge->genRandomChar(6);
            $user->email = $random . "@hello.free";
            $user->pass = $bridge->getHash($random);
            $user->passwd = $bridge->genRandomChar(6);
            $user->port = $port;
            $user->method = "chacha20";
            $user->t = 0;
            $user->u = 0;
            $user->d = 0;
            $user->user_type = 1;
            $user->transfer_enable = $bridge->gbToBytes(3);
            $user->transfer_enable_next =  $bridge->gbToBytes(3);
            $user->invite_num = 0;
            $user->reg_ip = "0.0.0.0";
            $user->ref_by = 0;
            $user->allow_login = 0;
            //finish!
            if ($user->save()) {
                $this->replyWithMessage(array(
                    "text" => "创建账号成功!进行绑定操作中..."
                ));
                $this->logger->addInfo("reg {$user->user_name}, not assoc, awaiting...");
            } Else {
                $this->replyWithMessage(array( //warning
                    "text" => "创建账户失败,未知原因!请联系管理员。"
                ));
                return;
            }

            //now starting assooc
            $tid = $this->getUpdate()->getMessage()->getFrom()->getId();
            $user->telegram_id = $tid;
            $user->telegram_token = "";

            if ($user->save()) {
                $tuser = $this->getUpdate()->getMessage()->getFrom()->getUsername();
                $this->logger->addInfo("关联：Doge {$user->id} <---> Telegram {$tid}, tuser: @{$tuser}");
                $this->replyWithMessage(array(
                    "text" => "绑定成功！\xF0\x9F\x99\x8C"
                ));
                
                $this->triggerCommand("myinfo");
                
                $this->replyWithMessage(array(
                    "text" => "你现在可以通过指令 /node 获取节点信息! /myinfo 获取个人信息!"
                ));

            return;
            } Else {
                $this->replyWithMessage(array( //warning
                    "text" => "绑定失败,未知原因!请联系管理员"
                ));
                return;
            }
        }

        //default
        $this->replyWithMessage(array(
            "text" => "https://dogespeed.ga/tos"
        ));
        $this->replyWithMessage(array(
            "text" => "请仔细阅读上方tos,加入telegram群组获取帮助。如果确认,请回复下方指令开启你的Doge之旅!"
        ));
        $this->replyWithMessage(array(
            "text" => "/reg -y"
        ));
    }
}