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

require_once('../clases/RRHH.php');

require_once('../clases/Empresa.php');

require_once('../clases/Empleado.php');

$resp = array('error'=>false );


try{


		
		$post = '';

		$op = '';


		if( isset($_POST['opcion']) ){

				$op = $_POST['opcion'];

				$post = (object)$_POST;

		}else{

				$JSONpost = file_get_contents("php://input");


				$post = json_decode($JSONpost);


				if( isset( $post->params ) ) {

						$op = (isset($post->params->opcion)) ? $post->params->opcion : '';

						$post = $post->params;          

				}else {

						throw new Exception('opcion no identificada');

				}

		}

		


		switch($op){


		
				case 'cargar_excel':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $Usuario = Usuario::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);
				


						require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

				
						$inputFile = $_FILES['archivo']['tmp_name'];

				
						//$archivo = '/home/admin/tmp/turnos'.time().'.xlsx';
						$carpeta = '/home/admin/tmp/';
						$nombre_archivo = $_FILES['archivo']['name'];
						$archivo = '/home/admin/tmp/'.$nombre_archivo;

						$error = false;

						$fecha_cab = $post->fecha;

						$turno_cab = $post->turno;


						try{
						
								if(!move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo) )
								
										throw new Exception("Error al guardar archivo", 1);
	
								
								$inputFileType = PHPExcel_IOFactory::identify($archivo);

								$objReader = PHPExcel_IOFactory::createReader($inputFileType);

								$objReader->setLoadAllSheets();

								$objPHPExcel = $objReader->load($archivo);

								$sheet = $objPHPExcel->getSheet(0);

								$highestRow = $sheet->getHighestDataRow();

								$highestColumn = $sheet->getHighestColumn();


								$lista = array();

								//throw new Exception('filas'.$highestRow);

								for ($row = 3; $row <= $highestRow; $row++){

										$rut = $sheet->getCell('B'.$row)->getValue();
										$rut = str_replace( array('.',',', ' '), '', $rut );
										$nombre = $sheet->getCell('C'.$row)->getValue();
										$cargo = $sheet->getCell('D'.$row)->getValue();
										$turno = $sheet->getCell('E'.$row)->getValue();
										$telefono = $sheet->getCell('F'.$row)->getValue();
										$comuna = $sheet->getCell('G'.$row)->getValue();
										$observacion = $sheet->getCell('H'.$row)->getValue();
					

										if( !empty($rut) && preg_match('/^(\d+\.?)\-(\d|k|K)$/', $rut, $r) ) {

												$mantiza = $r[1];
												$dv = $r[2];

												$conflictos = $Usuario->getProgramacionTurnoRutFecha( $mantiza , $turno_cab, $fecha_cab );


												$item = array(
														'rut' => $mantiza,
														'dv' => $dv,
														'nombre_completo' => $nombre,
														'cargo' => $cargo,
														'turno' => $turno,
														'telefono' => $telefono,
														'comuna' => $comuna,
														'observacion' => $observacion,
														'conflictos' => $conflictos,
														'planilla' => 1	
												);


												array_push( $lista , $item);
										
										}
							

								}

								$resp['lista'] = $lista;

					
						}catch(Exception $e){

								$error = $e->getMessage();
						
						}finally{
	
								//if(file_exists($archivo))
								//		unlink($archivo);
								$resp['archivo'] = $nombre_archivo;

						}


						if( $error )
								throw new Exception( $error );




				
				break;
	
			
				case 'registrar_turnos':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);

						if( empty( $post->trabajadores ) )
								
								throw new Exception("El listado esta vacio", 2);

						$turno = $post->turno;

						$fecha = $post->fecha;

						$archivo = $post->archivo;

						$archivo_2 = $turno.'-'.$fecha.'-'.$archivo;
						
						//if( !file_exists('/home/admin/tmp/'.$archivo ) )
							//	$resp['error_existe'] = "no existe";

						if(!copy('/home/admin/tmp/'.$archivo , '/home/admin/public_ftp/turnos/'.$archivo_2 ) )
								$resp['error_archivo'] = "error al respaldar el excel";

						unlink('/home/admin/tmp/'.$archivo);

						if( empty($turno) )
								throw new Exception("Debe asignar el turno", 2);

						if( empty($fecha) )
								throw new Exception("Debe asignar la fecha", 2);

						$empleados = array();

						foreach( (array)$post->trabajadores as $t ){

								$e = new Empleado( $t->rut, $t->nombre_completo , $t->planilla );
								$e->setDV( $t->dv );
								$e->setTelefono( $t->telefono);
								$e->setTurno( $t->turno );
								$e->setCargo( $t->cargo );
								$e->setComuna( $t->comuna );
								$e->setEstadoDescripcion( (isset($t->observacion) ? $t->observacion : null ) );

								array_push( $empleados, $e );

						}


						if( empty($empleados) )
								throw new Exception( 'No fue posible completar los registros');

						if( ! $cantidad = $RRHH->registrarProgramacionTurno( $turno, $fecha, $empleados ) )
								throw new Exception( $RRHH->getError() );

						//$resp['mensaje'] = $cantidad." fueron registrados";
						$resp['mensaje'] = "Los registros fueron guardados";


						$correos = $RRHH->getListaCorreoProgramacionTurnoCreacion();

						if( !empty($correos) ){
								
								$fecha = date_format( date_create($fecha) , 'd-m-Y');

								$body ="Estimado.<br><br> 
										Se ha registrado el turno <strong>".$turno."</strong> para el d&iacute;a <strong>".$fecha."</strong> el cual esta listo para su validaci&oacute;n. 
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
						
								$subject = "Registro de turno";

								$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
			
								foreach( (array)$correos as $c ){
									
										$mail->addAddress($c['correo'], '');
							
								}
								//$mail->addAddress($email, '');
								$mail->isHTML(true);
								$mail->Subject = $subject;
								$mail->Body = utf8_decode($tabla);

								$mail->send();
						}


				break;
				
	
				case 'turnos_por_validar':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						$resp['turnos'] = $RRHH->getProgramacionTurnosSinValidar();



				break;
		
				case 'detalle_turno':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						if( !isset( $post->pt_id ) || !is_numeric( $post->pt_id ) )

								throw new Exception("Identificador no recibido");


						$pt_id = $post->pt_id;

						$resp['empleados'] = $RRHH->getProgramacionTurnoDetalle( $pt_id);



				break;
			
				case 'guardar_validacion':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						if( !isset( $post->empleados ) || !count( $post->empleados ) )

								throw new Exception("Lista de trabajadores vacia");


						$empleados = $post->empleados;

						$pt_id = $post->turno;

						$errores = array();

						foreach( $empleados as $e ){

										if( false === $RRHH->actualizarProgramacionTurnoDetalle(
												$e->ptd_id , 
												$e->contrato, 
												$e->examen, 
												$e->encuesta, 
												$e->fecha_venc_induccion, 
												$e->fecha_venc_altura_geo )
										){
												array_push( $errores, $e->nombre );
										}
								
						}

						if( count($errores) ){

								$resp['errores'] = $errores;

								throw new Exception("Error al actualizar los registros");
						}


						$resp['empleados'] = $RRHH->getProgramacionTurnoDetalle( $pt_id);


				break;
		
				case 'turnos_validados':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						$resp['turnos'] = $RRHH->getProgramacionTurnosValidados();



				break;
		
		
				case 'patente_pasajeros':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						$resp['transportes'] = $RRHH->getProgramacionTurnosTransportes();



				break;
		
			
				case 'patente_pasajeros_detalle':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						if( !isset( $post->ptt_id ) || !is_numeric( $post->ptt_id ) )

								throw new Exception("Identificador no recibido");


						if( !isset( $post->patente ) || !is_string( $post->patente ) )

								throw new Exception("Patente no recibida");



						//$pt_id = $post->pt_id;
						$ptt_id = $post->ptt_id;
						
						$patente = $post->patente;

						$resp['empleados'] = $RRHH->getProgramacionTurnoTransporteDetalle( $ptt_id, $patente );



				break;
		
				
				case 'modificacion_turno_ini':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						

						$resp['turnos'] = $RRHH->getProgramacionTurnosValidados( $por_vencer = true );
						
						$resp['estados'] = $RRHH->getProgramacionTurnoEstados();


				break;
		
			
				case 'modificar_estado_turno':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						if( !isset( $post->ptd_id ) || !is_numeric( $post->ptd_id ) || 
								!isset( $post->pte_id ) || !is_numeric( $post->pte_id ) ||
								!isset( $post->pt_id ) || !is_numeric( $post->pt_id ) )

								throw new Exception("Identificador no recibido");


						$pt_id = $post->pt_id;
						
						$ptd_id = $post->ptd_id;

						$pte_id = $post->pte_id;
						
						$fecha = $post->fecha_subida;
						
						$motivo = $post->estado_descripcion;
						
						$turno = $post->turno;
						
						$nombre = $post->nombre;

								//throw new Exception("------------------------------");
						if( false === $RRHH->actualizarProgramacionTurnoDetalleEstado( $ptd_id, $pte_id ) )
								
								throw new Exception("No fue posible actualizar el registro");
						
						$RRHH->setDatos();
						$nombre_completo = $RRHH->getNombreCompleto();
						$nombre_completo = ucwords(strtolower( $nombre_completo )) ;

						$correos = $RRHH->getListaCorreoProgramacionTurno();


						if( $pte_id != 1 ){
								
								$body ="Estimado.<br><br> 
										Se ha registrado a <strong>".$nombre."</strong> como imposibilitado para asistir al turno 
										<strong>".$turno."</strong> para el <strong>".$fecha."</strong>, por motivo de <strong>".$motivo."</strong>.
										<br><br>Saludos cordiales.
										<br><br><strong>".$nombre_completo."</strong>";
						}else{
							
								$body ="Estimado.<br><br> 
										Se ha registrado a <strong>".$nombre."</strong> como habilitado para asistir al turno 
										<strong>".$turno."</strong> para el <strong>".$fecha."</strong>.
										<br><br>Saludos cordiales.
										<br><br><strong>".$nombre_completo."</strong>";

						}
		
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
						
						$subject = "Asistencia de personal";

						$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
			
						foreach( (array)$correos as $c ){
									
									$mail->addAddress($c['correo'], '');
							
						}
						//$mail->addAddress($email, '');
						$mail->isHTML(true);
						$mail->Subject = $subject;
						$mail->Body = utf8_decode($tabla);

						$mail->send();
						

						$resp['empleados'] = $RRHH->getProgramacionTurnoDetalle( $pt_id);



				break;
			
				
				case 'eliminar_turno_detalle':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);


						if( !isset( $post->ptd_id ) || !is_numeric( $post->ptd_id ) || 
								 !isset( $post->pt_id ) || !is_numeric( $post->pt_id ) 
						)

								throw new Exception("Identificador no recibido");


						
						$ptd_id = $post->ptd_id;

						$pt_id = $post->pt_id;

						if( false === $RRHH->eliminarProgramacionTurnoDetalle( $ptd_id ) )
								
								throw new Exception("No fue posible eliminar el registro");
						
					

						$resp['mensaje'] = "El registro fue eliminado";
						//$resp['empleados'] = $RRHH->getProgramacionTurnoDetalle( $pt_id);



				break;
	
	
				case 'comprobar_conflictos':
					
				
						if( !isset($post->token) || empty( $post->token ) )

								throw new Exception("Token de acceso no encontrado", 1);



						if( ! $RRHH = RRHH::LoginToken( $post->token ) )

								throw new Exception("Su tiempo de acceso ha expirado", 2);

						if( empty( $post->trabajadores ) )
								
								throw new Exception("El listado esta vacio", 2);

						if( !isset( $post->turno ) || empty( $post->turno ) || !isset($post->fecha) || empty($post->fecha) )

								throw new Exception("Fecha o turno no definido", 2);


						$turno = $post->turno;

						$fecha = $post->fecha;

						$trabajadores = $post->trabajadores;

						$nuevo_array = array();

						foreach( (array)$trabajadores as $rut ){

								array_push(
										$nuevo_array,
										array( 
												'rut'=> $rut,
												'conflictos' => $RRHH->getProgramacionTurnoRutFecha($rut, $turno, $fecha)
										)
								);
						}

						$resp['trabajadores'] = $nuevo_array;


				break;
				




		default:

      throw new Exception('Estimado usuario, esta opcion aun no es implementad');

  }

  

}catch(Exception $e){


  $resp['error'] = $e->getMessage();

}



//vomito

echo json_encode($resp);

?>
