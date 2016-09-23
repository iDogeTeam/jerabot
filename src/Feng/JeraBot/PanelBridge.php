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

// ss-panel dependencies
use App\Models\User;
use App\Models\Node;
use App\Models\InviteCode;
use App\Utils\Tools;
use App\Services\Analytics;
use App\Utils\Hash;
use App\Models\GiftCode;
use App\Models\TgLogin;

class PanelBridge {
	public function __construct() {
		// Nothing for now :P
	}

	public function getAnalytics() {
		return new Analytics();
	}

	public function getUsersByTelegramId( $id ) {
		$users = User::where( "telegram_id", "=", $id );
		if ( $users->count() == 0) return false;
		else return $users;
	}

	public function getUserByTelegramToken( $id ) {
		$users = User::where( "telegram_token", "=", $id );
		if ( 1 == $users->count() ) {
			return $users->get()->first();
		} else {
			return false;
		}
	}

    public function gbToBytes( $gb ){
		return Tools::toGB( $gb );
	}
	public function transTraffic( $traffic ){
		return Tools::flowAutoShow( $traffic );
	}

	public function mbToBytes( $mb ) {
		return Tools::toMB( $mb ); // What a misleading name :P
	}

	public function getNodes(){
		return Node::whereRaw('type =  1 or type = 2 or type = 3')->orderBy('sort')->get();   //all nodes
	}

	public function genRandomChar($num){
		return Tools::genRandomChar($num);
	}
	
	public function getAvailablePort(){
		return Tools::getAvailablePort();
	}
	
	public function getHash($str){
		return Hash::passwordHash($str);
	}
	
	public function createUser(){
		return new User();
	}

	public function AnyConnectUser($name){
		$ac_result = User::where( "ac_user_name", "=", $name);
		if ( $ac_result->count() == 0) return true; //唯一 = true 
		else return false;
	}
	
	public function getModel( $type ) {
		switch ( $type ) {
			case "User": return "App\\Models\\User";
			case "Node": return "App\\Models\\Node";
			default: return false;
		}
	}
	
	public function getGiftCode( $code ){
		return GiftCode::where('code', $code)->first();
	}

    public function verifyTgCode( $code ){
        return TgLogin::where('safecode', $code)->first();
    }

    public function getAll(){
        return User::all();
    }
}

