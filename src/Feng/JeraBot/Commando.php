<?php
/*
Copyright (c) 2012 Nate Good <me@nategood.com>, Zhaofeng Li <hello@zhaofeng.li>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Feng\JeraBot;

class Commando extends \Commando\Command {
	public function error( \Exception $e ) {
		throw $e;
	}

	public function getHelp() {
		$options = $this->getOptions();
		$keys = array_keys( $options );
		$seen = array();
		natsort( $keys );
		$return = "";
		foreach ( $keys as $key ) {
			$option = $this->getOption( $key );
			if ( in_array( $option, $seen ) ) continue;
			$isNamed = ( $option->getType() && $option::TYPE_NAMED );
			if ( $isNamed ) {
				$short = ( 1 === mb_strlen( $option->getName(), "UTF-8" ) );
				$aliases = $option->getAliases();
				$return .= "\r\n*";
				$return .= $short ? "-" : "--";
				$return .= $option->getName();
				if ( !empty( $aliases ) ) {
					foreach ( $aliases as $alias ) {
						$ashort = ( 1 === mb_strlen( $alias, "UTF-8" ) );
						$return .= $ashort ? "/-" : "/--";
						$return .= $alias;
					}
				}
				$return .= "*";
				$description = $option->getDescription();
				if ( !empty( $description ) ) {
					$return .= " $description";
				}
			}
			$seen[] = $option;
		}
		return $return;
	}
}
