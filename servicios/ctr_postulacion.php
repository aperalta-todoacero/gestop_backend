<?php
/*
ini_set("max_input_time",'24000');
ini_set("max_execution_time",'24000');
ini_set('upload_max_filesize', '24M');
ini_set('post_max_size', '24M');
ini_set('memory_limit', '20M');
ini_set('client_max_body_size', '24M');
*/

header('Access-Control-Allow-Origin: *');

header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

header('content-type: application/json; charset=utf-8');

ini_set('display_errors', 'On');

error_reporting(E_ALL);

require_once 'JWT/JWT.php';
require_once 'JWT/SignatureInvalidException.php';

use \Firebase\JWT\JWT;

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;



require 'Mailer/src/Exception.php';

require 'Mailer/src/PHPMailer.php';

require 'Mailer/src/SMTP.php';



require_once('../clases/Empresa.php');

require_once('../clases/Rec_Documento.php');

require_once('../clases/Rec_Postulante.php');

require_once('../clases/Rec_Postulacion.php');

require_once('../clases/Rec_Oferta.php');

require_once('../clases/Rec_Perfil.php');

require_once('../clases/Rec_Competencia_Evaluada.php');


$resp = array('error'=>false, 'jwt'=> '' );

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

	
	case 'login':

			if( !isset( $post->rut ) || empty($post->rut) || !is_numeric( $post->rut ) )
					throw new Exception('El rut no es válido',1);


			if( !isset( $post->clave ) || empty( $post->clave) )
					throw new Exception('La clave esta vacia',1);


			$rut = $post->rut;

			$clave = $post->clave;

			$Postulante = new Rec_Postulante();

			$Postulante->setRut( $rut );

			$r = $Postulante->login( $clave );


			if( $r === true ){
					$resp['token'] = $Postulante->getToken();
				  $resp['mensaje']	= 'Acceso al sistema';
				  $resp['id']	= $Postulante->getId();
			}else{
					$resp['existe'] = ( $r === 0 ) ;
					throw new Exception( $Postulante->getError() ,1);
			}

			break;

	case 'crear_acceso':

			if( !isset( $post->rut ) || empty($post->rut) || !is_numeric( $post->rut ) )
					throw new Exception('El rut no es válido',1);

			//if( !isset( $post->dv) || strlen( $post->dv )!==1 )
				//	throw new Exception('El digito verificador no es válido',1);

			if( !isset( $post->clave ) || empty( $post->clave) )
					throw new Exception('La clave esta vacia',1);

			$rut = $post->rut;

			//$dv = $post->dv;
				
			$dv = 'f';

			$clave = $post->clave;

			$Postulante = new Rec_Postulante();

			$Postulante->setRut( $rut );

			$Postulante->setDV( $dv );


			if( $Postulante->crearClave( $clave ) ){
					$resp['token'] = $Postulante->getToken();
				  $resp['mensaje']	= 'La cuenta para acceder al sistema de postulación ha sido creada';
			}else{
					throw new Exception( $Postulante->getError() ,1);
			}

			break;

		case 'recuperar_clave':


      if( ! $Postulante = new Rec_Postulante )
        throw new Exception("No fue posible crear el objeto", 2);
			
			if( !isset($post->rut) || !is_numeric( $post->rut )  )
          throw new Exception("Debe ingresar su rut sin digito verificador para restaurar su clave de acceso", 1);


			$rut = $post->rut;
		  
			$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
			//$permitted_chars = '0123456789';
		  $clave_temporal = substr(str_shuffle($permitted_chars), 0, 10);
			//$clave_temporal = random_int(1,99999999);
			
			//throw new Exception($clave_temporal, 1);

			if(!$correo = $Postulante->cambiarClavePorRut( $rut , $clave_temporal ) )
			{
					$error = $Usuario->getError();
					$error = ( !empty($error) ) ? $error : 'Ocurrio un error al intentar actualizar su clave de acceso';

					throw new Exception($error , 1);
			}
			
			$fecha= date('d-m-Y');
			$subject =utf8_decode( "Recuperación de clave de acceso" );
			$body ="Estimado usuario, su nueva clave de acceso es <strong>".$clave_temporal."</strong>";
			
			$mail = new PHPMailer(TRUE);
			
			$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
			$mail->addAddress($correo, '');
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = utf8_decode($body);
			$mail->send();

			$resp['mensaje']	= 'La nueva clave ha sido enviada a su correo.';
			$resp['correo'] = $correo;

    break;


		case 'obtener_datos_postulante':
				
			if( !isset( $post->rut ) || empty($post->rut) || !is_numeric( $post->rut ) )
					throw new Exception('El rut no es válido',1);
			
			if( !isset( $post->rocp_id ) || empty($post->rocp_id) || !is_numeric( $post->rocp_id ) )
					throw new Exception('Identificador no válido',1);


			$rut = $post->rut;

			$rocp_id = $post->rocp_id;

			$Postulante = new Rec_Postulante();

			$Postulante->setRut( $rut );
			
			$Postulante->cargarId();


			$resp['lista_nacionalidad'] = $Postulante->getListaNacionalidad();
			$resp['lista_nivel_educacional'] = $Postulante->getListaNivelEducacional();
			$resp['lista_estado_civil'] = $Postulante->getListaEstadoCivil();
			$resp['lista_afp'] = $Postulante->getListaAFP();
			$resp['lista_salud'] = $Postulante->getListaSalud();

			$bancos = $Postulante->getListaBanco();//array banco y cuentas

			$resp['lista_banco'] = $bancos['bancos'];
			$resp['lista_cuentas'] = $bancos['tipo_cuentas'];

		
			$lista_docs = $Postulante->getListaDocumentosRequeridos( $rocp_id );
			//$resp['ddd']= $lista_docs;

			$resp['lista_documentos_requeridos'] = array_map(
					function( $l) use ( $rut){
							
							$id_doc = $l->getId();
							$doc_temp = glob("/home/admin/tmp/".$rut."/".$id_doc."*"); 
							$doc_final = glob("/home/admin/public_ftp/postulantes/".$rut."/".$id_doc."*"); 
							$existe_doc = (count($doc_temp) || count($doc_final) );

							$id_doc = is_numeric( $id_doc) ? $id_doc : $id_doc2 ;
							return array(
										'id'=> $l->getId() ,
										'nombre' => $l->getDescripcion(),
										'id_doc_postulacion' => $l->getIdDocPostulacion(),
										'existe' => $existe_doc
							);

					}, $lista_docs);

			$resp['lista_sexo'] = array(
					array('id' => 'M', 'nombre' => 'Masculino'),
					array('id' => 'F', 'nombre' => 'Femenino')
			);

						/*
			if( $Postulante->cargarId() ) {

					$id_1 = $Postulante->getId();

					if( ! $Postulante = Rec_Postulante::LoginToken( $post->token ) )
							throw new Exception( 'validar',1);
					else if( $id_1 != $Postulante->getId() )
							throw new Exception( 'validar',1);
			
			}
		  */	

		  $Postulante->setDatos( $rocp_id );
		
			$resp['postulacion_id'] = $Postulante->getPostulacionId();

			$p = array(
						'rut' => $Postulante->getRut(),
						'dv' => $Postulante->getDV(),
						'nombre' => $Postulante->getNombre(),
						'apaterno' => $Postulante->getApaterno(),
						'amaterno' => $Postulante->getAmaterno(),
						'nacionalidad' => $Postulante->getNacionalidadId(),
						'fecha_nacimiento' => $Postulante->getFechaNacimiento(),
						'sexo' => $Postulante->getSexoId(),
						'estado_civil' => $Postulante->getEstadoCivilId(),
						'email' => $Postulante->getEmail(),
						'celular' => $Postulante->getTelCelular(),
						'telefono' => $Postulante->getTelFijo(),
						'nivel_educacional' => $Postulante->getNivelEducacionalId(),
						'profesion' => $Postulante->getProfesion(),
						'telefono_emergencia' => $Postulante->getTelContacto(),
						'nombre_emergencia' => $Postulante->getNombreContacto(),
						'afp' => $Postulante->getAfpId(),
						'salud'=> $Postulante->getSaludId(),
						'salud_uf' => $Postulante->getSaludUf(),
						'direccion' => $Postulante->getDireccion(),
						'ciudad'=> $Postulante->getCiudad(),
						'talla_pantalon' =>	$Postulante->getTallaPantalon(),
						'talla_calzado' =>$Postulante->getTallaCalzado(),
						'talla_camisa' =>$Postulante->getTallaCamisa(),
						'banco'=>	$Postulante->getBancoId(),
						'tipo_cuenta' =>	$Postulante->getTipoCuentaId(),
						'cuenta_bancaria' =>$Postulante->getCuentaBancaria()
						);

			
			$resp['datos_personales'] = $p;

			break;

		
		case 'guardar_datos_postulante':
				


				if( !isset( $post->paso ) || !is_numeric($post->paso) )
						throw new Exception('Paso o etapa indefinida',1);
				
				if( !isset( $post->rocp_id ) || !is_numeric($post->rocp_id) )
						throw new Exception('Identificador no válido',1);

				if( !isset( $post->datos_personales ) || empty( $post->datos_personales ) )
						throw new Exception('Datos personales incompletos');

				$paso = $post->paso;

				$rocp_id = $post->rocp_id;

				$persona = $post->datos_personales;


				if( !isset( $persona->rut ) || empty($persona->rut) || !is_numeric( $persona->rut ) )
						throw new Exception('El rut no es válido',1);

				//throw new Exception('r:'.isset($post->token).'/'.empty($post->token));

				if( isset( $post->token ) && !empty($post->token) ){

						if( ! $Postulante = Rec_Postulante::LoginToken( $post->token ) )
								throw new Exception( "token", 1);

				}else{
						
						$Postulante = new Rec_Postulante();
						$Postulante->setRut( $persona->rut );
						$Postulante->cargarId();
				}


				switch( intval($paso) ){

						case 1:
								
								$nombre= $persona->nombre;
								$apaterno = $persona->apaterno;
								$amaterno = $persona->amaterno;
								$email = $persona->email;
								$fecha_nacimiento = $persona->fecha_nacimiento;
								$nacionalidad = $persona->nacionalidad;
								$sexo = $persona->sexo;
								$estado_civil = $persona->estado_civil;
								$tel_celular = $persona->celular;
								$tel_fijo = $persona->telefono;
								$tel_contacto = $persona->telefono_emergencia;
								$nivel_educacional = $persona->nivel_educacional;
								$profesion = $persona->profesion;
								$nombre_contacto = $persona->nombre_emergencia;
								$afp= $persona->afp;
								$salud = $persona->salud;
								$salud_uf = $persona->salud_uf;
								
								$direccion = $persona->direccion  ;
								$ciudad = $persona->ciudad  ;
								$talla_pantalon = $persona->talla_pantalon  ;
								$talla_calzado = $persona->talla_calzado  ;
								$talla_camisa = $persona->talla_camisa ;
								$banco_id = $persona->banco  ;
								$tipo_cuenta_id = $persona->tipo_cuenta  ;
								$cuenta_bancaria = $persona->cuenta_bancaria ;

								//throw new Exception('alto', 1);

								if( empty( $Postulante->getId() ) ||  !is_numeric( $Postulante->getId() ) ) {
										
										$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
										$clave = substr(str_shuffle($permitted_chars), 0, 6);
										//$clave_temporal = random_int(1,99999999);

										if( empty( $Postulante->getDV() ) )
												$Postulante->setDV( $persona->dv );

										if( $Postulante->crearClave( $clave ) ){
												
												$fecha= date('d-m-Y');
												$subject ="Clave de acceso";
												$body ="Estimado postulante, su clave para futuros accesos al sistema ".
														"de postulacion es <strong>".$clave."</strong>";
					
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
			
												$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
												$mail->addAddress($email, '');
												$mail->isHTML(true);
												$mail->Subject = $subject;
												//$mail->Body = utf8_decode($body);
												$mail->Body = utf8_decode($tabla);
												$mail->send();

												$resp['token'] = $Postulante->getToken();
										
												$resp['mensaje']	= 'La clave para acceder al sistema de postulación ha sido enviada a su correo indicado en el formulario';
								
										}else{
										
												throw new Exception( $Postulante->getError() ,1);
								
										}
								}
								else if( !empty($Postulante->getId() ) && empty($post->token)){
										throw new Exception( "token", 1);
								}
								
								$Postulante->setNombre( $nombre );
								$Postulante->setApaterno( $apaterno );
								$Postulante->setAmaterno( $amaterno );
								$Postulante->setEmail( $email );
								
								$Postulante->setFechaNacimiento($fecha_nacimiento);
								$Postulante->setNacionalidadId($nacionalidad);
								$Postulante->setSexoId($sexo) ;
								$Postulante->setEstadoCivilId($estado_civil);
								$Postulante->setTelCelular($tel_celular);
								$Postulante->setTelFijo($tel_fijo);
								$Postulante->setTelContacto($tel_contacto);
								$Postulante->setNivelEducacionalId($nivel_educacional);
								$Postulante->setProfesion($profesion) ;
								$Postulante->setNombreContacto($nombre_contacto) ;
								$Postulante->setAfpId($afp) ;
								$Postulante->setSaludId($salud) ;
								$Postulante->setSaludUf($salud_uf) ;
								
								$Postulante->setDireccion( $direccion ) ;
								$Postulante->setCiudad( $ciudad ) ;
								$Postulante->setTallaPantalon( $talla_pantalon ) ;
								$Postulante->setTallaCalzado( $talla_calzado ) ;
								$Postulante->setTallaCamisa( $talla_camisa ) ;
								$Postulante->setBancoId( $banco_id ) ;
								$Postulante->setTipoCuentaId( $tipo_cuenta_id ) ;
								$Postulante->setCuentaBancaria( $cuenta_bancaria ) ;
								
								$Postulante->actualizarDatosPersonales();

								//if( !$Postulante->actualizarDatosPersonales() )
									//	throw new Exception( $Postulante->getError() ,1);


						break;

						case 2:

								$Postulante->setDatos();
								
								$resp['a'] = array();

								$directorio_tmp = '/home/admin/tmp/'.$Postulante->getRut();

								$directorio_final = '/home/admin/public_ftp/postulantes/'.$Postulante->getRut();


								if( !file_exists($directorio_tmp ) )
										throw new Exception('No hay documentos', 1);


								if( !file_exists($directorio_final ) )
										mkdir( $directorio_final , 0775, true);

								$directorio_tmp.='/';
								$directorio_final.='/';

								$errores = 0;
								
								#revision de documentos 
								$lista_docs = $Postulante->getListaDocumentosRequeridos( $rocp_id );

								$documentos_requeridos = array_map(
										function($l){
														return $l->getId();
								}, (array)$lista_docs);
				

								$documentos_encontrados = array();

								if ($gestor = opendir( $directorio_tmp )) {
								
										while (false !== ($entrada = readdir($gestor))) {
												

												if( !in_array( $entrada, array('.','..')) ){

														preg_match_all("/(^\d+).*(\.\w+)$/", $entrada, $e );
														
														$id = $e[1][0];
														$ext = $e[2][0];

														if( is_numeric( $id ))
																array_push( $documentos_encontrados, $id );														
												}
										}
 
										closedir($gestor);
								}

								if( count( array_diff( $documentos_requeridos, $documentos_encontrados ) ) )
										throw new Exception( "No se han adjuntado todos los documentos", 1 );


								#subida de documento
								if ($gestor = opendir( $directorio_tmp )) {
								
										while (false !== ($entrada = readdir($gestor))) {
												

												if( !in_array( $entrada, array('.','..')) ){

														preg_match_all("/(^\d+).*(\.\w+)$/", $entrada, $e );

														$id = $e[1][0];
														$ext = $e[2][0];

														$doc = new Rec_Documento();

														if( !is_numeric( $id ))
																throw new Exception( "error al mover el documento" ,1);

														$doc->setId($id);


														$nuevo_nombre = $id.$ext;
														
														if( file_exists( $directorio_final.$nuevo_nombre) )
																unlink($directorio_final.$nuevo_nombre);


														if( !copy( $directorio_tmp.$entrada, $directorio_final.$nuevo_nombre ) )
																$errores++;
														else
																unlink( $directorio_tmp.$entrada );

														$Postulante->addDocumento( $doc );
														
												}

										}
 
										closedir($gestor);
								}

								if( $errores )
										throw new Exception('Algunos documentos no puedieron ser confirmados');


								//$Postulante->guadarDocumentos();	
								$Postulante->guardarDocumentos();

						break;

						case 3:
								
								#revision de documentos 
								
								$Postulante->setDatos();

								$directorio_final = '/home/admin/public_ftp/postulantes/'.$Postulante->getRut();


								$lista_docs = $Postulante->getListaDocumentosRequeridos( $rocp_id );

								$documentos_requeridos = array_map(
										function($l){
														return $l->getId();
								}, (array)$lista_docs);
				

								$documentos_encontrados = array();

								if ($gestor = opendir( $directorio_final )) {
								
										while (false !== ($entrada = readdir($gestor))) {
												

												if( !in_array( $entrada, array('.','..')) ){

														preg_match_all("/(^\d+).*(\.\w+)$/", $entrada, $e );

														if( isset($e[1][0]) && isset($e[2][0]) ){
															
																$id = $e[1][0];
																$ext = $e[2][0];

																if( is_numeric( $id ))
																		array_push( $documentos_encontrados, $id );	
														}
																												
												}
										}
 
										closedir($gestor);
								}

	
								if( count( array_diff( $documentos_requeridos, $documentos_encontrados ) ) )
										throw new Exception( "No se han adjuntado todos los documentos", 1 );


								if( ! $id = $Postulante->postular( $rocp_id ) )
										throw new Exception('Ocurrio un error al confirmar la postulacion', 1);

								/***** correos****/
								
								$postulaciones = $Postulante->getListaPostulaciones($rocp_id);

								$cargo = $postulaciones[0]['perfil_nombre'];

								$nombre = $Postulante->getNombre().' '.$Postulante->getApaterno().' '.$Postulante->getAmaterno();

								$correos = $Postulante->getListaCorreosInteresadosPostulacion( $rocp_id );
	
				
								$body ="Estimado,<br><br> 
										<strong>".$nombre."</strong> se ha registrado como postulante para el cargo de <strong>".$cargo."</strong>" 
										."<br><br>Saludos cordiales.";

								
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
								
								$subject = "Solicitud de reclutamiento";
								
								$mail->setFrom('gestop@todoacero.cl', 'SISTEMA DE POSTULACION' );

								
								foreach( (array)$correos as $c ){
									
										switch($c['tipo']){

												case 'PARA':
														
														$mail->addAddress($c['correo'], '');
										
												break;

												case 'CC':

														$mail->addCC($c['correo'], '');

												break;

												case 'BCC':

														$mail->addBCC($c['correo'], '');

												break;

												default:
								
														$mail->addAddress($c['correo'], '');
										
												break;
										
										}

								}
						
								$mail->isHTML(true);
								$mail->Subject = $subject;
								$mail->Body = utf8_decode($tabla);
								$mail->send();
						

				/****fin correo*****/


								$resp['mensaje'] ="La postulación ha sido confirmada con el folio ".$id;

								break;

				}


				


		#es necesario reemplazar por token

		
			

					
			//throw new Exception( 'En implementacion');


			break;



		case 'subir_archivo_temporal':


				if( !isset( $post->rut ) || empty( $post->rut ) || !is_numeric( $post->rut ) )
						throw new Exception("Rut inválido", 1);

				if( !isset( $post->doc_id ) || empty( $post->doc_id ) || !is_numeric( $post->doc_id ) )
						throw new Exception("Rut inválido", 1);

				if ($_FILES["archivo"]["error"] > 0){
						
						$error_code =$_FILES["archivo"]["error"];

						switch ($error_code) { 
				
								case UPLOAD_ERR_INI_SIZE: 
										throw new Exception("El archivo es más grande que lo permitido por el Servidor.", 1); 
								case UPLOAD_ERR_FORM_SIZE: 
										throw new Exception("El archivo subido es demasiado grande.", 1); 
								case UPLOAD_ERR_PARTIAL: 
										throw new Exception("El archivo subido no se terminó de cargar (probablemente cancelado por el usuario).", 1); 
								case UPLOAD_ERR_NO_FILE: 
										throw new Exception("No se subió ningún archivo", 1); 
								case UPLOAD_ERR_NO_TMP_DIR: 
										throw new Exception("Error del servidor: Falta el directorio temporal.", 1); 
								case UPLOAD_ERR_CANT_WRITE: 
										throw new Exception("Error del servidor: Error de escritura en disco", 1); 
								case UPLOAD_ERR_EXTENSION: 
										throw new Exception("Error del servidor: Subida detenida por la extención", 1);
								default: 
										throw new Exception("Error del servidor: ".$error_code, 1); 
						} 
				}


				$rut = $post->rut;

				$doc_id = $post->doc_id;

				$archivo = $_FILES['archivo']['tmp_name'];

				$archivo_nombre = $_FILES['archivo']['name'];


				$directorio = '/home/admin/tmp/'.$rut;

				if ( ! file_exists( $directorio ) )
						mkdir( $directorio , 0777, true);

				$archivo_ruta = $directorio.'/'.$doc_id.'-'.$archivo_nombre;

				
				if(!move_uploaded_file( $archivo , $archivo_ruta ) )
						throw new Exception("Error al guardar archivo", 1);

				break;

		case 'borrar_archivo_temporal':


				if( !isset( $post->rut ) || empty( $post->rut ) || !is_numeric( $post->rut ) )
						throw new Exception("Rut inválido", 1);

				if( !isset( $post->doc_id ) || empty( $post->doc_id ) || !is_numeric( $post->doc_id ) )
						throw new Exception("Rut inválido", 1);

				$rut = $post->rut;

				$doc_id = $post->doc_id;



				$directorio = '/home/admin/tmp/'.$rut;

				if (file_exists($directorio) && ($gestor = opendir( $directorio ) ) ) {
							
							try{

									while (false !== ($entrada = readdir($gestor))) {

											if( !in_array( $entrada, array('.','..')) ){

													if( preg_match('/^'.$doc_id.'\-*/', $entrada ) ){
															
															$archivo_ruta = $directorio.'/'.$entrada;
															
															unlink($archivo_ruta);

													}
											}

									}

							}catch(Exception $e){
															

							}finally{

									if(!empty($gestor))

											closedir($gestor);

							}
			
				}

			

				break;




			case 'postulaciones':

				
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("token", 1);



				if( ! $Postulante = Rec_Postulante::LoginToken( $post->token ) )

						throw new Exception("token", 2);


				$Postulante->setDatos();


				if( !isset( $post->rocp_id ) || intval($post->rocp_id) == 0 ){
						
						$lista = $Postulante->getUltimaPostulacion();
						$resp['oferta']= ( $lista= array_shift( $lista ) );
						$rocp_id = $lista['rocp_id'];
				}
				else{
						$lista = $Postulante->getListaPostulaciones($post->rocp_id);
						$resp['oferta']= ( $lista= array_shift( $lista ) );
						$rocp_id = $post->rocp_id;
				}


				$resp['postulaciones'] = $Postulante->getListaPostulaciones();

				/*documentos*/

				$rut = $Postulante->getRut();
			
				$resp['rut'] = $rut;

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut";
		
				$documentos = $Postulante->getListaDocumentosPostulacion( $rocp_id );


				$documentos = array_filter((array)$documentos, function($doc){

						return $doc['validado']==0 || $doc['validado']===false;

				}, ARRAY_FILTER_USE_BOTH);

				$revisados = 0;

				if (file_exists($ruta_archivo) && ($gestor = opendir( $ruta_archivo ) ) ) {
							
							try{

									while (false !== ($entrada = readdir($gestor))) {

											if( !in_array( $entrada, array('.','..')) ){

													foreach( (array)$documentos as &$doc){

															if( preg_match('/'.$doc['id'].'\.*/', $entrada ) )

																	$doc['archivo'] = $entrada;
																$revisados++;

													}
											}

									}

							}catch(Exception $e){
															

							}finally{

									if(!empty($gestor))

											closedir($gestor);

							}
			
				}
				
				
				$resp['documentos_pendientes'] = array();
				$resp['revisados'] = $revisados;
				
				foreach($documentos as $doc){
				    array_push( $resp['documentos_pendientes'] ,$doc);
				}
						
				/*fin documentos*/



				$resp['etapas']= $Postulante->getListaEtapasPostulacion( $rocp_id );

				foreach ( $resp['etapas'] as &$et ){
						
						if( $et['reta_id'] == 3 ){

										$et['inducciones'] = $Postulante->getListaInduccionesGeneralesPostulacion( $rocp_id );
						
						}
						else if( $et['reta_id'] == 5 ){
						
										$et['inducciones'] = $Postulante->getListaInduccionesEspecificasPostulacion( $rocp_id );
						}
						else if( $et['reta_id'] == 12 ){

										$et['inducciones'] = $Postulante->getListaCursosOpeConductores( $rocp_id );
						
						}
						else if( $et['reta_id'] == 6 ){
								
								$et['examen_covid']= $Postulante->getUltimoExamenCovidPostulante();
						}
						else if( $et['reta_id'] == 4 ){
								
								$Postulante->setDatos();

								$et['epp'] = array(
																'sexo' => $Postulante->getSexoId(),	
																'talla_pantalon' => $Postulante->getTallaPantalon(),	
																'talla_camisa' => $Postulante->getTallaCamisa(),	
																'talla_calzado' => $Postulante->getTallaCalzado(),	
														);
						}

				}

				
				
				break;
			
			
			
			case 'aprobar_etapa_postulante':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Rec_Postulante::LoginToken( $post->token ) )

						throw new Exception("token", 2);

				if( !isset($post->rocpeta_id) || empty($post->rocpeta_id) )
						throw new Exception("Etapa no recibida",1);

				if( !isset($post->procp_id) || empty($post->procp_id) )
						throw new Exception("Postulacion no recibida",1);


				$rocpeta_id = $post->rocpeta_id;
				
				$procp_id = $post->procp_id;
				
				$puntaje = 100;
				
				$aprobado = null;
				
				$aprobado = 1;

				$fecha_hora = date('Y-m-d H:i');

				$observacion = null;
				
				$link = null;
				
				$obs_puntaje = null;
				
				$direccion = null;



				if( ! $id = $Usuario->registrarEtapaPostulacion( $rocpeta_id, $procp_id, $puntaje, $obs_puntaje, $fecha_hora, $direccion, $link , $observacion, $aprobado ) )
						throw new Exception("No fue posible registrar la informacion", 1);


				break;
			
			case 'confirmar_documentos':
				
	
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

	
					if( ! $Usuario = Rec_Postulante::LoginToken( $post->token ) )

						throw new Exception("token", 2);

		
					if( !isset( $post->rut ) || empty( $post->rut ) || !is_numeric( $post->rut ) )
			
							throw new Exception("Rut inválido", 1);


					$cargo = $post->cargo;
					
					$directorio_tmp = '/home/admin/tmp/'.$post->rut;
					
					$directorio_final = '/home/admin/public_ftp/postulantes/'.$post->rut;

					$documentos_pendientes = (array)$post->documentos_pendientes;


					if( !file_exists($directorio_final) || !file_exists($directorio_tmp) )

							throw new Exception('El directorio no existe');



					$errores=0;


					try{
							
							if ($gestor = opendir( $directorio_tmp )) {
								
							while (false !== ($entrada = readdir($gestor))) {
				
									if( !in_array( $entrada, array('.','..')) ){

											foreach( $documentos_pendientes as $id ){

													if( preg_match('/^'.$id.'\-.*/', $entrada ) ){
															
															preg_match_all("/(^\d+).*(\.\w+)$/", $entrada, $e );
															
															//$id = $e[1][0];
															
															$ext = $e[2][0];
															
															$nuevo_nombre = $id.$ext;
														
															if( file_exists( $directorio_final.'/'.$nuevo_nombre) )
																	
																	unlink($directorio_final.'/'.$nuevo_nombre);
														
															if( !copy( $directorio_tmp.'/'.$entrada, $directorio_final.'/'.$nuevo_nombre ) )
		
																	$errores++;
			
															else{
				
																	unlink( $directorio_tmp.'/'.$entrada );

																	$Usuario->actualizarDocumento( $id );
															}
													}
											}
									}
							}
							
							}
					
	
					
					}
					catch ( Exception $e){
					
					}
					finally{
					
							closedir($gestor);

					}

						
					$correos = $Usuario->getListaCorreoPostulacionDocumentos();

					$Usuario->setDatos();

					$nombre = ucfirst(strtolower($Usuario->getNombre())).' '.ucfirst(strtolower($Usuario->getApaterno())); 

					$body ="Estimado.<br><br> 
										El postulante ".$nombre." actualizo los documentos para la postulacion al cargo ".$cargo.".
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

					$subject = "Actualizacion de documentos";

					$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
			
					foreach( (array)$correos as $c ){
									
							$mail->addAddress($c['correo'], '');

					}

					$mail->isHTML(true);

					$mail->Subject = $subject;

					$mail->Body = utf8_decode($tabla);
				
					$mail->send();
						
					break;
	
			case 'reclutamiento_descargar_documento':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Rec_Postulante::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				
				if( !isset($post->rut) || empty( $post->rut ) )

						throw new Exception("Rut del postulante no recibido", 1);


				if( !isset($post->doc) || empty( $post->doc ) )
						throw new Exception("Nombre de documento no recibido", 1);


				$rut = $post->rut;

				$doc = $post->doc;
				

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut/$doc";

				if(!file_exists($ruta_archivo) )
						throw new Exception("No existen documentos");
				

				
				// Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
				
				header("Content-type: application/octet-stream");
				header("Content-disposition: attachment; filename='".basename( $ruta_archivo )."'");
				readfile( $ruta_archivo );


				break;


		default:

      throw new Exception('CASE default');
			break;

  }

  

}catch(Exception $e){


  $resp['error'] = $e->getMessage();

}


//vomito

echo json_encode($resp);

?>
