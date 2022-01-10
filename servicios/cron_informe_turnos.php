<?php
//header('Access-Control-Allow-Origin: *');

//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

//header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');


use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;

require 'Mailer/src/Exception.php';

require 'Mailer/src/PHPMailer.php';

require 'Mailer/src/SMTP.php';


require_once('../clases/Usuario.php');

$fecha = isset($argv[1])? $argv[1]:null;


$Usuario = Usuario::LoginInvitado( 15924693 );

$correos = $Usuario->getListaCorreoProgramacionTurnoCron();

if(empty($correos))
		die('no existen correos configurados');


if( ! ( $pdf = $Usuario->getInformeTurnosPDF($fecha) ) )
		die('nada que enviar');


$body ="Estimado.<br><br> Se adjunta informe de transporte de personal.
										<br><br>Saludos cordiales.";
		
$tabla ="<table style='width:700px; border:0; padding:5px 10px '>";

$tabla .="<tr>";

$tabla .="<td align='center'>";

$tabla .="<img src='https://todoacero.cl/images/logo.jpg'/>";

$tabla .="</td>";

$tabla .="</tr>";
				

$tabla .="<tr>";

$tabla .="<td><p>";

$tabla .=$body;

$tabla .="</p></td></tr>";

$tabla .="</table>";
								
$mail = new PHPMailer(TRUE);
						
$subject = "Informe de turnos";

$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');

/*foreach( (array)$correos as $c ){

		$mail->addAddress($c['correo'], '');

}*/

$mail->addAddress('jguasch@todoacero.cl');

$mail->isHTML(true);

$mail->Subject = $subject;

$mail->Body = utf8_decode($tabla);

$archivo = 'informe transporte-personal.pdf';

$pdf_str = $pdf->Output($archivo, 'S');

$mail->AddStringAttachment( $pdf_str , $archivo );				

$mail->send();

//$pdf->Output('test.pdf', 'I');


echo "enviado";

?>
