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

require_once('../clases/Encuesta.php');

require_once('../clases/Empresa.php');

//require_once('../clases/PasaporteCovid19.php');



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


				$Encuesta = new Encuesta( $encta_id );



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

     case 'guardar_encuesta_disc':


      if( !isset( $post->encta_id ) || !is_numeric( $post->encta_id ) )

        throw new Exception("Id encuesta no recibida", 1);

      if(!isset($post->usuario->rut) && !isset($post->usuario->nombre))

        throw new Exception("Rut obligatorio", 1);


			$encta_id = $post->encta_id;

      $Usuario = Usuario::LoginInvitado( $post->usuario->rut );

      $Usuario->setNombre($post->usuario->nombre);

      $Usuario->setEmail($post->usuario->email);

      $Usuario->setCelular($post->usuario->telefono);


      $respuestas = array_map(function($r){

        return array(

          'preg_id' => $r->id,

          'alt_id' => isset( $r->valor ) ? $r->valor : null ,

          'valor' => null

        );

      }, $post->respuestas );


      $Encuesta = new Encuesta($encta_id);

			//throw new Exception($encta_id."-".$Encuesta->getId(), 1);

      foreach ($post->respuestas as $r ){

					$valorCerrado = isset( $r->valor) ? $r->valor : null ;

					$valorAbierto = isset( $r->valorAbierto) ? $r->valorAbierto : null;


					if( empty($valorCerrado) && empty($valorAbierto) )

								throw new Exception("Existen preguntas sin responder", 1);


					$Encuesta->responderPregunta($r->id, $valorCerrado, $valorAbierto);

			}


			if(  $Usuario->registrarEncuestaDISC( $Encuesta )){

/*
        $fecha= date('d-m-Y');

        $pdf = new PasaporteCovid19($Usuario->getRut(), $Usuario->getNombre() ,$Usuario->getFaena()->getNombre(),  $fecha ,$Usuario->getEstadoPasaporte());

        $pdfdoc = $pdf->Output('pasaportecovid19.pdf', 'S');



        $subject ="Pasaporte Covid-19";

        $body ="Este pasaporte debe ser presentado en garita al momento de ingresar a la faena correspondiente.";

        $mail = new PHPMailer(TRUE);

        $mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');

        // $mail->addAddress("jguasch@todoacero.cl", 'Julio Guasch');

        $mail->addAddress($post->usuario->email, $post->usuario->nombre);

        $mail->isHTML(true);



        $mail->Subject = $subject;

        $mail->Body = utf8_decode($body);

        //$mail->addBCC("jguasch@todoacero.cl");

        $mail->AddStringAttachment($pdfdoc, 'pasaportecovid.pdf', 'base64', 'application/pdf');

        $mail->send();
 */


        $resp['datos']['rut']=$Usuario->getRut();

        $resp['datos']['nombre']=$Usuario->getNombre();



      }else{

        throw new Exception("Error al registrar encuesta : ".$Usuario->getError() , 1);

      }
 


    break;   

    default:

      throw new Exception('CASE default');

  }

  

}catch(Exception $e){


  $resp['error'] = $e->getMessage();

}



//vomito

echo json_encode($resp);

?>

