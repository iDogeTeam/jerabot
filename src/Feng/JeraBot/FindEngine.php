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

use Feng\JeraBot\PanelBridge;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option as GetoptOption;

class FindEngine {
	protected $types = array(
		"user" => array(
			"model" => "User",
			"properties" => array(
				"id" => array(
					"long" => "id",
					"short" => null,
					"description" => "ss-panel ID",
				),
				"port" => array(
					"long" => "port",
					"short" => null,
					"description" => "端口号",
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
					"long" => "is-admin",
					"short" => null,
					"description" => "是否管理员",
				),
				"ac_enable" => array(
					"long" => "ac-enable",
					"short" => null,
					"description" => "是否开通了 AnyConnect",
				),
			),
		),
	);

	protected $extraOptions = array();

	public function __construct() {
	}

	public function runQuery( $type, $arguments ) {
		$options = $this->buildOptions( $type );
		$bridge = new PanelBridge();
		$t = $this->types[$type];
		$model = $bridge->getModel( $t['model'] );
		$results = null;
		$getopt = new GetOpt( $options );
		$getopt->parse( $arguments );
		foreach ( $t['properties'] as $field => $details ) {
			$criterion = $getopt->getOption( $details['long'] );
			if ( null !== $criterion ) {
				if ( null === $results ) {
					// first criterion
					$results = call_user_func(
						array( $model, "where" ),
						$field,
						"=",
						$criterion
					);
				} else {
					$results = $results->where(
						$field,
						"=",
						$criterion
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

	public function addOptions( $options ) {
		$this->extraOptions = array_merge( $this->extraOptions, $options );
	}

	public function buildOptions( $type ) {
		if ( !isset( $this->types[$type] ) ) {
			throw new \InvalidArgumentException( "No such type: $type" );
		}
		$t = $this->types[$type];
		$return = array();
		foreach ( $t['properties'] as $field => $details ) {
			$option = new GetoptOption(
				$details['short'],
				$details['long'],
				Getopt::REQUIRED_ARGUMENT
			);
			if ( !empty( $details['description'] ) ) {
				$option->setDescription( $details['description'] );
			}
			$return[] = $option;
		}
		$return = array_merge( $return, $this->extraOptions );
		return $return;
	}
}
