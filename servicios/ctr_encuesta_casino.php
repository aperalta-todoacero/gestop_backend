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

require_once('../clases/Encuesta_Casino.php');

require_once('../clases/Empresa.php');




$resp = array('error'=>false, 'preguntas'=> array() );


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

      throw new Exception('opción no identificada');

    }

  }






  switch($op){

    

		case 'obtener_encuesta':



				if(!isset($post->encta_id) || !is_numeric( $post->encta_id ) )

						throw new Exception("Id encuesta no recibida", 1);

				
				$encta_id = $post->encta_id;

				$resp['preguntas'] = array();



				$Empresa = new Empresa();


				$Encuesta = new EncuestaCasino( $encta_id );



				$resp['preguntas'] = array_map(function($pregunta){

						return array(

								'id'=>$pregunta->getId(),

								'tipo'=>$pregunta->getTipo(),

								'descripcion'=> ($pregunta->getDescripcion()),

								'alternativas' => $pregunta->getAlternativas(),

								'grupo' => $pregunta->getGrupo(),
						);

				},  $Encuesta->getPreguntas() );


				$resp['encta_id'] = $encta_id; 

    break;


     case 'guardar_encuesta':


      if( !isset( $post->encta_id ) || !is_numeric( $post->encta_id ) )

        throw new Exception("Id encuesta no recibida", 1);

      if(!isset($post->usuario->rut) && !isset($post->usuario->nombre))

        throw new Exception("Rut obligatorio", 1);


			$encta_id = $post->encta_id;

      $Usuario = Usuario::LoginInvitado( null /*$post->usuario->rut*/ );

      //$Usuario->setNombre($post->usuario->nombre);

      //$Usuario->setEmail($post->usuario->email);

			//$Usuario->setCelular($post->usuario->telefono);
			$sexo	= $post->usuario->sexo;
		  $edad	=	$post->usuario->edad;
			$casino = $post->usuario->casino;

			$ip	= null ;

			$empresa	=	$post->usuario->empresa;
			
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				
					$ip = $_SERVER['HTTP_CLIENT_IP'];
			
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			
			} else {
					
					$ip = $_SERVER['REMOTE_ADDR'];
			
			}
			
			//throw new Exception('x__x');

      $respuestas = array_map(function($r){

        return array(

          'preg_id' => $r->id,

          'alt_id' => isset( $r->valor ) ? $r->valor : null ,

          'valor' => null

        );

      }, $post->respuestas );


      $Encuesta = new EncuestaCasino($encta_id);


      foreach ($post->respuestas as $r ){

					$valorCerrado = isset( $r->valor) ? $r->valor : null ;

					$valorAbierto = isset( $r->valorAbierto) ? $r->valorAbierto : null;


					$Encuesta->responderPregunta($r->id, $valorCerrado, $valorAbierto);

			}


			if(  $Usuario->registrarEncuestaCasino($sexo, $edad, $ip, $empresa, $casino , $Encuesta )){



        //$resp['datos']['rut']=$Usuario->getRut();

        //$resp['datos']['nombre']=$Usuario->getNombre();



      }else{
					$error = $Usuario->getError()	;
				  $e = ( $error ) ? $error : "Ocurrio un error inesperado mientras resgitrabamos el TEST, si este error continua, sirvase contactar al área de Soporte";
						throw new Exception ($e , 1);

      }


    break;   


    default:

      throw new Exception('Estimado, esta opción aún no se encuentra implementada');

  }

  

}catch(Exception $e){


  $resp['error'] = $e->getMessage();

}



//vomito

echo json_encode($resp);

?>

