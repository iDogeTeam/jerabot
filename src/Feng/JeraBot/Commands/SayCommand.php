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
use Feng\JeraBot\FindEngine;

class SayCommand extends Command
{
    protected $name = "s";

    protected $description = "和我们说些什么";

    protected $access = Access::EVERYONE;

    /*protected $find = null;*/
    public function init()
    {
        $this->find = new FindEngine("user", $this);
    }

    public function initOptions()
    {
        $this
            ->addOption(0)
            ->describedAs("直接输你想说的话在后面就好了,不要空格哦!");

    }

    public function handle($arguments)
    {
        $tid = $this->getUpdate()->getMessage()->getFrom()->getId();

        if (empty($this->getOption(0))) {
            $this->triggerCommand($this->name, "-help");
            return;
        }

        $get_words = $this->getOption(0);

        if (false === $user = $this->getPanelUser()) {

            $this->logger->addInfo("message from unknown, {$get_words}, {$tid}");

        } Else {
            $this->logger->addInfo("Message from doge {$user->id}! {$get_words}, {$tid}, {$user->user_name}");
        }
        return;
    }
}

