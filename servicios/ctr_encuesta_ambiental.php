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

require_once('../clases/Encuesta_Ambiental.php');

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


				$Encuesta = new EncuestaAmbiental($encta_id );



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

    case 'buscar_datos_personales':



      if(!isset($post->rut) || !is_numeric($post->rut))

        throw new Exception("Rut inválido", 1);



      $Usuario = Usuario::LoginInvitado($post->rut);

      $Usuario->setDatosDesdeEncuestas();



      $resp['nombre']= $Usuario->getNombre();

      $resp['email']= $Usuario->getEmail();

      $resp['telefono']= $Usuario->getCelular();


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
		  $rut	=	$post->usuario->rut;
			$nombre = $post->usuario->nombre;
			$telefono = $post->usuario->telefono;
			$correo = $post->usuario->email;

			if( !is_numeric( $rut ) )
				throw new Exception('Debe ingresar el rut sin puntos ni digivo verificador');

			if( !is_numeric( $telefono ) )
				throw new Exception('Debe ingresar el telefono sin simbolos');

			if( !is_string( $nombre ) || empty( $nombre) )
				throw new Exception('Nombre vacio o invalido');

			if( empty( $correo ) )
				throw new Exception('El correo esta vacio');


			$Usuario->setRut( $rut );
			$Usuario->setNombre ($nombre );
			$Usuario->setCelular($telefono );
			$Usuario->setEmail( $correo );

			$ip	= null ;

			//$empresa	=	$post->usuario->empresa;
			
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				
					$ip = $_SERVER['HTTP_CLIENT_IP'];
			
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			
			} else {
					
					$ip = $_SERVER['REMOTE_ADDR'];
			
			}
			
			//throw new Exception('x__x');

      $Encuesta = new EncuestaAmbiental($encta_id);


			$body = "";

      foreach ($post->respuestas as $r ){

					$valorCerrado = isset( $r->valor) ? $r->valor : null ;

					$valorAbierto = isset( $r->valorAbierto) ? $r->valorAbierto : null;

					$Encuesta->responderPregunta($r->id, $valorCerrado, $valorAbierto);

					$body.="<strong>".$r->descripcion."</strong><br>".$valorAbierto."<br><br>";

			}


			if(  $Usuario->registrarEncuestaAmbiental( $Encuesta ) ){

					$correos = $Usuario->getListaCorreoEncuestaAmbiental();
				
						$tabla ="<table style='width:700px; border:0; padding:5px 10px '>";
						$tabla .="<tr>";
						$tabla .="<td align='center'>";
						$tabla .="<img src='https://todoacero.cl/images/logo.jpg'/>";
						$tabla .="</td>";
						$tabla .="</tr>";
				
						$tabla .="<tr>";
						$tabla .="<td><p style='text-align:justify'>";
						$tabla .= "Estimado.<br><br> 
										<strong>".$nombre."</strong> ha enviado la siguiente propuesta: 
										<br><br>";
		
						$tabla .=$body;
						$tabla .="</p></td></tr>";
						$tabla .="</table>";

								
						$mail = new PHPMailer(TRUE);
						
						$subject = "Concurso de iniciativas medioambientales";

						$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
			
						foreach( (array)$correos as $c ){
									
									$mail->addAddress($c['correo'], '');
							
						}
						//$mail->addAddress($email, '');
						$mail->isHTML(true);
						$mail->Subject = $subject;
						$mail->Body = utf8_decode($tabla);

						$mail->send();


      }else{
					$error = $Usuario->getError()	;
				  $e = ( $error ) ? $error : "Ocurrio un error inesperado mientras resgitrabamos el formulario, si este error continua, sirvase contactar al área de Soporte";
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

