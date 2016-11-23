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

class NodeinfoCommand extends Command
{
    protected $name = "node";

    protected $description = "节点信息(默认只显示ss节点)";

    protected $access = Access::EVERYONE;

    /*protected $find = null;*/

    public function init()
    {

    }

    public function initOptions()
    {
        $this
            ->addOption("node")
            ->describedAs("指定节点(名称)");

        $this
            ->addOption("ss")
            ->describedAs("base64地址")
            ->boolean();

        $this
            ->addOption("allss")
            ->describedAs("全部的base64地址,用于安卓一次性导入")
            ->boolean();

        $this
            ->addOption("json")
            ->describedAs("显示节点的json")
            ->boolean();

        $this
            ->addOption("s")
            ->describedAs("附带显示节点状态")
            ->boolean();

        $this
            ->addOption("file")
            ->describedAs("只输出带备注的json文件")
            ->boolean();

        $this
            ->addOption("ac")
            ->describedAs("显示ac节点(仅限开通用户)")
            ->boolean();

        $this
            ->addOption("y")
            ->describedAs("跳过提示")
            ->boolean();
    }

    public function handle($arguments)
    {

        if (false === $user = $this->getPanelUser()) {
            $this->replyWithMessage(array(
                "text" => "你还没有绑定 Doge 账户呢！"
            ));
            return;
        }

        //Acquire nodeinfo
        $bridge = new PanelBridge();
        $ssnodes = $bridge->getNodes();
        //Acquire userinfo
        $password = $user->passwd;
        //ss节点信息
        $main = <<<EOF
服务器名称 %s
服务器id %s
服务器状态 %s
服务器流量比例: %s 倍
EOF;
        //节点状态
        $other = <<<EOF
\r\n-------------------
服务器地址: %s 
服务器链接端口: %s
服务器加密方式: %s
服务器注释:%s
服务器负载:%s
服务器在线人数:%s
服务器产生流量:%s
服务器在线时间:%s
EOF;
        //base64
        $qr = <<<EOF
\r\n-------------------
address: %s
EOF;
        //json 文件配置
        $json_style = <<<EOF
\r\n--------------------
  {
"server" : "%s",
"server_port" :%s,
"password" : "%s",
"method" : "%s",
"remarks" : "%s"  }
EOF;
        $part_style = <<<EOF
 {
"server" : "%s",
"server_port" :%s,
"password" : "%s",
"method" : "%s",
"remarks" : "%s"  }
EOF;
        //json 完整文件配置
        $file_style = <<<EOF
{
"configs" : [
            %s
            ],
"strategy" : null,
"index" : 10,
"global" : false,
"enabled" : false,
"shareOverLan" : false,
"isDefault" : false,
"localPort" : 1080,
"pacUrl" : null,
"useOnlinePac" : false,
"availabilityStatistics" : false}
EOF;

        global $response,$allss, $part;
        $response = "";
        $part = "" ;
        $allss = "";
        //指定节点
       if ( $this->getOption("node") != "" ) {
            $pointname = $this->getOption("node");

           //check vaild or not
          try{$pointnode = $bridge->searchNodes($pointname);}
           catch (\Exception $e) {
               $this->replyWithMessage(array(
                   "text" => "出现错误" . $e
               ));
               return;
           }
           $ssurl = $pointnode->custom_method ? $pointnode->method : $user->method. ":" . $password . "@" . $pointnode->server . ":" . $user->port;
           $ssqr = "ss://" . base64_encode($ssurl);

           $this->replyWithMessage(array(
               "text" => "信息检索到了!服务器id为" . $pointnode->name . "\r\n" . $ssqr
               ));
           }

        if ( !($this->getOption("ac") ||
            $this->getOption("ss") ||
            $this->getOption("s") ||
            $this->getOption("ac") ||
            $this->getOption("node") ||
            $this->getOption("json") ||
            $this->getOption("file") ||
            $this->getOption("allss")
        )
        ) {
            $this->replyWithMessage(array(
                "text" => "请使用 -help 获取帮助\r\n 例如 /node -help 查看帮助 或者\r\n /node -ss 查看节点链接地址"
            ));
            return;
        }
        
        if ( !$this->getOption("y") ) {
            $this->replyWithMessage(array(
                "text" => "请注意:节点流量比例设定\r\n最后计入的流量是你使用的实际流量乘以流量比例\r\n比如2就是说使用100m就算200m\r\n流量比例0的话就是不算流量\r\n以此类推...\r\n同时请注意汇总消息时间会较长,请不要反复发送指令。 "
            ));
        }

            //start dash!
        foreach ($ssnodes as $node) {
            $type = $node->type;
            $id = $node->id;
            $name = $node->name;
            $status = $node->status;
            $address = $node->server;
            if ( $node->custom_method == 1) {                        //judgement for method
                $method = $user->method;
            } Else {
                $method = $node->method;
            }
            $rate = $node->traffic_rate;
            $port = $user->port;
            $note = $node->info;
            $load = $node->getNodeLoad();
            $people = $node->getOnlineUserCount();
            $traffic = $node->getTrafficFromLogs();
            $time = $node->getNodeUptime();

                $response .= sprintf(  //main
                    $main,
                    $name,
                    $id,
                    $status,
                    $rate
                );

            if ($this->getOption("ac")) {
                    if ($user->ac_enable) {
                        if ($type == 2 || $type == 3) {
                            $response .= "\r\n是否AC:是";
                        } Else {
                            $response .= "\r\n是否AC:否";
                        }
                    } Else {
                        $response .= "\r\n是否AC:您无权访问此信息";
                    }
            }

            if ($this->getOption("s")) {   //details
                    $response .= sprintf(
                        $other,
                        $address,
                        $port,
                        $method,
                        $note,
                        $load,
                        $people,
                        $traffic,
                        $time
                    );
                }

            if ( $this->getOption("ss") || $this->getOption("allss") ) {
                $ssurl = $method . ":" . $password . "@" . $address . ":" . $port;
                $ssqr = "ss://" . base64_encode($ssurl);
                if ($this->getOption("ss")) {
                    $response .= sprintf(
                        $qr,
                        $ssqr
                    );
                }Else{
                    $allss .= sprintf(
                        $qr,
                        $ssqr,
                        "\r\n"
                    );
                }
            }

            if ($this->getOption("json")) {
                    $response .= sprintf(
                        $json_style,
                        $address,
                        $port,
                        $password,
                        $method,
                        $note
                    );
                }

            if ($this->getOption("file")) {
                $part .= "\r\n";
                $part .= sprintf(
                    $part_style,
                    $address,
                    $port,
                    $password,
                    $method,
                    $note
                );
                $part .= ",";
            }

            if ($this->getOption("file") == false
                && $this->getOption("allss") == false
                ) {
                    $this->replyWithMessage(array(
                        "text" => $response
                    ));
                    usleep(10);
                    $response = "";
                }

        }

        if ($this->getOption("file")){
            $part .= $part_style;
            $file = sprintf(
                $file_style,
                $part
            );

            $this->replyWithMessage(array(
                "text" => $file
            ));
        }

        if ( $this->getOption("allss") ){
            $this->replyWithMessage(array(
                "text" => $allss
            ));
        }

        //汇总用户信息
        $judge = '';
        if ( $this->getOption("ac") ) $judge .= " ac";
        if ( $this->getOption("ss") ) $judge .= " ss";
        if ( $this->getOption("s") ) $judge .= " s";
        if ( $this->getOption("node") ) $judge .= " node";
        if ( $this->getOption("json") ) $judge .= " json";
        if ( $this->getOption("file") ) $judge .= " file";
        if ( $this->getOption("allss") ) $judge .= " allss";
        $tuser = $this->getUpdate()->getMessage()->getFrom()->getUsername();
        $this->logger->addInfo( "获取列表：Doge {$user->id}，Name:{$user->user_name},TGID:{$user->telegram_id},tuser: @{$tuser}, 参数: [{$judge}]");
        $this->replyWithMessage(array(
            "text" => "消息发送完毕,频繁获取信息将会被列入黑名单。请谅解!"
        ));
        return;
    }

}