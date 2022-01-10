<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('content-type: application/json; charset=utf-8');
ini_set('display_errors', 'On');

error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'Mailer/src/Exception.php';
require 'Mailer/src/PHPMailer.php';
require 'Mailer/src/SMTP.php';
require_once('../clases/Usuario.php');

$resp = array('error'=>false );

try{

  $post = '';
  $op = '';

  if( isset($_POST['opcion']) ){
    $op = $_POST['opcion'];
    $post = $_POST;
  }else{
    $JSONpost = file_get_contents("php://input");

    $post = json_decode($JSONpost);

    if( isset( $post->params ) ) {
        $op = (isset($post->params->opcion)) ? $post->params->opcion : '';
        $post = $post->params;          
    }else {
      throw new Exception('opci車n no identificada');
    }
  }


  switch($op){
    
    case 'obtener_arbol':
      
      if( !isset($post->token) || empty( $post->token ) )
          throw new Exception("Token de acceso no encontrado", 1);

      if( ! $Usuario = Usuario::LoginToken( $post->token ) )
        throw new Exception("Su tiempo de acceso ha expirado", 2);

      $resp['arbol']= $Usuario->getArbolModulos();
      $resp['expira'] = $Usuario->getExpiracion() - time() ;//segundos
      $resp['usr'] = $Usuario->getUsuario();

			
	    $Usuario->setDatos();

      $resp['nombre'] = $Usuario->getNombre();
      $resp['nombre_completo'] = $Usuario->getNombreCompleto();
        
    break;

		case 'cambiar_clave':
      
      if( !isset($post->token) || empty( $post->token ) )
          throw new Exception("Token de acceso no encontrado", 1);

      if( ! $Usuario = Usuario::LoginToken( $post->token ) )
        throw new Exception("Su tiempo de acceso ha expirado", 2);
			
			if( !isset($post->clave_antigua) || empty( $post->clave_nueva ) || !isset($post->clave_nueva) || empty($post->clave_nueva) )
          throw new Exception("Faltan datos para realizar la actualizaci車n de clave", 1);

			$clave_antigua = $post->clave_antigua;
			
			$clave_nueva = $post->clave_nueva;

			if(! $r = $Usuario->cambiarClave( $clave_antigua, $clave_nueva ) )
			{
					$error = $Usuario->getError();
					$error = ( !empty($error) ) ? $error : 'Ocurrio un error al intentar actualizar su clave de acceso';

					throw new Exception($error , 1);
			}
				
			$resp['mensaje']	= 'Su clave ha sido actualizada';

    break;
		


		case 'recuperar_clave':
      
				


      if( ! $Usuario = Usuario::LoginInvitado( 0 ) )
        throw new Exception("Su tiempo de acceso ha expirado", 2);
			
			if( !isset($post->email) || empty( $post->email )  )
          throw new Exception("Es necesario su correo electr車nico para restaurar su clave de acceso", 1);


			$email = $post->email.'@todoacero.cl';
		  
			//$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
			$permitted_chars = '0123456789';
//		  $clave_temporal = substr(str_shuffle($permitted_chars), 0, 10);
			$clave_temporal = random_int(1,99999999);
			
			//throw new Exception($clave_temporal, 1);

			if(!$r = $Usuario->cambiarClavePorCorreo( $email , $clave_temporal ) )
			{
					$error = $Usuario->getError();
					$error = ( !empty($error) ) ? $error : 'Ocurrio un error al intentar actualizar su clave de acceso';

					throw new Exception($error , 1);
			}
			
			$fecha= date('d-m-Y');
			$subject ="Cambio de clave de acceso";
			$body ="Estimado usuario, su clave provisoria es <strong>".$clave_temporal."</strong>. Le recordamos actualizarla por una clave personal que solo usted conosca, y que no este compuesta por datos personales (fechas de nacimientos, nombres, etc.).";
			
			$mail = new PHPMailer(TRUE);
			
			$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
			$mail->addAddress($email, '');
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = utf8_decode($body);
			$mail->send();

			$resp['mensaje']	= 'La nueva clave ha sido enviada a su correo corporativo.';

    break;

    default:
      throw new Exception('Esta opci車n a迆n no est芍 implementada');
  }
  
}catch(Exception $e){

  sleep(3);
  $resp['error'] = $e->getMessage();
}

//vomito
echo json_encode($resp);
?>
