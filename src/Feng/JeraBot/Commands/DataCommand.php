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

class DataCommand extends Command {
    protected $name = "data";

    protected $description = "流量管理";

    protected $access = Access::ADMIN;

    /*protected $find = null;*/

    public function init() {
        $this->find = new FindEngine( "user", $this );
    }

    public function initOptions() {
        $this->find->attachOptions();
        $this
            ->addOption("num")
            ->describedAs("流量数(GB)");

        $this
            ->addOption("a")
            ->describedAs("增加多少流量(GB)")
            ->boolean();
        $this
            ->addOption("m")
            ->describedAs("减少多少流量(GB)")
            ->boolean();
        $this
            ->addOption("d")
            ->describedAs("除多少流量(GB)")
            ->boolean();
        $this
            ->addOption("mx")
            ->describedAs("乘多少流量(GB)")
            ->boolean();
    }

    public function handle( $arguments )
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
        $bridge = $this->find->getPanelBridge();

        $traffic = $this->getOption("num");

        if ($traffic !== null && is_numeric($traffic)) {
            $response = "";

            global $count, $total;
            $count = 0;
            $total = 0;

            foreach ($results as $user) {
                $username = $user->user_name;
                $transfer_enable = $user->transfer_enable;
                $old = $transfer_enable;
                if ($this->getOption("a")) {
                    $user->transfer_enable += $bridge->gbToBytes($traffic);
                }
                if ($this->getOption("m")) {
                    $user->transfer_enable -= $bridge->gbToBytes($traffic);
                }
                if ($this->getOption("d")) {
                    $user->transfer_enable /= $traffic;
                }
                if ($this->getOption("mx")) {
                    $user->transfer_enable *= $traffic;
                }
                if ($user->save()) {

                    $response .= "用户" . $username . " 修改成功\r\n";
                    $response .= "修改前流量是 " . $bridge->transTraffic($old) . " , 修改后是 " . $bridge->transTraffic($user->transfer_enable) . "\r\n";
                    $count++;

                } Else {

                    $response .= $username . "修改失败";
                    $response .= "修改前流量是 " . $bridge->transTraffic($old);
                    $count++;

                }
                if ( $count == 50 ) {
                    sleep(15);
                    $this->replyWithMessage(array(
                        "text" => $response,
                        "parse_mode" => "Markdown"
                    ));
                    $total += $count;
                    $count = 0;
                    $response = "";
                }
            }
        } Else {
            $this->replyWithMessage(array(
                "text" => "流量必须是数字,且必须给定。",
                "parse_mode" => "Markdown"
            ));
            Return;
        }
        if ($count != 0) {
            $total += $count;
            $this->replyWithMessage(array(
                "text" => $response ."\r\n" . "本次操作影响了 " . $total . " 位用户",
                "parse_mode" => "Markdown"
            ));
        }Else{
            $this->replyWithMessage(array(
                "text" => "本次操作影响了 " . $total . " 位用户",
                "parse_mode" => "Markdown"
            ));
        }
    }

}

