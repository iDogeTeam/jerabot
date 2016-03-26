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

use Telegram\Bot\Objects\User;

class Utils {
	public static function formatTelegramUser( User $user ) {
		$name = $user->getFirstName() . " " . $user->getLastName();
		$id = $user->getId();
		$username = $user->getUsername();
		if ( !empty( $username ) ) $username = "@$username; ";
		return "$name ($username$id)";
	}

	public static function formatBytes( $bytes, $precision = 2 ) { 
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' ); 

		$bytes = max( $bytes, 0 ); 
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log(1024)); 
		$pow = min( $pow, count( $units ) - 1 );

		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		// $bytes /= (1 << (10 * $pow)); 

		return round( $bytes, $precision ) . ' ' . $units[$pow]; 
	} 

	public static function startsWith( $haystack, $needle ) {
		return $needle === "" || strrpos( $haystack, $needle, -strlen( $haystack ) ) !== false;
	}
}
