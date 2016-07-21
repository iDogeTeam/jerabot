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

namespace Feng\JeraBot;

use Feng\JeraBot\Utils;
use Feng\JeraBot\Command;
use Feng\JeraBot\PanelBridge;

class FindEngine {
	protected $type = "user";
	protected $types = array(
		"user" => array(
			"model" => "User",
			"properties" => array(
				"id" => array(
					"numeric" => true,
					"long" => "id",
					"short" => null,
					"description" => "ss-panel ID",
				),
				"port" => array(
					"numeric" => true,
					"long" => "port",
					"short" => null,
					"description" => "端口号",
				),
				"user_name" => array(
					"long" => "user_name",
					"short" => null,
					"description" => "昵称",
				),
				"email" => array(
					"long" => "email",
					"short" => null,
					"description" => "邮箱",
				),
				"method" => array(
					"long" => "method",
					"short" => null,
					"description" => "自定义加密方式",
				),
				"is_admin" => array(
					"numeric" => true,
					"long" => "is-admin",
					"short" => null,
					"description" => "是否管理员",
				),
			"user_type" => array(
					"numeric" => true,
					"long" => "user_type",
					"short" => null,
					"description" => "用户类型",
				),
				"ac_enable" => array(
					"numeric" => true,
					"long" => "ac-enable",
					"short" => null,
					"description" => "是否开通了 AnyConnect",
				),
				"telegram_id" => array(
					"numeric" => true,
					"long" => "telegram-id",
					"short" => null,
					"description" => "绑定的 Telegram ID",
				),
				"is_protected" => array(
					"numeric" => true,
					"long" => "is_protected",
					"short" => null,
					"description" => "钦点用户",
				),
				"ac_passwd" => array(
					"long" => "ac_passwd",
					"short" => null,
					"description" => "AnyConnect服务密码"
				),
				"ac_user_name" => array(
					"long" => "ac_user_name",
					"short" => null,
					"description" => "AnyConnect服务用户"
				)
			),
		),
	);
	protected $numericOperators = array(
		"!=" => "<>",
		">=" => ">=",
		"<=" => "<=",
		"<>" => "<>",
		">" => ">",
		"<" => "<",
		"!" => "<>",
	);

	protected $command = null;
	protected $bridge = null;

	public function __construct( $type = "user", Command &$command ) {
		if ( !isset( $this->types[$type] ) ) {
			throw new \InvalidArgumentException( "No such type: $type" );
			return false;
		}
		$this->type = $type;
		$this->command = &$command;
		$this->bridge = new PanelBridge();
	}

	public function runQuery() {
		$t = $this->types[$this->type];
		$model = $this->bridge->getModel( $t['model'] );
		$results = null;
		foreach ( $t['properties'] as $field => $details ) {
			$criterion = $this->command->getOption( $details['long'] );
			if ( null !== $criterion ) {
				if ( "null" == $criterion ) {
					// value is null
					$method = "whereNull";
					$parameters = array(
						$field
					);
				} else if ( "!null" == $criterion ) {
					// value not null
					$method = "whereNotNull";
					$parameters = array(
						$field
					);
				} else if ( $details['numeric'] ) {
					// numeric property
					$operator = "=";
					foreach ( $this->numericOperators as $userOperator => $sqlOperator ) {
						if ( Utils::startsWith( $criterion, $userOperator ) ) {
							$operator = $sqlOperator;
							$criterion = substr( $criterion, strlen( $userOperator ) );
							break;
						}
					}
					$method = "where";
					$parameters = array(
						$field,
						$operator,
						$criterion
					);
					var_dump( $parameters );
				} else {
					// non-numeric property
					$method = "where";
					$parameters = array(
						$field,
						"=",
						$criterion
					);
				}
				if ( null === $results ) {
					// first criterion
					$results = call_user_func_array(
						array( $model, $method ),
						$parameters
					);
				} else {
					$results = call_user_func_array(
						array( $results, $method ),
						$parameters
					);
				}
			}
		}
		if ( null !== $results ) {
			return $results->get();
		} else {
			return false;
		}
	}

	public function attachOptions() {
		$t = $this->types[$this->type];
		foreach ( $t['properties'] as $field => $details ) {
			$fluent = &$this->command->addOption( $details['long'] );
			if ( !empty( $details['short'] ) ) {
				$fluent = &$fluent->aka( $details['short'] );
			}
			if ( !empty( $details['description'] ) ) {
				$fluent = &$fluent->describedAs( $details['description'] );
			}
		}
		return true;
	}

	public function getPanelBridge() {
		return $this->bridge;
	}
}
