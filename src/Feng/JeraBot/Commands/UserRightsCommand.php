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

class UserRightsCommand extends Command {
    protected $name = "userrights";

    protected $description = "用户权限设定";

    protected $access = Access::ADMIN;

    /*protected $find = null;*/

    public function init() {
        $this->find = new FindEngine( "user", $this );
    }
    public function initOptions() {
        $this->find->attachOptions();
        $this
            ->addOption("l")
            ->describedAs("目标等级");
    }

    public function handle( $arguments ) {
        try {
            $results = $this->find->runQuery();
        } catch ( \Exception $e ) {
            $this->replyWithMessage( array(
                "text" => "出错了\xF0\x9F\x8C\x9A " . $e->getMessage()
            ) );
            return;
        }
        if ( !$results || 0 === $results->count() ) {
            $this->replyWithMessage( array(
                "text" => "并没有找到符合要求的用户"
            ) );
            return;
        }
        $level =$this->getOption( "l" );
        $users = $this->find->runQuery();
        $bridge = $this->find->getPanelBridge();
        if ( $level <= 5
            &&   $level >= 1 ){
            foreach ( $results as $user ) {
                $old = $user->user_type;
                $username = $user->user_name;
                if ( $old == $level ) {
                    $this->replyWithMessage(array(
                        "text" => "用户等级已经为 " . $old . " 无需修改"
                    ));
                    return;
                }
                $user->user_type = $level;
                if ( $user->save() ) {
                    $this->replyWithMessage(array(
                        "text" => "用户 " . $username . " 原等级为 " . $old . " ,现等级为 " . $level ." #Success！"
                    ));
                    return;
                }
                Else{
                    $this->replyWithMessage(array(
                        "text" => "用户 " . $username . " 更改失败，当前等级 " .$old . " 没有改变 #Fail"
                    ));
                    return;
                }
            }
        }
        Else {
            $this->replyWithMessage( array(
                "text" => "等级输入不正确，请检查。". $level
            ) );
            return;
        }

    }

}
