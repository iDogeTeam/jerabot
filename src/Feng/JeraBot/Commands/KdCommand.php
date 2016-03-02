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

class KdCommand extends Command {
	protected $name = "kd";

	protected $description = "查快递";

	protected $access = Access::EVERYONE;

	protected $apiKey = "";

	protected $api = "http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx";

	protected $carrierMapping = array(
		"EMS" => array(
			"name" => "EMS",
			"keywords" => array( "ems" ),
		),
		"JD" => array(
			"name" => "京东",
			"keywords" => array( "京东", "jd" ),
		),
		"ZTO" => array(
			"name" => "中通",
			"keywords" => array( "中通", "zto" ),
		),
		"STO" => array(
			"name" => "申通",
			"keywords" => array( "申通", "sto" ),
		),
		"YTO" => array(
			"name" => "胡搅蛮缠尼玛就是不给你派送的圆通大师 \xF0\x9F\x90\xB6",
			"keywords" => array( "圆通", "圆", "yto" ),
		),
		"SF" => array(
			"name" => "顺丰 \xF0\x9F\x91\x8D",
			"keywords" => array( "顺丰", "sf" ),
		),
		"YD" => array(
			"name" => "韵达",
			"keywords" => array( "韵达", "yd" ),
		),
		"HHTT" => array(
			"name" => "天天等快递天天等不到的天天快递",
			"keywords" => array( "天天" ),
		),
	);

	public function init() {
	}

	public function initOptions() {
		$this
			->addOption( "carrier" )
			->aka( "c" )
			->describedAs( "快递公司中文名称" )
		;
		$this
			->addOption( 0 )
			->aka( "n" )
			->describedAs( "单号" )
		;
	}

	public function handle( $arguments ) {
		$this->apiKey = $this->bot->getConfig( "kdniaokey" );
		if ( empty( $this->apiKey ) ) {
			$this->replyWithMessage( array(
				"text" => "然而并没有设置 API key \xF0\x9F\x90\xB6"
			) );
			return;
		}
		if ( empty( $this->getOption( "carrier" ) ) ) {
			$this->triggerCommand( $this->name, "--help" );
			return;
		}
		$carrier = $this->resolveCarrier( $this->getOption( "carrier" ) );
		$number = $this->getOption( 0 );
		if ( false === $carrier ) {
			$carriers = "\r\n资瓷的快递：";
			foreach ( $this->carrierMapping as $carrier ) {
				$carriers .= $carrier['name'] . "、";
			}
			$carriers = rtrim( $carriers, "、" );
			$this->replyWithMessage( array(
				"text" => "什么快递啊，我不认识 \xF0\x9F\x90\xB6" . $carriers
			) );
			return;
		}
		try {
			$data = $this->trackPackage( $carrier, $number );
		} catch ( \Exception $e ) {
			$this->replyWithMessage( array(
				"text" => "\xF0\x9F\x98\xB7 " . $e->getMessage()
			) );
			return;
		}
		if ( $data ) {
			$rendering = $this->renderTrail( $data );
			$response = $this->carrierMapping[$carrier]['name'] . "\r\n" . $rendering;
			$this->replyWithMessage( array(
				"text" => $response,
				"parse_mode" => "Markdown"
			) );
		} else {
			$this->replyWithMessage( array(
				"text" => "\xF0\x9F\x98\xB7"
			) );
		}
	}

	public function trackPackage( $carrier, $number ) {
		$keyArray = explode( ":", $this->apiKey );
		$apiId = $keyArray[0];
		$apiSecret = $keyArray[1];
		$json = json_encode( array(
			"ShipperCode" => $carrier,
			"LogisticCode" => $number,
		) );
		$signature = urlencode( base64_encode( md5( $json . $apiSecret ) ) );
		$request = array(
			"EBusinessID" => $apiId,
			"RequestType" => "1002",
			"RequestData" => urlencode( $json ),
			"DataType" => "2",
			"DataSign" => $signature,
		);
		$response = \Requests::post( $this->api, array(), $request );
		$data = json_decode( $response->body, true );
		if ( $data['Success'] ) {
			$return = array();
			$return['carrier'] = $carrier;
			$return['number'] = $number;
			$return['trail'] = array();
			foreach ( $data['Traces'] as $entry ) {
				$a = array();
				$a['time'] = $entry['AcceptTime'];
				$a['message'] = $entry['AcceptStation'];
				$return['trail'][] = $a;
			}
			return $return;
		}
		return false;
	}

	public function resolveCarrier( $text ) {
		foreach ( $this->carrierMapping as $carrier => $details ) {
			foreach ( $details['keywords'] as $keyword ) {
				if ( false !== strpos( strtolower( $text ), $keyword ) ) return $carrier;
			}
		}
		return false;
	}

	public function renderTrail( $data, $renderer = "verbatim" ) {
		switch ( $renderer ) {
			default:
			case "verbatim":
				return $this->verbatimRenderer( $data );
		}
	}

	protected function verbatimRenderer( $data ) {
		$return = "";
		foreach ( $data['trail'] as $entry ) {
			$return .= "*" . $entry['time'] . "* " . $entry['message'] . "\r\n";
		}
		return trim( $return );
	}
}
