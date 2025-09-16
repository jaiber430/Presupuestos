<?php
namespace Klassroom\helpers;

class PasswordHelper{
	public static function encryptPass(string $password): string{
		$password= trim($password);
        $password= password_hash($password, PASSWORD_BCRYPT, ["cost"=>11]);
		
        return $password;
	}
}