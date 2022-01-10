<?php

interface iLogin{

	public static function LoginPass($usr, $pass);

	public static function LoginToken($token);
}
?>