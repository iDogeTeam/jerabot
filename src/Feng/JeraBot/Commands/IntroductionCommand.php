<?php
/**
 * Created by PhpStorm.
 * User: neverbehave
 * Date: 16/4/7
 * Time: 下午2:27
 */
namespace Feng\JeraBot\Commands;

use Telegram\Bot\Actions;
use Feng\JeraBot\Command;
use Feng\JeraBot\Access;

class IntroductionCommand extends Command
{
    protected $name = "intro";

    protected $description = "加入我们!";

    protected $access = Access::EVERYONE;

    public function initOptions()
    {
        $this
            ->addOption("h")
            ->describedAs("完整版介绍")
            ->boolean();
        $this
            ->addOption("r")
            ->describedAs("资源下载")
            ->boolean();
        $this
            ->addOption("d")
            ->describedAs("捐赠:Donation")
            ->boolean();
        $this
            ->addOption("tos")
            ->describedAs("我们的用户协议")
            ->boolean();
    }


    public function handle($arguments)
    {

        $tid = $this->getUpdate()->getMessage()->getFrom()->getId();
        $tuser = $this->getUpdate()->getMessage()->getFrom()->getUsername();

        if ( $this->getOption("tos") ){
            $this->replyWithMessage(array(
                "text" => 'https://dogespeed.ga/tos/',
                "parse_ mode" => "Markdown"
            ));
            return;
        }
          if ( $this->getOption("r") ) {
            //列出资源列表
           /** $keyboard = [
                ['Shadowsocks 安卓','Shadowsocks 安卓扫码器'],
                ['Anyconnect Win', 'Anyconnect 安卓'],
                ['Shadowsocks Win', "Shadowsocks OS X"],
                ['没有我想要的，请列出所有资源']
            ];
            $reply_markup = $telegram->replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            $this->replyWithMessage(array(
                "text" => '常用工具，请选择您需要什么?',
                "reply_markup" => $reply_markup,
                "parse_ mode" => "Markdown"
            )); */
              $resource = <<<EOF
*Dogespeed* For A Better World
_点击蓝字开始下载_
网页版: doge.me/download
*Shadowsocks* 专区
[Android Shadowsocks](https://dogespeed.ga/download/shadowsocks-nightly-2.9.4.apk)
[Android QR scanner](https://dogespeed.ga/download/Shadowsocks_Scanner.apk) 
[Windows Shadowsocks](https://dogespeed.ga/download/Shadowsocks-win-2.5.6.zip)  
[Mac Shadowsocks](https://dogespeed.ga/download/ShadowsocksX-2.6.3.dmg)
[Shadowroctet Link](https://appsto.re/cn/UDjM3.i)
[Windows Official](https://shadowsocks.org/en/download/clients.html) 
[Official Website](https://shadowsocks.org/en/download/clients.html) 
[Android Shadowsocks R](https://dogespeed.ga/download/SSR-2.9.9.apk)  
[Windows Shadowsocks R](https://dogespeed.ga/download/SSR4-DONATE.exe)  
[Linux Lib](https://dogespeed.ga/download/shadowsocks-libev-2.4.0.tar.gz)  
#####################
*Anyconnect* 专区
[Android Anyconnect](https://dogespeed.ga/download/com.cisco.anyconnect.vpn.android.avf-4.0.05026.apk)  
[Windows Anyconnect](https://dogespeed.ga/download/anyconnect-win-3.1.10010-web-deploy-k9.exe) 
[Mac Link](https://dogespeed.ga/download/vpnclient-darwin-4.8.00.0490-GUI-k9.dmg)  
[Anyconnect AppStore link](https://itunes.apple.com/us/app/cisco-anyconnect/id392790924?mt=8)  
[Google Play](https://play.google.com/store/apps/details?id=com.cisco.anyconnect.vpn.android.avf)  
[Anyconnect Official](https://software.cisco.com/download/navigator.html?mdfid=283000185&flowid=72322) 
#####################
*Other* 专区
[SwitchyOmega for Chrome](https://dogespeed.ga/download/SwitchyOmega2.3.15.crx)
[Proxifier for Windows](https://dogespeed.ga/download/HA-Proxifier321-LDR.rar)  
[FinalSpeed for Windows](https://dogespeed.ga/download/finalspeed_install_Windows.exe)  
[FinalSpeed for Linux](https://dogespeed.ga/download/finalspeed_for_linux.zip) 
更新日期 2016/03/24
EOF;
              $this->replyWithMessage(array(
                  "text" => $resource,
                  "parse_mode" => "Markdown"
              ));
              $this->logger->addInfo("download! {$tid}! tuser: @{$tuser}");
            return;
        }
        if ( $this->getOption("d") ) {
            //捐赠方式
            $template = <<<EOF
*Dogespeed* For A Better World
暂时仅支持支付宝,如有其他需求请联系我们
@zhaofeng @NeverBehave @youjiuzhiyi
EOF;
            $respond = sprintf(
                $template
            );
            $this->replyWithMessage(array(
                "text" => $respond,
                "parse_mode" => "Markdown"
            ));
            $this->replyWithPhoto(array(
                "photo" => 'https://dogespeed.ga/donate.jpg'
            ));
            return;
        }
        $starter = <<<EOF
Website: doge.me
*汪*－最忠实的盟友——
他们的世界，你触手可及，却又擦肩而过 

*星*－最绚丽的夜空——
他们的世界，看似遥远无边，然可任性畅游 

*人*－最无私的奉献——
执着，和那一丝固执，始终支持着他们前进
EOF;
        $foreign = <<<EOF
*Dogespeed* For A Better World
#本为更自由的互联网而设。
#Built for unblock what you cannot access.
----------------------
_If you are not from China, and want to join us:_
_please join the telegram group below and ask for help._
---> @dogespeedofficial <---
if you have any trouble, try sending message to
" @NeverBehave @zhaofeng "
Email is also acceptable : hello@ssworld.ga
----------------------
加入Telegram群组索要邀请码
-->这是免费的<--
加入群组后主动"汪"一声哦~

目前我们为 Android、Linux、Windows、iOS 平台提供 Shadowsocks 服务（iOS 提供 Surge 配置文件，Anyconnect服务)
未来会推出 APNP 代理，HTTPS代理

<whisper>支持AnyConnect客户端!WP大法好!</whisper>
EOF;
        $communication = <<<EOF

*目前本站处于封闭管理状态，有任何问题请使用Telegram发起（最快），或者通过回答问卷。*
----------------------
Telegram（电报）联系方式：（可以在下方直接点击加入，无需复制）
[----------->官方Channel<-----------](https://telegram.me/DogeSpeedOfficialChannel)
↑↑↑↑↑↑↑↑一定要加入啊↑↑↑↑↑↑↑↑↑↑↑
官方交流群:@dogespeedofficial
[意见反馈问卷](http://goo.gl/forms/utbU9cqE4z)
其实可以直接私信站长的.
----------------------
Shadowsocks 是一个开源的的轻量级服务器中转包传输工具。项目地址：https://github.com/shadowsocks/
OpenConnect服务项目地址：http://www.infradead.org/ocserv/

EOF;
        $short = <<<EOF
*SS/VPN/Https/国内中转/APNP*

任何指令后方加入-help获得帮助，例如 /help -help

Telegram（电报）联系方式：（可以在下方直接点击加入，无需复制）
[----------->官方Channel<-----------](https://telegram.me/DogeSpeedOfficialChannel)
↑↑↑↑↑↑↑↑一定要加入啊↑↑↑↑↑↑↑↑↑↑↑
官方交流群:@dogespeedofficial
加入Telegram群组索要邀请码
-->这是免费的<--
加入群组后主动"汪"一声哦~
主页：doge.me
或者dogespeed.ga

目前我们为 Android、Linux、Windows、iOS 平台提供 Shadowsocks 服务（iOS 提供 Surge 配置文件，Anyconnect服务)
未来会推出 APNP 代理，HTTPS代理
EOF;
        if ( $this->getOption('h') ) {

            //为了分开消息多次发送
            $this->replyWithMessage(array(
                "text" => $starter,
                "parse_mode" => "Markdown"
            ));
            $this->replyWithMessage(array(
                "text" => $foreign,
                "parse_mode" => "Markdown"
            ));
            $this->replyWithMessage(array(
                "text" => $communication,
                "parse_mode" => "Markdown"
            ));
            return;

        }
        $this->replyWithMessage(array(
            "text" => '您现在看到的是简洁版介绍，请输入',
            "parse_mode" => "Markdown"
        ));
        $this->replyWithMessage(array(
            "text" => '/intro -h',
            "parse_mode" => "Markdown"
        ));
        $this->replyWithMessage(array(
            "text" => '获取完整版简介',
            "parse_mode" => "Markdown"
        ));
        $this->replyWithMessage(array(
            "text" => $short,
            "parse_mode" => "Markdown"
        ));

        $this->logger->addInfo("Intro {$tid}! tuser: {$tuser}");
    }
}