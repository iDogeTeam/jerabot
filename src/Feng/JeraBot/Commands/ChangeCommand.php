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
use Feng\JeraBot\PanelBridge;


class ChangeCommand extends Command
{
    protected $name = "change";

    protected $description = "修改Shadowsocks/Anyconnect服务(端口或密码或用户名)";

    protected $access = Access::EVERYONE;

    public function init() {
        $this->find = new FindEngine( "user", $this );
        $this->replyWitChatAction( array(
            "action" => "typing"
        ) );
    }

    public function initOptions()
    {
     /**   $this
            ->addOption("port")
            ->describedAs("修改端口"); **/
        $this
            ->addOption("password")
            ->describedAs("修改Shadowsocks密码");
        $this
            ->addOption("acpasswd")
            ->describedAs("修改Anyconnect密码");
        $this
            ->addOption("acusername")
            ->describedAs("修改Anyconnect用户名");
        $this
            ->addOption("nc")
            ->describedAs("修改昵称");
    }

    public function handle($arguments)
    {
        $bridge = new PanelBridge();
        $port = $this->getOption("port");
        $password = $this->getOption("password");
        $p = mb_strlen($password);
        $ac_passwd = $this->getOption("acpasswd");
        $ac_pass = mb_strlen($ac_passwd);
        $ac_username = $this->getOption("acusername");
        $ac_user = mb_strlen($ac_username);
        $nc = $this->getOption("nc");

        if (false === $user = $this->getPanelUser()) {
            $this->replyWithMessage(array(
                "text" => "你还没有绑定 Doge 账户呢！"

            ));
            return;
        }
        //判断端口
       /** if ($port
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
            if ( $user->port >32768
                || $user->port < 10000){
                $user->transfer_enable += $bridge->mbToBytes( 2048 );
                $this->replyWithMessage(array(
                    "text" => "主动修改端口加成2GB!www"
                ));
            }
            $user->port = $port;
            if ($user->save()) {
                $this->replyWithMessage(array(
                    "text" => "端口修改成功!请确认:" . $port
                ));
                return;
            }
        } **/

        //判断密码
        if ($password
            && $p >= 8
            && preg_match("#[a-zA-Z]+#", $password)
            && preg_match("#[0-9]+#", $password)
        ) {
            $user->passwd = $password;
            if ($user->save()) {
                $this->replyWithMessage(array(
                    "text" => "Shadowsocks密码修改成功!请确认: " . $password
                ));
                return;
            }
        }

        //判断Anyconnect是否开通
        if ($ac_passwd||$ac_username) {
            if (!$user->ac_enable) {
                $this->replyWithMessage(array(
                    "text" => "您无权修改您的AnyConnect服务相关设置!"
                ));
                $user->ac_user_name = "";
                $user->ac_passwd = "";
                $user->save();
                return;
            }
        }

        //判断AnyConnect密码

        if ($ac_passwd
        &&  $ac_pass >=6
        ) {
            if (mb_strlen($user->ac_user_name) + $ac_pass >= 14) {
                $user->ac_passwd = $ac_passwd;
                if ($user->save()) {
                    $this->replyWithMessage(array(
                        "text" => "Anyconnect密码修改成功!请确认: " . $ac_passwd
                    ));
                    return;
                }
            } Else {
                $this->replyWithMessage(array(
                    "text" => "由于安全限制,用户名位数和密码位数总和必须大于14.你可以先修改您的密码,密码位数和用户名位数都必须大于4."
                ));
                return;
            }
        }
        //判断Anyconnect用户名

        if ( $ac_username
            && $ac_user >=4
        ) {

            if ( !$bridge->AnyConnectUser($ac_username) ) { //唯一性检查
                $this->replyWithMessage(array(
                    "text" => "用户名已经被占用,请重新选择。"
                ));
                return;
            }
            if ( mb_strlen($user->ac_passwd) + $ac_user >= 14 ) {

                $user->ac_user_name = $ac_username;
                if ($user->save()) {
                    $this->replyWithMessage(array(
                        "text" => "Anyconnect用户名修改成功!请确认: " . $ac_username
                    ));
                    return;
                }
            }Else{
                $this->replyWithMessage(array(
                    "text" => "由于安全限制,用户名位数和密码位数总和必须大于14.你可以先修改您的密码,密码位数和用户名位数都必须大于4."
                ));
                return;
            }
        }

        if ( $nc &&
        !preg_match("/[_*]", $nc)){
            $user->user_name = $nc;
            if ( $user->save() ){
                $this->replyWithMessage(array(
                    "text" => "昵称修改成功!请确认: " . $nc
                ));
                return;
            }
        }

        $this->replyWithMessage(array(
            "text" => "Shadowsocks密码不正确,请确认含有至少一个字符和数字,且长度大于8.Anyconnect密码至少8位,用户名至少四位。昵称中不能含有*和_ 。请确认!"
        ));
    }


}