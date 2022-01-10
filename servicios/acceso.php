<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('content-type: application/json; charset=utf-8');
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once('../clases/Usuario.php');
//require_once('../clases/SSOMA.php');

require_once 'JWT/JWT.php';

use \Firebase\JWT\JWT;

$resp = array('error'=>false, 'jwt'=> '' );

try{
  	$post = '';
  	$op = '';
  
		$JSONpost = file_get_contents("php://input");
    $post = json_decode($JSONpost);

    if( isset( $post->params ) ) {        
        $post = $post->params;
    }else {
      throw new Exception('Sin parametros');
    }

    if( !isset($post->usr) || !isset($post->pass) )
    	throw new Exception("Error Processing Request", 1);
    	
    if( !preg_match('/^[a-zA-Z0-9\-_]{3,20}$/', $post->usr ) || !preg_match('/^[a-zA-Z0-9\-_]{3,20}$/', $post->pass ) )
    	throw new Exception("Solo puede contener números, letras o guiones", 1);
    	
    $usr = $post->usr;
    
    $pass = $post->pass;
	
	$Usuario = Usuario::LoginPass( $usr , $pass ) ;

	if( ! $Usuario instanceof Usuario )
		throw new Exception("Usuario o clave incorrecta", 1);		

	//$rut = $Usuario->getRut();

	$key = date('dmY');//debe invalidarse a final del dia
	
	$time = time();

	$exp = $time + 60*60*3;
	//$exp = $time + 60*2;


	$payload = array(
	    "iss" => "gestopweb",
	    "sub" => $usr,
	    "rut" => $Usuario->getRut(),
	    "iat" => $time, //emitido
	    "nbf" => $time, //no usar antes de
	    "exp" => $exp //no usar antes de
	);


	$jwt = JWT::encode($payload, $key);

	if( strlen($jwt) < 10 )
		throw new Exception("Error al generar identificador de acceso", 1);

	$resp['jwt'] = $jwt;

	//$resp['expira'] = ($exp -10*60) *1000;//tiempo de expiracion - 10 minutos
	//$resp['usr'] = $usr;//tiempo de expiracion - 10 minutos
	
	$resp['rtoken'] = $Usuario->registrarToken( $jwt );

	//$decoded = JWT::decode($jwt, $key, array('HS256'));

	//print_r($jwt);

	//print_r($decoded);

	/*
	 NOTE: This will now be an object instead of an associative array. To get
	 an associative array, you will need to cast it as such:
	 $decoded_array = (array) $decoded;
	*/


	/**
	 * You can add a leeway to account for when there is a clock skew times between
	 * the signing and verifying servers. It is recommended that this leeway should
	 * not be bigger than a few minutes.
	 *
	 * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
	JWT::$leeway = 60; // $leeway in seconds
	$decoded = JWT::decode($jwt, $key, array('HS256'));
	 */

  
  }catch(Exception $e){
  	$resp['error'] = $e->getMessage();
  }

  echo json_encode( $resp );

?>
