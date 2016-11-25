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

class RebootServiceCommand extends Command
{
    protected $name = "shell";

    protected $description = "做一些羞羞的事情";

    protected $access = Access::ADMIN;

    /*protected $find = null;*/


    public function initOptions()
    {
        $this
            ->addOption('r')
            ->describedAs("重启服务器SS服务")
            ->boolean();
        $this
            ->addOption('command')
            ->describedAs("坏人！不许看！");

    }

    public function handle($argument){
        // init
        $output = 'Nothing';
        $command = "bash /opt/jerabot/restart.sh";
        $user = $this->getPanelUser();
        $this->logger->addInfo( "！！服务器指令开始：Doge {$user->id}，Name:{$user->user_name},TGID:{$user->telegram_id}");
        if ($this->getOption('r')) {$output = shell_exec($command);}
        elseif (!empty($this->getOption('command'))){
            $output = shell_exec($this->getOption('command'));
        }
        if ($output === NULL) $output = '可能发生了错误或者结果是没有输出的';
        $this->replyWithMessage(array(
        "text" => $output,
            "parse_mode" => "Markdown"
        ));
        $this->logger->addInfo( "！！服务器指令结束：Doge {$user->id}，Name:{$user->user_name},TGID:{$user->telegram_id} 结果: {$output}");
    }
}