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
use Feng\JeraBot\FindEngine;

class UsernoteCommand extends Command
{
    protected $name = "info";

    protected $description = "流量管理";

    protected $access = Access::ADMIN;

    /*protected $find = null;*/

    public function init()
    {
        $this->find = new FindEngine("user", $this);
    }

    public function initOptions()
    {
        $this->find->attachOptions();

        $this
            ->addOption("note")
            ->describedAs("备注");

        $this
            ->addOption("q")
            ->describedAs("钦点用户");

        $this
            ->addOption("d")
            ->describedAs("捐赠");

        $this
            ->addOption("admin")
            ->describedAs("管理员");

        $this
            ->addOption("login")
            ->describedAs("面板登录");

        $this
            ->addOption("m")
            ->describedAs("更改")
            ->boolean();

        $this
            ->addOption("prohibit")
            ->describedAs("禁用")
            ->boolean();
    }

    public function handle($arguments)
    {

        try {
            $results = $this->find->runQuery();
        } catch (\Exception $e) {
            $this->replyWithMessage(array(
                "text" => "出错了\xF0\x9F\x8C\x9A " . $e->getMessage()
            ));
            return;
        }
        if (!$results || 0 === $results->count()) {
            $this->replyWithMessage(array(
                "text" => "并没有找到符合要求的用户"
            ));
            return;
        }

        global $response;
        $response = "";
        foreach ($results as $user) {
            $note = $this->getOption("note");
            $q = $this->getOption("q");
            $d = $this->getOption('d');
            $admin = $this->getOption("admin");
            $login = $this->getOption("login");
            $response .= "Doge ID";
            $response .= $user->id;
            $response .= "\r\n";
            $response .= "钦点用户: ";
            $response .=  $user->is_protected ? "是" : "否" ;
            $response .= "\r\n";
            $response .= "管理员:";
            $response .=  $user->is_admin ? "是" : "否" ;
            $response .= "\r\n";
            $response .= "用户等级:" . $user->user_type . "\r\n";
            $response .= "捐赠金额:" . $user->donate_amount . "\r\n";
            $response .= "面板登录" ;
            $response .= $user->allow_login ? "是" : "否" ;
            $response .= "\r\n";
            if ( $user->note!= null ) {
                $response .= "备注:\r\n";
                $response .= $user->note;
                $response .= "\r\n";
            }
            $response .= "端口:" . $user->port . "\r\n";
            $response .= "邮箱:" . $user->email . "\r\n";
            $response .= "用户名:" . $user->user_name . "\r\n";
            $this->replyWithMessage(array(
                "text" => $response
            ));
            $response = ""; //clean
            //fliter
            if ($this->getOption("m")) {
                if ( $this->getOption("note")!= null) {
                    $user->note = $note;
                }
                if ( $this->getOption("d")!= null ) {
                    $user->donate_amount = $d;
                }
                if ( $this->getOption("q")!= null ) {
                    $user->is_protected = $q;
                }
                if ( $this->getOption("admin")!= null ) {
                    $user->is_admin = $admin;
                }
                if ( $this->getOption("login")!= null ){
                    $user->allow_login = $login;
                }
                if ($user->save()) {
                        $this->replyWithMessage(array(
                            "text" => "用户更改成功!"
                        ));
                    } Else {
                        $this->replyWithMessage(array(
                            "text" => "更改失败!"
                        ));
                }
            }
            //del
            if ($this->getOption("prohibit")){
                $user->allow_login = 0;
                $user->enable = 0;
                if ($user->ac_enable){
                    $user->ac_enable = 0;//miss sth. here
                }
                if ($user->save()){
                    $this->replyWithMessage(array(
                        "text" => "用户禁用成功!"
                    ));
                } Else {
                    $this->replyWithMessage(array(
                        "text" => "用户禁用失败!"
                    ));
                }
            }
        usleep(100);
        }//end of foreach
        $this->replyWithMessage(array(
            "text" => "这是结尾!"
        ));
    }
}