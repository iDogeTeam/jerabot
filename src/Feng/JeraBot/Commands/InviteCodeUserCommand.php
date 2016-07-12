<?php
/**
 * Created by PhpStorm.
 * User: never
 * Date: 2016/4/17
 * Time: 16:24
 */

namespace Feng\JeraBot\Commands;

use Telegram\Bot\Actions;
use Feng\JeraBot\Command;
use Feng\JeraBot\Access;
use Feng\JeraBot\FindEngine;

class InviteCodeUserCommand extends Command
{
    protected $name = "invite";

    protected $description = "查看你可以生成的邀请码";

    protected $access = Access::USER;


    public function initOptions()
    {
        $this
            ->addOption( "num" )
            ->describedAs("显示的数量,不设定此项则默认为1");
    }

    public function handle($argument)
    {
        $bridge = $this->getPanelBridge();

        global $num;
        $num = $this->getOption("num");

        if (false === $user = $this->getPanelUser()) {
            $this->replyWithMessage(array(
                "text" => "你还没有绑定 Doge 账户呢！"
            ));
            return;
        }
        //默认数量设置
        if ( !$num ){
            $num = 1;
        }

        $codes = $user->inviteCodes();

        $this->replyWithMessage(array(
            "text" =>  "*您的可用邀请*",
            "parse_mode" => "Markdown"
        ));

        $template .= <<<EOF
*这是一条邀请信息,点击链接即可注册,无需复制邀请码*
注册后可以在通知处加入群组
-----------------
邀请码编号：%s
邀请码：%s
-----------------
[点击这里注册](https://dogespeed.ga/auth/register?code=%s)
_DogeSpeed_ *Let's Make A Better World.*
EOF;
        foreach ($codes as $code) {

            if ( !$num ){
                $this->replyWithMessage(array(
                    "text" => "全部邀请码已经列出！"
                ));
                break;
            }

            $num--;

            $respond = sprintf(
                $template,
                $code->id,
                $code->code,
                $code->code
            );
            $this->replyWithMessage(array(
                "text" => $respond,
                "parse_mode" => "Markdown"
            ));
        }
        if ( $num != 0) {
            $this->replyWithMessage(array(
                "text" => "似乎你的邀请并没有这么多！"
            ));
        }
    }
}
