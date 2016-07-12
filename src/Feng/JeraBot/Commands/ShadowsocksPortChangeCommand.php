<?php
/**
 * Created by PhpStorm.
 * User: neverbehave
 * Date: 2016/4/9
 * Time: 16:56
 */

namespace Feng\JeraBot\Commands;
use Telegram\Bot\Actions;
use Feng\JeraBot\Command;
use Feng\JeraBot\Access;
use Feng\JeraBot\FindEngine;

class ShadowsocksPortChangeCommand extends Command
{
    protected $name = "ShadowsocksChange";

    protected $description = "修改Shadowsocks服务端口和密码";

    protected $access = Access::EVERYONE;

    public function init() {
        $this->find = new FindEngine( "user", $this );
    }

    public function initOptions()
    {
        $this
            ->addOption("port")
            ->describedAs("修改端口");
        $this
            ->addOption("password")
            ->describedAs("修改密码");
    }

    public function handle($arguments)
    {
        $bridge = $this->getPanelBridge();
        $port = $this->getOption("port");
        $password = $this->getOption("password");
        $p = mb_strlen($password);


        if (false === $user = $this->getPanelUser()) {
            $this->replyWithMessage(array(
                "text" => "你还没有绑定 Doge 账户呢！"

            ));
            return;
        }
        //判断端口
        if ($port
            && $port > 10000
            && $port < 60000
        ) {
                try {
                    $results = $this->find->runQuery();
                } catch (\Exception $e) {
                    $this->replyWithMessage(array(
                        "text" => "出错了\xF0\x9F\x8C\x9A " . $e->getMessage()
                    ));
                    return;
                }

                if (!$results || !(0 === $results->count())) {
                    $this->replyWithMessage(array(
                        "text" => "端口已经被占用,请重新选择。"
                    ));
                    return;
                }
            $user->port = $port;
            if ($user->save()) {
                $this->replyWithMessage(array(
                    "text" => "端口修改成功!请确认:" . $port
                ));
                return;
            }
        }

        //判断密码
        if ($password
            && $p >= 8
            && preg_match("#[a-zA-Z]+#", $password)
            && preg_match("#[0-9]+#", $password)
        ) {
            $user->passwd = $password;
            if ($user->save()) {
                $this->replyWithMessage(array(
                    "text" => "密码修改成功!请确认:" . $password
                ));
                return;
            }
        }
        $this->replyWithMessage(array(

            "text" => "端口输入不正确,区间:10001-60000,或者密码不正确,请确认含有至少一个字符和数字,且长度大于8.请确认!Debug信息" . $password .$port . $p
        ));
    }

}