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

require_once('../clases/Rec_Solicitud.php');

require_once('../clases/Rec_Oferta.php');

require_once('../clases/Rec_Perfil.php');

require_once('../clases/Rec_Perfil_Evaluado.php');

//require_once('../clases/Rec_Perfil_Solicitud.php');

require_once('../clases/Rec_Competencia.php');

require_once('../clases/Rec_Competencia_Evaluada.php');

require_once('../clases/Rec_Examen_Evaluado.php');

$resp = array('error'=>false, 'preguntas'=> array() );


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

    

		case 'resultados_encuestas':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

				if(!isset($post->encta_id) || !is_numeric( $post->encta_id ) )

						throw new Exception("Id encuesta no recibida", 1);


				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

		
				
				$encta_id = $post->encta_id;

				$resp['preguntas'] = array();
			

				$resp['resumen'] = $RRHH->getResumenRespuestasEncuesta( $encta_id) ;


				$resp['encta_id'] = $encta_id; 

    break;

		case 'obtener_perfil':

				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

				if(!isset($post->pers_id) || !is_numeric( $post->pers_id ) )

						throw new Exception("Identificador de la persona no recibido", 1);


				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				$Persona = Persona::getInstancia($post->pers_id);
			

				$resp['perfil'] = $Persona->getPerfilDisc() ;

				
				$resp['respuestas'] = array();


				if(isset($post->resp_id) && is_numeric( $post->resp_id ) )

						$resp['respuestas'] = $RRHH->getRespuestasEncuesta( $post->resp_id );


				break;

		case 'obtener_perfil_postulacion':

				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);


				if(!isset($post->procp_id) || !is_numeric( $post->procp_id ) )

						throw new Exception("Identificador de la postulacion no recibido", 1);


				//if(!isset($post->rocp_id) || !is_numeric( $post->rocp_id ) )

					//	throw new Exception("Identificador del perfil no recibido", 1);


				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				$procp_id = $post->procp_id;
				$post_id = $post->post_id;
				$rocp_id = $post->rocp_id;
				//$Persona = Persona::getInstancia($post->pers_id);
				
				
				$resp['perfil'] = $RRHH->getPerfilDiscPostulacion( $procp_id ) ;

				$postulante = new Rec_Postulante();

				$postulante->setId( $post_id );

				$postulante->setDatos();
				
				$id = $postulante->getNacionalidadId();
				
				$nacionalidades =$postulante->getListaNacionalidad() ;

				$nacionalidad = array_filter( $nacionalidades ,function($v, $k) use ($id){
						return 	$v['id']==$id;
				},ARRAY_FILTER_USE_BOTH);

				if( !empty($nacionalidad) ){
						$nacionalidad = array_shift($nacionalidad);
						$nacionalidad = $nacionalidad['nombre'];
				}else{
						$nacionalidad = null;
				}

				$sexo =  $postulante->getSexoId() ;

				if( !empty( $sexo ) ){

						$sexo = ( strtoupper( $sexo) =='M' )? 'Masculino' : 'Femenino';

				}else{
						$sexo = null;
				} 

				$resp['personales'] = array(
						'rut' => $postulante->getRut(),
						'nombre' => $postulante->getNombre(),
						'apaterno' => $postulante->getApaterno(),
						'amaterno' => $postulante->getAmaterno(),
						'direccion' => $postulante->getDireccion(),
						'telefono' => $postulante->getTelCelular(),
						'email' => $postulante->getEmail(),
						'nacionalidad' => $nacionalidad,
						'sexo' =>$sexo,
				);

				$resp['examenes'] = $postulante->getListaExamenesPostulacion( $rocp_id );
					
				$resp['competencias_tecnicas'] = $postulante->getListaCompetenciasTecnicasPostulacion( $rocp_id );
				
				$resp['competencias_blandas'] = $postulante->getListaCompetenciasBlandasPostulacion( $rocp_id );

				$etapas = $postulante->getListaEtapasPostulacion( $rocp_id );

				$etapa_tecnica = array_filter( $etapas, function($v, $k) {
						return $v['reta_id']==1; 
				}, ARRAY_FILTER_USE_BOTH);

				$etapa_blanda = array_filter( $etapas, function($v, $k) {
						return $v['reta_id']==16; 
				}, ARRAY_FILTER_USE_BOTH);

				$etapa_examen = array_filter( $etapas, function($v, $k) {
						return $v['reta_id']==2; 
				}, ARRAY_FILTER_USE_BOTH);


				$obs_tecnica = null;

				if( !empty( $etapa_tecnica )){

						$etapa_tecnica = array_shift( $etapa_tecnica );
						
						$rocpeta_id = $etapa_tecnica['id'];
						
						$etapa = $RRHH->getDetalleEtapaPostulacion( $procp_id, $rocpeta_id);

						$obs_tecnica = ( !empty($etapa) ) ? $etapa['descripcion'] : null;

				}

				$obs_blanda = null;

				if( !empty( $etapa_blanda )){

						$etapa_blanda = array_shift( $etapa_blanda );
						
						$rocpeta_id = $etapa_blanda['id'];
						
						$etapa = $RRHH->getDetalleEtapaPostulacion( $procp_id, $rocpeta_id);

						$obs_blanda = ( !empty($etapa) ) ? $etapa['descripcion'] : null;

				}

				$obs_examen = null;

				if( !empty( $etapa_examen )){

						$etapa_examen = array_shift( $etapa_examen );
						
						$rocpeta_id = $etapa_examen['id'];
						
						$etapa = $RRHH->getDetalleEtapaPostulacion( $procp_id, $rocpeta_id);

						$obs_examen = ( !empty($etapa) ) ? $etapa['descripcion'] : null;

				}

				####### documentos #####
				$rut= $postulante->getRut();

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut";

				$archivos = 0;
				$documentos= array();
				$documentos_solicitados= array();

				if( $documentos_solicitados = $RRHH->getDocumentosOfertaPerfil( $post->rocp_id  ) ){

						$docs = array_map( function($c){

								return array(
										'id'=> $c->getId(),
										'descripcion' => $c->getDescripcion(),
								);

						}, $documentos_solicitados );
				}
				
				if( file_exists( $ruta_archivo ) ){
						
						if ($gestor = opendir( $ruta_archivo )) {
		
								while (false !== ($entrada = readdir($gestor))) {

								/*		if( !in_array( $entrada, array('.','..')) ){
												$archivos++;
												array_push( $documentos, $entrada);
								}*/
										foreach( $docs as &$doc){
												if( preg_match('/'.$doc['id'].'\.*/', $entrada ) )
														$doc['archivo'] = $entrada;
										}
								}

						}

						closedir($gestor);

				}

				//$resp['rut'] = $rut;

				$resp['documentos']= $docs;

				//$resp['documentos'] = $documentos ;

				$resp['obs_tecnica'] = $obs_tecnica ;

				$resp['obs_blanda'] = $obs_blanda;
				
				$resp['obs_examen'] = $obs_examen;

					//$resp['detalle'] = $Usuario->getDetalleEtapaPostulacion( $procp_id, $rocpeta_id);

				break;


		case 'eliminar_respuesta':

				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);


				if(!isset($post->id) || !is_numeric( $post->id ) ) //resp_id

						throw new Exception("Identificador de la persona no recibido", 1);


				if(!isset($post->encta_id) || !is_numeric( $post->encta_id ) )

						throw new Exception("Id encuesta no recibida", 1);


				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				$encta_id = $post->encta_id;

				
				if( ! $RRHH->eliminarRespuestaEncuesta( $post->id ) )

						throw new Exception('No fue posible eliminar el registro',1);
				

				
				$resp['mensaje'] = "Encuesta liberada";
				
				$resp['resumen'] = $RRHH->getResumenRespuestasEncuesta( $encta_id) ;

				break;

			case 'reclutamiento_sol_ini':

				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);
	

				$resp['tipo_competencias'] = $Usuario->getTipoCompetencias();

				$resp['documentos'] = $Usuario->getTiposDocumento();
				
				$resp['turnos'] = $Usuario->getTurnos();
				
				$resp['evaluadores'] = $Usuario->getUsuariosSubModEntrevistaTecnica();


				$Empresa = new Empresa();
				
				$resp['faenas'] = array_map(function($f){ return array('id'=>$f->getId(), 'nombre'=>$f->getNombre() );}, $Empresa->getFaenas() );
				
				$resp['perfiles'] = array_map(function($p){ 
						return array(
								'id'=>$p->getId(), 
								'descripcion'=> $p->getDescripcion(),
								'sueldo'=> $p->getSueldo()
					 	);
				}, $Usuario->getPerfilesCargo() );

				$resp['areas'] = $Empresa->getAreas();

				$resp['fecha_actual'] = date('d-m-Y');
				//$competencias = $Usuaro->getCompetencias( null );

				break;

			case 'competencias_perfil':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				if( ! is_numeric($post->id) )// id perfil

						throw new Exception("Identificador no válido" , 1);

				$id_perfil = $post->id;

				$resp['documentos'] = $Usuario->getTiposDocumento( $id_perfil );

				$comp = array();

				if( $competencias = $Usuario->getCompetenciasPerfil( $id_perfil ) ){

						$comp = array_map( function($c){

								return array(
										'id'=> $c->getId(),
										'titulo' => $c->getTitulo(),
										'descripcion' => $c->getDescripcion(),
										'tipo_descripcion' => $c->getTipoDescripcion(),
										'tipo_id' => $c->getTipoId()
								);
						}, $competencias );
				}
						
				$resp['competencias'] = $comp;

				
				$impl = array();

				if( $implementos = $Usuario->getImplementosPerfil( $id_perfil ) ){

						$impl= array_map( function($c){

								return array(
										'id'=> $c->getId(),
										'descripcion' => $c->getDescripcion(),
										'tipo_descripcion' => $c->getTipoDescripcion()
								);
						}, $implementos );
				}
						
				$resp['implementos'] = $impl;

				break;
			
			
			case 'datos_perfil_solicitado':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				if( ! is_numeric($post->id) )// id perfil

						throw new Exception("Identificador no válido" , 1);

				$id_perfil = $post->id;


				$comp = array();

				if( $competencias = $Usuario->getCompetenciasPerfilSolicitado( $id_perfil ) ){

						$comp = array_map( function($c){

								return array(
										'id'=> $c->getId(),
										'titulo' => $c->getTitulo(),
										'descripcion' => $c->getDescripcion(),
										'tipo_descripcion' => $c->getTipoDescripcion()
								);
						}, $competencias );
				}
				
				$docs = array();

				if( $documentos = $Usuario->getDocumentosPerfilSolicitado( $id_perfil ) ){

						$docs = array_map( function($c){

								return array(
										'id'=> $c->getId(),
										//'titulo' => $c->getTitulo(),
										'descripcion' => $c->getDescripcion(),
										//'tipo_descripcion' => $c->getTipoDescripcion()
								);
						}, $documentos );
				}
				
				
				$impl = array();

				if( $implementos = $Usuario->getImplementosPerfilSolicitado( $id_perfil ) ){

						$impl = array_map( function($c){

								return array(
										'id'=> $c->getId(),
										//'titulo' => $c->getTitulo(),
										'descripcion' => $c->getDescripcion(),
										'tipo_descripcion' => $c->getTipoDescripcion()
								);
						}, $implementos );
				}

				$resp['competencias'] = $comp;
				
				$resp['documentos'] = $docs;
				
				$resp['implementos'] = $impl;


				break;

			case 'reclutamiento_guardar_solicitudes':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				if( empty( $post->solicitudes ) || !isset( $post->solicitudes ) || ! is_array( $post->solicitudes ) )

						throw new Exception("No hay solicitudes para procesar" , 1);
				


				$solicitud = new Rec_Solicitud();//no se ha definido id ni texto

				$obs = $post->observacion;//falta limpiar string
				
				$urgente = $post->urgente === true;//falta limpiar string

				$solicitud->setDescripcion( $obs );
				
				foreach( $post->solicitudes as $sol){ //deberian ser perfiles dentro de la solicitud

						$perfil = new Rec_Perfil_Solicitud();
						$perfil->setId( $sol->perfil );
						$perfil->setDescripcion( $sol->descripcion );
						$perfil->setCantidad( $sol->cantidad );
						$perfil->setTurnoId( $sol->turno );
						$perfil->setFaena( $sol->faena );
						$perfil->setArea( $sol->area );
						$perfil->setFechaReq( $sol->fecha );
						$perfil->setEvaluadorUsrId( $sol->evaluador );

						if( ! is_numeric( $sol->sueldo ) )
								throw new Exception('El sueldo debe ser un valor numérico',1);

						$perfil->setSueldo( $sol->sueldo );

						if( empty( $sol->competencias ) || !isset( $sol->competencias ) || !is_array( $sol->competencias ) ){
				
								throw new Exception("Uno de los perfiles solicitados no tiene definido las competencias" , 1);
						}
						else{

								foreach( $sol->competencias as $comp ){		
										
										$competencia = new Rec_Competencia();
						
										$competencia->setId( $comp );

										$perfil->addCompetencia( $competencia );
								}

						}

						
						if( empty( $sol->documentos ) || !isset( $sol->documentos ) || !is_array( $sol->documentos ) ){
				
								throw new Exception("Uno de los perfiles solicitados no tiene definido la documentación requerida" , 1);
						}
						else{

								foreach( $sol->documentos as $doc ){		
										
										$documento = new Rec_Documento();
						
										$documento->setTipo( $doc );

										$perfil->addDocumento( $documento );
								}

						}
						
						if( empty( $sol->implementos ) || !isset( $sol->implementos ) || !is_array( $sol->implementos ) ){
				
								// throw new Exception("Uno de los perfiles solicitados no tiene definido la documentación requerida" , 1);
						}
						else{

								foreach( $sol->implementos as $impl ){		
										
										$implemento = new Implemento();
						
										$implemento->setId( $impl );

										$perfil->addImplemento( $implemento );
								}

						}

						
						$solicitud->addPerfil( $perfil );
				}

				if( $id_sol = $Usuario->registrarSolicitudOfertaLaboral( $solicitud , $urgente) ){

						$resp['solicitud'] = $id_sol;
				}
				else{
						throw new Exception( $Usuario->getError() , 1);
				}


				$resp['mensaje'] = 'La solicitud ha sido registrada con el folio '.$id_sol;

				break;

	
			case 'reclutamiento_solicitudes_usuario':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

		
				$solicitudes = $Usuario->getMisSolicitudesOfertaLaboral( $solicitud = null );

				$resp['solicitudes'] = array_map(
						function ($sol){

								$perfiles = $sol->getPerfiles();

								$cantidad = 0;

								$detalle = array_map( 
										function( $p ) use ($cantidad) {

//												$cantidad+= !is_numeric( $p->getCantidad() ) ? 0 : $p->getCantidad() ;

												return array(
														'id' => $p->getId(),
														'descripcion' => $p->getDescripcion(),
														'cantidad' => $p->getCantidad(),
														'observacion' => $p->getObservacion()
												);

										}, (array)$perfiles);

								return array(
										'id' => $sol->getId(),
										'fecha' => $sol->getFechaString(),
										//'faena' => $sol->getFaenaNombre(),
										//'area' => $sol->getAreaNombre(),
										'cantidad' => $sol->getCantidadPersonasSolicitadas(),
										'estado_id' => $sol->getEstadoId(),
										'estado' => $sol->getEstadoDescripcion(),
										'perfiles' => $detalle
								);
						}
				, $solicitudes );

				break;

			case 'reclutamiento_solicitudes_pendientes':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH= RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);
				
				
				$Empresa = new Empresa();
				
				$resp['faenas'] = array_map(function($f){ return array('id'=>$f->getId(), 'nombre'=>$f->getNombre() );}, $Empresa->getFaenas() );

				$resp['areas'] = $Empresa->getAreas();

				$resp['turnos'] = $RRHH->getTurnos();
				
				$resp['documentos'] = $RRHH->getTiposDocumento();
				
				
				$solicitudes = $RRHH->getSolicitudesOfertaLaboralEstado( $estado = 3 );

				$resp['solicitudes'] = array_map(
						function ($sol){

								$cantidad = 0;
								
								return array(
										'id' => $sol->getId(),
										'usuario_nombre' => $sol->getUsuarioNombre(),
										'descripcion' => $sol->getDescripcion(),
										'fecha' => $sol->getFechaString(),
										'cantidad' => $sol->getCantidadPersonasSolicitadas(),
										'estado_id' => $sol->getEstadoId(),
										'estado_descripcion' => $sol->getEstadoDescripcion(),
										'urgente' => $sol->getUrgente(),
								);
						}
				, $solicitudes );

				$resp['plantillas'] = $RRHH->getPlantillasEtapasReclutamiento();

				break;

			case 'reclutamiento_solicitudes_pendiente_aprobacion':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

		
				$solicitudes = $Usuario->getSolicitudesPorAprobarOfertaLaboral( $solicitud = null );

				$resp['solicitudes'] = array_map(
						function ($sol){

								$perfiles = $sol->getPerfiles();

								$cantidad = 0;

								$detalle = array_map( 
										function( $p ) use ($cantidad) {

												return array(
														'id' => $p->getId(),
														'descripcion' => $p->getDescripcion(),
														'cantidad' => $p->getCantidad(),
														'faena_nombre' => $p->getFaenaNombre(),
														'area_nombre' => $p->getAreaNombre(),
														'observacion' => $p->getObservacion(),
														'sueldo' => $p->getSueldo(),
														'turno_nombre' =>$p->getTurnoDescripcion(),
														'evaluador' =>$p->getEvaluadorUsrId(),
														'habilitado' => true
												);

										}, (array)$perfiles);

								return array(
										'id' => $sol->getId(),
										'solicitante_nombre' => $sol->getUsuarioNombre(),
										'fecha' => $sol->getFechaString(),
										'cantidad' => $sol->getCantidadPersonasSolicitadas(),
										'estado_id' => $sol->getEstadoId(),
										'estado' => $sol->getEstadoDescripcion(),
										'urgente' => $sol->getUrgente(),
										'perfiles' => $detalle
								);
						}
				, $solicitudes );



				$resp['evaluadores'] = $Usuario->getUsuariosSubModEntrevistaTecnica();

				break;
		
			case 'reclutamiento_perfiles_solicitud':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH= RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);
				
				if( empty( $post->id ) || !isset( $post->id ) || !is_numeric( $post->id ) )

						throw new Exception("El identificador de la solicitud no es válido" , 1);

				$sol_id = $post->id;

				$con_oferta = ( isset($post->incluir_con_oferta) && $post->incluir_con_oferta==false) ? false : true ;

				$solicitud = $RRHH->getSolicitudOfertaLaboral( $sol_id );

				$solicitud->setPerfiles( $con_oferta );

				$resp['perfiles'] = array_map(

						function ( $perfil ) use ( $RRHH ){
								
								$comp = array();

								$comp = array_map( function($c){
										
										return array(
													'id'=> $c->getId(),
													'tipo_id'=> $c->getTipoId(),
													'titulo' => $c->getTitulo(),
													'descripcion' => $c->getDescripcion(),
													'tipo_descripcion' => $c->getTipoDescripcion(),
													//'estado_id' => $c->getEstadoId()
										);

								}, (array)$perfil->getCompetencias() );
								
								$comp_blandas = array_filter($comp, function($v, $k){
										return $v['tipo_id']!=2;
								}, ARRAY_FILTER_USE_BOTH);

								$docs = array();


								$docs = array_map( function($c){

										return array(
												'id'=> $c->getId(),
												'tipo_id'=> $c->getTipo(),
												'descripcion' => $c->getDescripcion(),
										);
								}, (array)$perfil->getDocumentos() );
				
			
								
								$impl = array();
								
								$impl = array_map( function($i){

										return array(
												'id'=> $i->getId(),
												'descripcion' => $i->getDescripcion(),
												'tipo_descripcion' => $i->getTipoDescripcion()
										);
								}, (array)$perfil->getImplementos() );


								
								$perfil_impl = array();

						
								if( $implementos = $RRHH->getImplementosPerfil( $perfil->getTipoId() ) ){

										$perfil_impl = array_map( function($c){

												return array(
														'id'=> $c->getId(),
														'descripcion' => $c->getDescripcion(),
														'tipo_descripcion' => $c->getTipoDescripcion()
												);
										}, $implementos );
				
								}	
								
								$perfil_exam = array();

						
								if( $examenes = $RRHH->getExamenesPerfil( $perfil->getTipoId() ) ){

										$perfil_exam = array_map( function($c){

												return array(
														'id'=> $c->getId(),
														'titulo' => $c->getTitulo(),
														'descripcion' => $c->getDescripcion()
												);
										}, $examenes);
				
								}

								return array(
														
										'id' => $perfil->getId(),
										'tipo_id' => $perfil->getTipoId(),
										'plantilla' => '',
										'habilitado' => true,
										'descripcion' => $perfil->getDescripcion(),
										'cantidad' => $perfil->getCantidad(),
										'oferta_cantidad' =>$perfil->getCantidad(),
										'observacion' => $perfil->getObservacion(),
										'fecha' => $perfil->getFechaReq(),
										'oferta_fecha' => $perfil->getFechaReq(),
										'faena' => $perfil->getFaena(),
										'oferta_faena' => $perfil->getFaena(),
										'area' => $perfil->getArea(),
										'oferta_area' => $perfil->getArea(),
										'oferta_observacion' => 'Empresa de fabricación metalmecánica, requiere para sus operaciones el siguiente personal: ',
										'turno' => $perfil->getTurnoId(),
										'oferta_turno' => $perfil->getTurnoId(),
										'sueldo' =>$perfil->getSueldo(),
										'oferta_sueldo'=> $perfil->getSueldo(),
										'evaluador_nombre'=> $perfil->getEvaluadorNombre(),
										'evaluador_id'=> $perfil->getEvaluadorUsrId(),
										'competencias' => $comp,
										'oferta_competencias' => array_column($comp, 'id'),
										//'oferta_competencias_evaluadas'=> array(),
										'oferta_competencias_evaluadas'=> array_merge(array_column($comp, 'id'), array_column($comp_blandas, 'id' ) ),
										'oferta_documentos' => array_column($docs, 'tipo_id'),
										'implementos' => $impl,
										'oferta_implementos' => array_column($impl, 'id'),
										'perfil_implementos' => $perfil_impl,
										'perfil_examenes' => $perfil_exam,
										'oferta_examenes' => array_column($perfil_exam, 'id'),
								);
						}
				, $solicitud->getPerfiles() );

				break;
			
			case 'reclutamiento_guardar_oferta':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				if( empty( $post->oferta ) || !isset( $post->oferta ) )

						throw new Exception("No hay ofertas para procesar" , 1);


				//throw new Exception("veamos los datos",1);

					
				$of = $post->oferta;

				$array_ofertas = array();

				$publicacion_simple = isset($of->publicacion_simple) && $of->publicacion_simple===true ? true: false ;

				$array_fb = array( 
						'descripcion'=>'', 
						'imagen'=>'',
						'cargos' => array() 
				);

				$oferta = null;

				$f_ini = $of->fecha_inicial;
				$f_fin = $of->fecha_final;

				list($y,$m,$d) = explode( '-', $f_ini);
				$f_ini = $d.'-'.$m.'-'.$y;
				list($y,$m,$d) = explode( '-', $f_fin);
				$f_fin = $d.'-'.$m.'-'.$y;

				$texto_agrupado = "Inicio de postulacion: ".$f_ini."
						Cierre de postulacion: ".$f_fin;


				$array_fb['descripcion'] = $of->oferta_descripcion;

				$textos_individuales = array();

				
						foreach( (array)$of->cargos_solicitados as $sol){ //deberian ser perfiles dentro de la solicitud

								if( isset( $sol->habilitado ) && $sol->habilitado==true ){
						
										#array a publicar
										array_push( $array_fb['cargos'], 
												array(
														'nombre' => $sol->descripcion,
														'descripcion' => $sol->oferta_observacion,
														'competencias_evaluadas' => $sol->oferta_competencias_evaluadas,
														'competencias'=> $sol->competencias
												)
										);


										 
										if( empty($oferta) || $publicacion_simple === false ){


												$oferta = new Rec_Oferta();//no se ha definido id ni texto

												$obs = $of->oferta_descripcion;//falta limpiar string

												$oferta->setSolicitudId( $of->solicitud_id );
												$oferta->setDescripcion( $obs );
												$oferta->setTitulo(null);
												$oferta->setEstadoId( $estado =1 );
												$oferta->setFechaPublicacion( $publicacion_simple ? $of->fecha_inicial : $sol->fecha_inicial  );
												$oferta->setFechaCierre( $publicacion_simple ? $of->fecha_final : $sol->fecha_final );

										}



										$perfil = new Rec_Perfil_Evaluado();
										$perfil->setId( $sol->id );
										$perfil->setTipoId( $sol->tipo_id );
										$perfil->setDescripcion( $sol->oferta_observacion );
										$perfil->setCantidad( $sol->oferta_cantidad );
										$perfil->setTurnoId( $sol->oferta_turno );
										$perfil->setFaena( $sol->oferta_faena );
										$perfil->setArea( $sol->oferta_area );
										$perfil->setSueldo( $sol->oferta_sueldo );
										$perfil->setPlantillaId( $sol->plantilla );

						
										if( empty( $sol->oferta_competencias ) || !isset( $sol->oferta_competencias ) || !is_array( $sol->oferta_competencias ) ){
				
												throw new Exception("Uno de los perfiles solicitados no tiene definido las competencias" , 1);
										}
										else{

												$competencias_evaluadas = array();
												
												if( isset( $sol->oferta_competencias_evaluadas ) && is_array( $sol->oferta_competencias_evaluadas ) ){
														$competencias_evaluadas = (array)$sol->oferta_competencias_evaluadas;
												}

												foreach( $sol->oferta_competencias as $comp ){		
										
														$competencia = new Rec_Competencia_Evaluada();
						
														$competencia->setId( $comp );

														if( in_array( $comp, $competencias_evaluadas ) ){

																$competencia->setEsEvaluada( true );
																$competencia->setPuntajeMinimo(0);
																$competencia->setPuntajeMaximo(100);

														}


														$competencia->setEvaluadorId( null );

														$perfil->addCompetencia( $competencia );
												}

										}

						
										if( empty( $sol->oferta_documentos ) || !isset( $sol->oferta_documentos ) || !is_array( $sol->oferta_documentos ) ){
				
												throw new Exception("Uno de los perfiles solicitados no tiene definido la documentación requerida" , 1);
										}
										else{

												foreach( (array)$sol->oferta_documentos as $doc ){
										
														$documento = new Rec_Documento();
						
														$documento->setTipo( $doc );

														$perfil->addDocumento( $documento );
												}

										}

						
										if( empty( $sol->oferta_implementos ) || !isset( $sol->oferta_implementos ) || !is_array( $sol->oferta_implementos ) ){
				
										//throw new Exception("Uno de los perfiles requeridos no tiene definido la documentación requerida" , 1);
										}
										else{

												foreach( $sol->oferta_implementos as $impl ){		
										
														$implemento = new Implemento();
						
														$implemento->setId( $impl );

														$perfil->addImplemento( $implemento );
												}

										}
										
										if( empty( $sol->oferta_examenes ) || !isset( $sol->oferta_examenes ) || !is_array( $sol->oferta_examenes ) ){
				
										}
										else{

												foreach( $sol->oferta_examenes as $ex ){		
														
														$examen = new Rec_Examen_Evaluado();
						
														$examen->setId( $ex );

														$perfil->addExamen( $examen );
												}

										}

										$oferta->addPerfil( $perfil );

										if( empty( $array_ofertas ) || $publicacion_simple === false ){
												array_push( $array_ofertas, $oferta );
										}
										
						
								}
						}

				$array_id = array();

/*
				foreach( $array_ofertas as $o ){
						
						if( $oferta_id = $RRHH->registrarOfertaLaboral( $o ) ){

								array_push( $array_id, $oferta_id);
						}
						else{
								throw new Exception( $RRHH->getError() , 1);
						}

				}
 */

				try{
/*
	array_push( $array_ofertas['cargos'], 
												array(
														'nombre' => $sol->descripcion,
														'descripcion' => $sol->oferta_observacion,
														'competencias_evaluadas' => $sol->oferta_competencias_evaluadas,
														'competencias'=> $sol->competencias
												)
										);
 */
						if( $publicacion_simple ){
								
								$cargos = array();

								foreach( $array_fb['cargos'] as $k => $oferta ){

										array_push($cargos, $oferta['nombre'] );
								
								}

								$array_fb['imagen'] = $RRHH->crearImagenOfertaAgrupada( $oferta['descripcion'], $cargos );

						}else{
							
								foreach( (array)$array_fb['cargos'] as $k => $obj_of ){

										$eval = $obj_of['competencias_evaluadas'];

										$competencias = array_filter( (array)$obj_of['competencias'], function( $v, $k ) use ($eval){
												return in_array( $v->id , $eval );
										}, ARRAY_FILTER_USE_BOTH);

										$array_fb['cargos'][$k]['imagen'] = $RRHH->crearImagenOfertaIndividual( $obj_of['descripcion'], $obj_of['nombre'], array_column($competencias,'titulo') );
								
								}

						}

						$resp['imagenes'] = $array_fb;

					
						
						#posteo

						$token_acceso="EAAFympzK5c0BANqEUba7cXBAqANiPJkK4Ruf4nne7nBecUhzlgp5MPS5aKHQVFEbdM32DtDshEetLNjVlTjZArXTLR6VZBuzOcnB9Eh86h2v3b01zVNyv3AfQOXZBxtNp1HX9eDGhzdOGNhN5uCJBCcIcbSrNSeSCJRpjKARM39l05pIiOV";

						$pagina_id ='442410452618970' ;
						#pagina_id y token de acceso a pagina
						
						$graph_url ="https://graph.facebook.com/v12.0/".$pagina_id."/photos";
		
						if( $publicacion_simple ){
			
			
								$post_data ="url=https://www.todoacero.cl/gestop_backend_desarrollo/servicios/imagenes/".$array_fb['imagen']
										."&message=este es una prueba https://reclutamiento.todoacero.cl"
										."&access_token=".$token_acceso;

								$ch = curl_init();

								curl_setopt($ch, CURLOPT_URL, $graph_url);
								curl_setopt($ch, CURLOPT_HEADER, 0);
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
								
								$output = curl_exec($ch);
								
								curl_close($ch);
								
								$resp['fb'] = $output;

						}else{
							
								foreach( (array)$array_fb['cargos'] as $k => $obj_of ){
			
										$post_data ="url=https://www.todoacero.cl/gestop_backend_desarrollo/servicios/imagenes/".$obj_of['imagen']
										."&message=este es una prueba https://reclutamiento.todoacero.cl"
										."&access_token=".$token_acceso;
		
										$ch = curl_init();

										curl_setopt($ch, CURLOPT_URL, $graph_url);
										curl_setopt($ch, CURLOPT_HEADER, 0);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
								
										$output = curl_exec($ch);
								
										curl_close($ch);
										
										$resp['fb'] = $output;
								
								}

						}

	
				}
				catch ( Exception $x){
				
				}
						
								throw new Exception( 'aaaaahhhhh' , 1);

				if( count($array_id) >1 ){
						$resp['mensaje'] = 'Las ofertas han sido registradas con los folios :'.implode(', ', $array_id );
				}else{
						$resp['mensaje'] = 'La oferta ha sido registrada con el folio '.$array_id[0];
				}

				$solicitudes = $RRHH->getSolicitudesOfertaLaboralEstado( $estado = 3 );

				$resp['solicitudes'] = array_map(
						function ($sol){

								$cantidad = 0;
								
								return array(
										'id' => $sol->getId(),
										'usuario_nombre' => $sol->getUsuarioNombre(),
										'descripcion' => $sol->getDescripcion(),
										'fecha' => $sol->getFechaString(),
										'cantidad' => $sol->getCantidadPersonasSolicitadas(),
										'estado_id' => $sol->getEstadoId(),
										'estado_descripcion' => $sol->getEstadoDescripcion(),
								);
						}
				, $solicitudes );

				break;


			case 'reclutamiento_ofertas_publicadas':

					
					$Usuario = Usuario::LoginInvitado( 15924693 );

					$perfiles = $Usuario->getOfertasLaboralesPublicadas();
					
					$resp['ofertas'] = array_map( 

							function( $perfil ){

									return array(
											'oferta_id' =>	$perfil->getOfertaId(),
											'perfil_id' =>	$perfil->getId(),
										  'perfil_nombre' => $perfil->getDescripcion(),
											'fecha_cierre' => $perfil->getFechaReq(),
											'descripcion' => $perfil->getObservacion(),
											'cantidad' => $perfil->getCantidad(),
											'faena_nombre'=>$perfil->getFaenaNombre(),
											'area_nombre' =>	$perfil->getAreaNombre()
									);					

					}, $perfiles );

					$Empresa = new Empresa();
				
					$resp['faenas'] = array_map(
							function($f){ 
									return array(
											'id'=>$f->getId(), 
											'nombre'=>$f->getNombre() 
									);
							}, $Empresa->getFaenas() );
			
					$resp['perfiles'] = array_map(function($p){ 
						return array(
								'id'=>$p->getId(), 
								'descripcion'=> $p->getDescripcion(),
								'sueldo'=> $p->getSueldo(),
					 	);
				}, $Usuario->getPerfilesCargo() );

					break;
			
			case 'reclutamiento_aprobar_rechazar_solicitud_perfil':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);
				


				$sol_id = $post->sol_id;

				$cargos = (isset($post->cargos) && count($post->cargos)) ? $post->cargos : array();

				$cargos_aprobados = (isset($post->cargos_aprobados) && count($post->cargos_aprobados)) ? $post->cargos_aprobados : array();



				if($post->accion == 'rechazo'){
						
						if( ! $Usuario->rechazarSolicitudOfertaLaboralPendiente( $sol_id ) )
								throw new Exception('Ocurrio un error al intentar rechazar la solicitud',1);
						
						$resp['mensaje'] ="La solicitud fue rechazada";
				
				}else{
						
						foreach( (array)$cargos_aprobados as $c )
								$Usuario->cambiarEvaluadorCargoPerfilSolicitud( $c->id , $c->evaluador );
				
						if( ! $Usuario->aprobarSolicitudOfertaLaboralPendiente( $sol_id ) )
								throw new Exception('Ocurrio un error al intentar aprobar la solicitud',1);

						//quitar cargos rechazados
						foreach( (array)$cargos as $c ){
								$Usuario->rechazarCargoPerfilSolicitudOfertaLaboral( $c );
						}

						$resp['mensaje'] ="La solicitud fue aprobada";

				}

				
				$solicitudes = $Usuario->getSolicitudesPorAprobarOfertaLaboral( $solicitud = null );

				$resp['solicitudes'] = array_map(
						function ($sol){

								$perfiles = $sol->getPerfiles();

								$cantidad = 0;

								$detalle = array_map( 
										function( $p ) use ($cantidad) {

												return array(
														'id' => $p->getId(),
														'descripcion' => $p->getDescripcion(),
														'cantidad' => $p->getCantidad(),
														'faena_nombre' => $p->getFaenaNombre(),
														'area_nombre' => $p->getAreaNombre(),
														'observacion' => $p->getObservacion(),
														'turno_nombre' =>'7x7B',
														'habilitado' => true
												);

										}, (array)$perfiles);

								return array(
										'id' => $sol->getId(),
										'solicitante_nombre' => $sol->getUsuarioNombre(),
										'fecha' => $sol->getFechaString(),
										'cantidad' => $sol->getCantidadPersonasSolicitadas(),
										'estado_id' => $sol->getEstadoId(),
										'estado' => $sol->getEstadoDescripcion(),
										'perfiles' => $detalle
								);
						}
				, $solicitudes );


				break;

			
			case 'reclutamiento_rechazar_oferta':


				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				$sol_id = $post->sol_id;

						
				if( ! $RRHH->rechazarSolicitudOfertaLaboralAprobada( $sol_id ) )
							throw new Exception('Ocurrio un error al intentar rechazar la solicitud',1);
						
				$resp['mensaje'] ="La solicitud fue rechazada";
				
				$solicitudes = $RRHH->getSolicitudesOfertaLaboralEstado( $estado = 3 );

				$resp['solicitudes'] = array_map(
						function ($sol){

								$cantidad = 0;
								
								return array(
										'id' => $sol->getId(),
										'usuario_nombre' => $sol->getUsuarioNombre(),
										'descripcion' => $sol->getDescripcion(),
										'fecha' => $sol->getFechaString(),
										'cantidad' => $sol->getCantidadPersonasSolicitadas(),
										'estado_id' => $sol->getEstadoId(),
										'estado_descripcion' => $sol->getEstadoDescripcion(),
								);
						}
				, $solicitudes );
			

				break;
			
			case 'reclutamiento_postulaciones':

				
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				
				$Empresa = new Empresa();

				$resp['faenas'] = array_map(function($f){ 
						return array(
								'id'=>$f->getId(), 
								'nombre'=>$f->getNombre() 
						);
				}, $Empresa->getFaenas() );
				
				$resp['perfiles'] = array_map(function($p){ 
						return array(
								'id'=>$p->getId(), 
								'nombre'=> $p->getDescripcion(),
				//				'sueldo'=> $p->getSueldo()
					 	);
				}, $RRHH->getPerfilesCargo() );

				$resp['areas'] = $Empresa->getAreas();


				$perfiles = $RRHH->getOfertasLaboralesPublicadas();

				function contar( $contador, $puntaje ){
				
						$contador+= (is_numeric($puntaje))? 1 : 0;
						return $contador;
				}
					
					$resp['ofertas'] = array_map( 

							function( $perfil ) use ( $RRHH) {

									$postulantes = $RRHH->getPostulantes( $perfil->getId());

									$perfil_id = $perfil->getId();

									$lista = array_map(
											function( $p ) use ( $perfil_id ) {

													$etapas = $p->getListaEtapasPostulacion( $perfil_id );
													
													$completadas = array_reduce( array_column( $etapas,'puntaje') , "contar"	);
														
													$porcentaje = ( count($etapas)>0 ) ? ( $completadas/count($etapas) ) * 100  : 0 ;


													return array(
														'id' => $p->getId(),
														'postulacion_id' => $p->getPostulacionId(),
														'rut' => $p->getRut(),
														'dv' => $p->getDV(),
														'nombre' => $p->getNombre(),
														'apaterno' => $p->getApaterno(),
														'etapas' => $etapas,
														'porcentaje' => round( $porcentaje ),
														'ponderacion' => $p->getPonderacion()
													);
											}	
									, $postulantes);
									

									return array(
											'oferta_id' =>	$perfil->getOfertaId(),
											'perfil_id' =>	$perfil->getId(),
										  'perfil_nombre' => $perfil->getDescripcion(),
											'fecha_cierre' => $perfil->getFechaReq(),
											'descripcion' => $perfil->getObservacion(),
											'cantidad' => $perfil->getCantidad(),
											'faena_nombre'=>$perfil->getFaenaNombre(),
											'area_nombre' =>	$perfil->getAreaNombre(),
										  'solicitante' => $perfil->getSolicitanteNombre(),
											'postulantes' =>$lista,
									);					

					}, $perfiles );
					
					//$p = $RRHH->getPostulantes();

					break;
			
			
			case 'reclutamiento_postulaciones_epp':

				
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				$ofertas = $RRHH->getOfertasLaboralesPublicadas();
			
				$resp['ofertas'] = array_map( 

							function( $oferta_perfil ) use ( $RRHH) {
								
									$postulantes = $RRHH->getPostulantesEtapa( $oferta_perfil->getId() , $reta_id = 4 );
									

									$postulantes = array_filter( $postulantes, 
											function( $v, $k) {
												return true;	
													return $v['aprobaciones'] >0 && $v['reprobaciones'] == 0 ;
												
											}, ARRAY_FILTER_USE_BOTH);


									$postulantes = array_map(
											function( $p ) {
												
													$postulante = new Rec_Postulante();
													
													$postulante->setId( $p['id']);

													$postulante->setDatos();

													return array_merge(
														
															$p,
															array(
																'sexo' => $postulante->getSexoId(),	
																'talla_pantalon' => $postulante->getTallaPantalon(),	
																'talla_camisa' => $postulante->getTallaCamisa(),	
																'talla_calzado' => $postulante->getTallaCalzado(),	
														
															)
														);															
											}

									, $postulantes);

									$perfil_evaluado = new Rec_Perfil_Evaluado();

									$perfil_evaluado->setId( $oferta_perfil->getId() );

									$perfil_evaluado->setImplementos();

									$implementos = array_map(
											function($i){

													return array(
														'id'=> $i->getId(),
														'tipo_id'=> $i->getTipoId(),
														'descripcion'=> $i->getDescripcion(),
														'tipo_descripcion'=> $i->getTipoDescripcion(),
														'icono' => $i->getIcono(),
													);
											},
											$perfil_evaluado->getImplementos()
									);

									return array(
											'oferta_id' =>	$oferta_perfil->getOfertaId(),
											'perfil_id' =>	$oferta_perfil->getId(),
										  'perfil_nombre' => $oferta_perfil->getDescripcion(),
											'fecha_cierre' => $oferta_perfil->getFechaReq(),
											'descripcion' => $oferta_perfil->getObservacion(),
											'cantidad' => $oferta_perfil->getCantidad(),
											'faena_nombre'=>$oferta_perfil->getFaenaNombre(),
											'area_nombre' =>	$oferta_perfil->getAreaNombre(),
										  'solicitante' => $oferta_perfil->getSolicitanteNombre(),
											'implementos' => $implementos,
											'postulantes' =>$postulantes,
									);					

					}, $ofertas );
					
	//				$p = $RRHH->getPostulantes();

					break;

			case 'reclutamiento_postulante_epp':

				
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				$postulante_id = $post->postulante_id;

				$perfil_id = $post->perfil_id;

				$postulante = new Rec_Postulante();
													
				$postulante->setId( $postulante_id );

				$postulante->setDatos();
				
				$resp['medidas'] = array(
																'sexo' => $postulante->getSexoId(),	
																'talla_pantalon' => $postulante->getTallaPantalon(),	
																'talla_camisa' => $postulante->getTallaCamisa(),	
																'talla_calzado' => $postulante->getTallaCalzado(),	
														);

				$perfil_evaluado = new Rec_Perfil_Evaluado();

				$perfil_evaluado->setId( $perfil_id );

				$perfil_evaluado->setImplementos();

				$resp['implementos'] = array_map(
											function($i){

													return array(
														'id'=> $i->getId(),
														'tipo_id'=> $i->getTipoId(),
														'descripcion'=> $i->getDescripcion(),
														'tipo_descripcion'=> $i->getTipoDescripcion(),
														'icono' => $i->getIcono(),
													);
											},
											$perfil_evaluado->getImplementos()
									);



				break;


			case 'reclutamiento_postulaciones_etapa':

				
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);
				
				
				$Empresa = new Empresa();

				$resp['faenas'] = array_map(function($f){ 
						return array(
								'id'=>$f->getId(), 
								'nombre'=>$f->getNombre() 
						);
				}, $Empresa->getFaenas() );
				
				$resp['perfiles'] = array_map(function($p){ 
						return array(
								'id'=>$p->getId(), 
								'nombre'=> $p->getDescripcion(),
				//				'sueldo'=> $p->getSueldo()
					 	);
				}, $RRHH->getPerfilesCargo() );

				$resp['areas'] = $Empresa->getAreas();


				//$recetatip_id = intval( $post->tipo_etapa );
				$reta_id = intval( $post->etapa_id );

				$recetatip_id = null;

				$postulantes = $RRHH->getPostulantesEtapa( null , $reta_id  );
				
				//if( $recetatip_id == 3 ){
				if( $reta_id == 3 ){

						$postulantes = array_map(
								function( $p ){
										
										$obj = new Rec_Postulante();
										$obj->setId( $p['id']);
										$p['inducciones'] = $obj->getListaInduccionesGeneralesPostulacion( $p['perfil_id'] );
										
										return $p;						

								}, (array)$postulantes);

						$resp['inducciones_programadas'] = $RRHH->getListaInduccionesGeneralesProgramadas(); 

				}
				else if( $reta_id == 5 ){

						$postulantes = array_map(
								function( $p ) use ( $recetatip_id ){
										
										$obj = new Rec_Postulante();
										$obj->setId( $p['id']);
										$p['inducciones'] = $obj->getListaInduccionesEspecificasPostulacion( $p['perfil_id'] );
										
										return $p;						

								}, (array)$postulantes);

						$resp['inducciones_programadas'] = $RRHH->getListaInduccionesEspecificasProgramadas(); 
				}
				else if( $reta_id == 7 ){

						$resp['correos_sgcas'] = $RRHH->getListaCorreoSGCASpostulacion();

						$resp['correos_wkmt'] = $RRHH->getListaCorreoWKMTpostulacion();
						
						$RRHH->setDatos();
						
						$resp['correo_usuario'] = $RRHH->getEmail();

						$postulantes = array_map(function( $p ){
								
								$obj = new Rec_Postulante();
								
								$obj->setId( $p['id']);

								$subetapas = $obj->getListaSubEtapasSGCASPostulacion( $p['perfil_id'] );

								return array_merge($p, array( 'subetapas' => $subetapas ) );

						}, $postulantes );
				}
				else if( $reta_id == 8){
						
						$postulantes = array_map(function( $p ){
								
								$rut = $p['rut'];

								$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut/contrato";

								$archivos = 0;

								if( file_exists( $ruta_archivo ) ){
										
										if ($gestor = opendir( $ruta_archivo )) {
								
												
												while (false !== ($entrada = readdir($gestor))) {
												
														if( !in_array( $entrada, array('.','..')) )
																$archivos++;
												}
										}
 
										closedir($gestor);
								
								}
										
								
								return array_merge($p, array( 'documentos' => $archivos ) );

						}, $postulantes );


				
				}
				else if( $reta_id == 12 ){
				//else if( $recetatip_id == 10 ){

						$postulantes = array_map(
								function( $p ) use ( $recetatip_id ){
										
										$obj = new Rec_Postulante();
										$obj->setId( $p['id']);
										$p['cursos'] = $obj->getListaCursosOpeConductores( $p['perfil_id'] );
										
										return $p;						

								}, (array)$postulantes);

						$resp['cursos_programados'] = $RRHH->getListaCursosOpeConductoresProgramados(); 
				}

				$resp['postulantes']= $postulantes;

					break;
			
			case 'reclutamiento_postulante_inducciones':

				
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);
				

				//$recetatip_id = intval( $post->tipo_etapa );
				$reta_id = intval( $post->etapa_id );

				$perfil_id = intval( $post->perfil_id );

				$postulante_id = intval( $post->postulante_id );


				$obj = new Rec_Postulante();

				$obj->setId( $postulante_id );



				//if( $recetatip_id == 3 )									
				if( $reta_id == 3 )									
						$resp['inducciones'] = $obj->getListaInduccionesGeneralesPostulacion( $perfil_id );

				//else if( $recetatip_id == 5 )
				else if( $reta_id == 5 )
						$resp['inducciones'] = $obj->getListaInduccionesEspecificasPostulacion( $perfil_id );
				//else if( $recetatip_id == 10 )
				else if( $reta_id == 12 )
						$resp['inducciones'] = $obj->getListaCursosOpeConductores( $perfil_id );
				

				
				break;


			
			
			
			
			case 'reclutamiento_guardar_etapa_postulante':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);


				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				if( !isset($post->fecha_hora) || empty($post->fecha_hora) )
						
						throw new Exception("Fecha invalida",1);

				
				$pdfdoc = false;

				if(isset( $_FILES['archivo'])){
						$pdfdoc= true;
				}


				$postulante_id = $post->postulante_id;

				$rocpeta_id = $post->rocpeta_id;
				
				$procp_id = $post->procp_id;
				
				$puntaje = ( !isset( $post->puntaje ) || (empty($post->puntaje) && $post->puntaje != 0 )  || !is_numeric($post->puntaje) ) ? null : $post->puntaje ;
				
				$aprobado = null;
				
				if( isset( $post->aprobado ) && is_numeric( $post->aprobado ) ){
						$aprobado = $post->aprobado;
				}

				$fecha_hora = $post->fecha_hora;

				$observacion = ( isset( $post->observacion) )? $post->observacion : null;
				
				$obs_puntaje = ( isset( $post->obs_puntaje) )? $post->obs_puntaje : null;
				
				$direccion = ( isset( $post->direccion) )? $post->direccion : null;

				$link = ( isset($post->link))? $post->link : null;
				
				$tipo = ( isset($post->tipo) )? $post->tipo : null;
				
				$cargo = ( isset($post->cargo) )? $post->cargo : null;

				$enviar_correo = ( isset($post->enviar_correo) && ( $post->enviar_correo===true || $post->enviar_correo==='true' || $post->enviar_correo===1 ) )? true : false;

				$r = $Usuario->registrarEtapaPostulante( $rocpeta_id, $procp_id, $puntaje, $obs_puntaje, $fecha_hora, $direccion, $link , $observacion, $aprobado );

				//if( ! $id = $Usuario->registrarEtapaPostulante( $rocpeta_id, $procp_id, $puntaje, $obs_puntaje, $fecha_hora, $direccion, $link , $observacion, $aprobado ) )
				if( ! $r || !isset( $r['id'] ) )
						throw new Exception("No fue posible registrar la informacion", 1);

				$id = $r['id'];

				//$resp['out']= $r;
				#si la proxima etapa es pcr covid
				if( $r['solicitar_examen'] == 'SI' && $aprobado==1 ){
				
				
						$Usuario->registrarSolicitudExamenCOVID19Postulante( $procp_id );
						
						$obj = new Rec_Postulante();
						
						$obj->setId( $postulante_id );
						
						$obj->setDatos();
				
						$rut = $obj->getRut().'-'.$obj->getDV();
		
						$nombre = $obj->getNombre().' '.$obj->getApaterno().' '.$obj->getAmaterno() ;

						$celular = $obj->getTelCelular();

						$correos = $Usuario->getListaCorreoCOVIDpostulacion();
						
						$body ="Estimado.<br><br> 
										Se requiere realizar examen COVID-19 para <strong>".$nombre."</strong> postulante al cargo 
										<strong>".$cargo."</strong>.
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
						
						$subject = "Solicitud Examen COVID";

						$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
			
						foreach( (array)$correos as $c ){
									
									$mail->addAddress($c['correo'], '');
							
						}
						//$mail->addAddress($email, '');
						$mail->isHTML(true);
						$mail->Subject = $subject;
						$mail->Body = utf8_decode($tabla);

						$mail->send();
						
						$enviar_correo = false;

				}

				if( isset( $post->competencias ) && is_array( $post->competencias ) && !empty( $post->competencias ) ){

						foreach( (array)$post->competencias as $comp ){
								if( ! $Usuario->registrarEvaluacionCompetenciaPostulante( $comp->id, $procp_id, $comp->puntaje , null ) )
										throw new Exception("No fue posible ingresar la evaluacion de la competencia");

						}

				}
				
				if( isset( $post->examenes ) && !empty( $post->examenes ) ){

						$examenes = json_decode( $post->examenes );

						foreach( (array)$examenes as $ex ){
						
								$apto = ( !empty($ex->apto) ) ? 1 : 0 ;

								if( ! $Usuario->registrarEvaluacionExamenPostulante( $procp_id, $ex->id, $apto ) )
										throw new Exception("No fue posible ingresar la evaluacion del examen");

						}

				}

				if( $enviar_correo ){
						
						$Postulante = new Rec_Postulante();

						$Postulante->setId( $postulante_id );
				
						$Postulante->setDatos();

						$email= $Postulante->getEmail();

						$resp['email']=$email;
				
						$fecha= date('d-m-Y');

						switch ( $tipo ){
						
						case 1:
							

								if( empty($link) ){
										$forma = ' forma presencial en <strong>'.$direccion.'</strong>.';
								}
								else{
										$forma = ' forma virtual a traves del siguiente link <strong>'.$link.'</strong>.';
								}

								list($f, $h) = explode( ' ',$fecha_hora );
								list($y,$m,$d) = explode( '-',$f);
								$fecha_hora = $d.'-'.$m.'-'.$y.' '.$h;

								$subject ="Entrevista tecnica";

								$body ="Estimado postulante.<br><br> 
										Se informa que en base a su postulacion para el cargo 
										<strong>".$cargo."</strong>, se agendo 
										entrevista tecnica para la fecha".
										" <strong>".$fecha_hora."</strong>, la cual se realizara de 
										".$forma."<br>";

								if( !empty($observacion) )
										$body.="<br><strong>Obs:</strong> ".$observacion.".";

								$body.="<br><br>Saludos cordiales.";


								break;

						case 2:
							
								$subject ="Examen preocupacional";

								list($f, $h) = explode( ' ',$fecha_hora );
								list($y,$m,$d) = explode( '-',$f);
								$fecha_hora = $d.'-'.$m.'-'.$y.' '.$h;

								$body ="Estimado postulante.<br><br> 
										Se informa que en base a su postulacion para el cargo 
										<strong>".$cargo."</strong>, se agendo 
										examen preocupacional para la fecha".
										" <strong>".$fecha_hora."</strong>, la cual se realizara de 
										forma presencial en <strong>".$direccion."</strong>.<br>";

								if( !empty($observacion) )
										$body.="<br><strong>Obs:</strong> ".$observacion.".";

								$body.="<br><br>Saludos cordiales.";


								break;
	
						case 3:
							

								if( empty($link) ){
										$forma = ' forma presencial en <strong>'.$direccion.'</strong>.';
								}
								else{
										$forma = ' forma virtual a traves del siguiente link <strong>'.$link.'</strong>.';
								}

								$subject ="Entrevista RRHH";

								$body ="Estimado postulante.<br><br> 
										Se informa que en base a su postulacion para el cargo 
										<strong>".$cargo."</strong>, se agendo 
										entrevista de Recusrsos Humanos para la fecha".
										" <strong>".$fecha_hora."</strong>, la cual se realizara de ".$forma.".<br>";

								if( !empty($observacion) )
										$body.="<br><strong>Obs:</strong> ".$observacion;

								$body.="<br><br>Saludos cordiales";

								break;


						default:
								
								$subject ="Nueva etapa";

								$body ="Estimado postulante.<br><br> 
										Se informa que en base a su postulacion para el cargo 
										<strong>".$cargo."</strong>, se agendo 
										examen preocupacional para la fecha".
										" <strong>".$fecha_hora."</strong>, la cual se realizara de 
										forma presencial en <strong>".$direccion."</strong>
										<br><br>Saludos cordiales.";

								break;
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
			
						$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
						$mail->addAddress($email, '');
						$mail->isHTML(true);
						$mail->Subject = $subject;
						$mail->Body = utf8_decode($tabla);


						if( $pdfdoc ){
								$mail->AddAttachment( $_FILES['archivo']['tmp_name'], $_FILES['archivo']['name'], 'base64', 'application/pdf');
						}

						$mail->send();
				}

				break;
			
			case 'reclutamiento_guardar_subetapa_postulante':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				if( !isset($post->postulantes) || !is_array( $post->postulantes ) || empty($post->postulantes) )
						throw new Exception("El listado de postulantes esta vacio");

				$postulantes = $post->postulantes;

				$puntaje = null;
				
				$aprobado = null;


				foreach( (array)$postulantes as $p ){
						
						$post_id = $p->post_id;

						$rocp_id = $p->rocp_id;

						//$etapa_tipo = $p->etapa_tipo;
						$reta_id = $p->etapa_id;

						$subetapas = count($p->subetapas);
						$subetapas_aprobadas = 0;

						foreach( (array)$p->subetapas as $s){

								$postofeeta_id = $s->postofeeta_id;
								$rseta_id = $s->rseta_id;
								$aprobado = ( $s->aprobado == true ||  $s->aprobado == 1  ) ? 1 : 0 ;

								$Usuario->registrarSubEtapaPostulante( $postofeeta_id, $rseta_id, $aprobado );

								if( $aprobado )
										$subetapas_aprobadas++;

						}

						if( $subetapas == $subetapas_aprobadas )
								$Usuario->registrarEtapaExternaPostulante( $post_id, $rocp_id, $reta_id , $puntaje=100, $aprobado=1 );
				}

				
				break;


			case 'reclutamiento_aprobar_reprobar_etapa_postulante':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				if( !isset($post->postulantes) || !is_array( $post->postulantes ) || empty($post->postulantes) )
						throw new Exception("El listado de postulantes esta vacio");

				
				$postulantes = $post->postulantes;

				$puntaje = null;
				
				$aprobado = null;


				foreach( (array)$postulantes as $p ){
						
						$post_id = $p->post_id;

						$procp_id = $p->procp_id;

						$rocp_id = $p->rocp_id;

						$rocpeta_id = $p->rocpeta_id;

						//$etapa_tipo = $p->etapa_tipo;
						
						$aprobado = ( isset($p->aprobado) && intval( $p->aprobado )===1 ) ? 1 : 0 ;

						$puntaje = ( isset($p->puntaje) && is_numeric( $p->puntaje ) ) ? $p->puntaje : 0 ;

						$fecha_hora = date('Y-m-d H:i');

						//$Usuario->registrarEtapaExternaPostulante( $post_id, $rocp_id, $etapa_tipo , $puntaje , $aprobado );
						
						$Usuario->registrarEtapaPostulante( $rocpeta_id, $procp_id, $puntaje, $descripcion=null, $fecha_hora, $direccion=null, $link=null, $observacion=null , $aprobado );
				}

				
				break;
				

			case 'reclutamiento_aprobar_etapa_externa_postulante':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


				if( !isset($post->postulantes) || !is_array( $post->postulantes ) || empty($post->postulantes) )
						throw new Exception("El listado de postulantes esta vacio");

				
				$postulantes = $post->postulantes;

				$puntaje = null;
				
				$aprobado = null;


				foreach( (array)$postulantes as $p ){
						
						$post_id = $p->post_id;

						$rocp_id = $p->rocp_id;

						//$etapa_tipo = $p->etapa_tipo;
						$reta_id = $p->etapa_id;

						$Usuario->registrarEtapaExternaPostulante( $post_id, $rocp_id, $reta_id , $puntaje=100, $aprobado=1 );
				}

				
				break;
				
			case 'reclutamiento_solicitar_etapa_integracion_postulante':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				if( !isset($post->postulantes) || !is_array( $post->postulantes ) || empty($post->postulantes) )
						throw new Exception("El listado de postulantes esta vacio");

				$postulantes = $post->postulantes;

				$enviar_correo = true;
				$puntaje = null;
				
				$aprobado = null;

				$mensaje = $post->mensaje;

				$resp['procesados']=0;

				//$tabla ="<table>";
				$tabla ="<table>";
				$tabla .="<thead>";
				$tabla .="<tr>";
				$tabla .="<th colspan='3'>";
				$tabla .="<center><img src='https://todoacero.cl/images/logo.jpg'/></center>";
				$tabla .="</th>";
				$tabla .="</tr>";
				
				$tabla .="<tr>";
				$tabla .="<th colspan='3' style='padding:20px;font-weight:normal'>";
				$tabla .=$post->mensaje;
				$tabla .="</th></tr>";

				$tabla .="<tr  style='background:#00695C; color:white' >";
				$tabla .="<th>NOMBRE</th>";
				$tabla .="<th>ETAPA</th>";
				$tabla .="<th>FAENA</th>";
				$tabla .="</tr>";
				$tabla .="</thead>";
				$tabla .="<tbody>";
				
				
				$enviar_correo= false;

				$i = 0;

				foreach( (array)$postulantes as $p ){
						
						$resp['procesados']++;

						$rseta_id = isset($p->rseta_id) ? $p->rseta_id : null;
						
						$postofeeta_id = isset($p->postofeeta_id) ? $p->postofeeta_id : null;

						$subetapa_desc = isset( $p->subetapa) ? $p->subetapa : null ;


						if( !empty($rseta_id) && !empty($postofeeta_id) ){

								$Usuario->registrarSolicitudSubEtapaPostulante( $postofeeta_id, $rseta_id, $mensaje = null);
								$color= $i%2==0 ? '#E0F2F1' : '#B2DFDB';
								$tabla .="<tr style='background:".$color."'>";
								$tabla .="<td style='padding: 4px 8px; color:black'>".$p->nombre."</td>";
								$tabla .="<td style='padding: 4px 8px; color:black'>".$p->subetapa."</td>";
								$tabla .="<td style='padding: 4px 8px; color:black'>".$p->faena."</td>";
								$tabla .="</tr>";

								$enviar_correo = true;
						
								$i++;
						}

				}

				$tabla .="</tbody>";
				$tabla .="</table>";
				

				if( $enviar_correo ){
						
				
						$fecha= date('Y-m-d');

							
						$subject ="Solicitud para habilitacion de personal";

//						$body = $post->mensaje."<br><br>".$tabla;

						$body= $tabla;

						$mail = new PHPMailer(TRUE);
			
						$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');

						foreach((array)$post->correos as $correo ){
								$mail->addAddress($correo, '');
						}

						if( isset($post->correo_cc) )
								$mail->addCC( $post->correo_cc );

						$mail->isHTML(true);
						$mail->Subject = $subject;
						$mail->Body = utf8_decode($body);

/*
						if( $pdfdoc ){
								$mail->AddAttachment( $_FILES['archivo']['tmp_name'], $_FILES['archivo']['name'], 'base64', 'application/pdf');
						}
 */
						$mail->send();
				}

				break;

		
			case 'reclutamiento_solicitar_etapa_integracion_postulante_2':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				if( !isset($post->postulantes) || !is_array( $post->postulantes ) || empty($post->postulantes) )
						throw new Exception("El listado de postulantes esta vacio");

				$postulantes = $post->postulantes;

				$enviar_correo = true;
				$puntaje = null;
				
				$aprobado = null;

				$mensaje = $post->mensaje;

				$tipo_etapa= $post->tipo_etapa;

				$resp['procesados']=0;

				$tabla ="<table>";
				$tabla .="<thead>";
				$tabla .="<tr>";
				$tabla .="<th colspan='2'>";
				$tabla .="<center><img src='https://todoacero.cl/images/logo.jpg'/></center>";
				$tabla .="</th>";
				$tabla .="</tr>";
				
				$tabla .="<tr>";
				$tabla .="<th colspan='2' style='padding:20px;font-weight:normal'>";
				$tabla .=$mensaje;
				$tabla .="</th></tr>";

				$tabla .="<tr style='background:#00695C; color:white'>";
				$tabla .="<th>NOMBRE</th>";
				$tabla .="<th>FAENA</th>";
				$tabla .="</tr>";
				$tabla .="</thead>";
				$tabla .="<tbody>";
/*	
				$tabla ="<table style='background:#00695C; color:white'>";
				$tabla .="<thead>";
				$tabla .="<tr>";
				$tabla .="<th>NOMBRE</th>";
				$tabla .="<th>FAENA</th>";
				$tabla .="</tr>";
				$tabla .="</thead>";
				$tabla .="<tbody>";
 */
				$enviar_correo= false;

				$i = 0;

				foreach( (array)$postulantes as $p ){
						
						$resp['procesados']++;

						$post_id = isset($p->post_id) ? $p->post_id : null;
						
						$rocp_id = isset($p->rocp_id) ? $p->rocp_id : null;

						//$tipo_etapa = isset( $p->) ? $p->tipo_etapa : null ;


						if( !empty($post_id) && !empty($rocp_id) ){
								
								$Usuario->registrarEtapaExternaPostulante( $post_id, $rocp_id, $tipo_etapa, $puntaje=null, $aprobado= null );
								
								$Usuario->registrarSolicitudEtapaPostulante( $post_id, $rocp_id, $tipo_etapa, $mensaje);
								
								$color= $i%2==0 ? '#E0F2F1' : '#B2DFDB';
								$tabla .="<tr style='background:".$color."'>";
								$tabla .="<td style='padding: 4px 8px; color:black'>".$p->nombre."</td>";
								$tabla .="<td style='padding: 4px 8px; color:black'>".$p->faena."</td>";
								$tabla .="</tr>";

								$enviar_correo = true;
						
								$i++;
						}

				}

				$tabla .="</tbody>";
				$tabla .="</table>";
				

				if( $enviar_correo ){
						
				
						$fecha= date('Y-m-d');

							
						$subject ="Solicitud para habilitacion de personal";

						$body = $post->mensaje."<br><br>".$tabla;

								
						$mail = new PHPMailer(TRUE);
			
						$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');

						foreach((array)$post->correos as $correo ){
								$mail->addAddress($correo, '');
						}

						if( isset($post->correo_cc) )
								$mail->addCC( $post->correo_cc );

						$mail->isHTML(true);
						$mail->Subject = $subject;
						$mail->Body = utf8_decode($body);

/*
						if( $pdfdoc ){
								$mail->AddAttachment( $_FILES['archivo']['tmp_name'], $_FILES['archivo']['name'], 'base64', 'application/pdf');
						}
 */
						$mail->send();
				}

				break;



			case 'reclutamiento_obtener_etapa':
					
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);
				
					if( !isset($post->perfil_id) || !is_numeric($post->perfil_id) )
						throw new Exception("Identificador del perfil no valido",1);

				
					if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


					$postulante_id = $post->postulante_id;
					
					$perfil_id = $post->perfil_id;

					$rocpeta_id = $post->rocpeta_id;

					$postulante = new Rec_Postulante();

					$postulante->setId( $postulante_id );

					$etapas = $postulante->getListaEtapasPostulacion( $perfil_id );

					$etapas = array_filter( $etapas, function( $v, $k) use ($rocpeta_id){
						return $v['id']== $rocpeta_id;
					}, ARRAY_FILTER_USE_BOTH);

					$resp['etapa']= array_pop($etapas);

					$resp['examenes'] = $postulante->getListaExamenesPostulacion( $perfil_id );
					
					$resp['competencias_tecnicas'] = $postulante->getListaCompetenciasTecnicasPostulacion( $perfil_id );
					$resp['competencias_blandas'] = $postulante->getListaCompetenciasBlandasPostulacion( $perfil_id );

					break;
		
			case 'reclutamiento_obtener_postulantes_induccion':
					
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

				
					if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

					$ind_id = $post->ind_id;
					
					$fecha = $post->fecha;

					$resp['postulantes'] = $Usuario->getListaPostulantesInduccion( $ind_id, $fecha );

					break;

			case 'reclutamiento_guardar_inducciones_generales':
					
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

				
					if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


					if( empty( $post->postulantes ) || empty( $post->postulantes ) || !is_array( $post->postulantes ) )
							throw new Exception("no hay postulantes para buscar");


							
					foreach( (array)$post->postulantes as $postulante ){


							$rocpeta_id = $postulante->rocpeta_id;
							
							$post_id = $postulante->postulante_id;

							$inducciones = array();

							foreach( $postulante->inducciones as $induccion ){
								
								
									$rind_id = $induccion->id;

									if( !isset($induccion->fecha_hora ) )
										$fecha_hora = $induccion->fecha.' '.$induccion->hora.':'.$induccion->minuto;
									else
										$fecha_hora = $induccion->fecha_hora;

									$direccion = null;
									
									$link=null;

								
									if( isset( $induccion->presencial) ){

											if( $induccion->presencial == true )
													$direccion = $induccion->direccion;
											else
													$link= $induccion->direccion;							
									
									}
									
									if( !empty($fecha_hora) && ( !empty($direccion) || !empty($link) ) ){
											$Usuario->registrarInduccionPostulante( 
																$rocpeta_id,
																$post_id,
																$rind_id,
																$fecha_hora, 
																$direccion , 
																$link );

									list($f, $h) = explode( ' ', $fecha_hora );
										
									list( $y, $m, $d ) = explode( '-' , $f );
									
									$f = $d.'-'.$m.'-'.$y;

										array_push( $inducciones, array( 
												'presencial'=> $induccion->presencial,
												'direccion' => $induccion->direccion,
												'fecha'=> $f,
												'hora' => $h
									 	));

									}

						}
							/* envio de correo*/

						$body = "";

						if( count( $inducciones ) > 1 ){
								
								$body ="<table>";
								$body.="<thead>";
								$body.="<tr>";
								$body.="<th colspan='4'>";
								$body.="<center><img src='https://todoacero.cl/images/logo.jpg'/></center>";
								$body.="</th>";
								$body.="</tr>";
								
								$body.="<tr>";
								$body.="<th colspan='4' style='padding:20px;font-weight:normal'>";
								$body.="Estimado postulante, a continuacion le presentamos la calendarizacion de sus proximas inducciones:<br>";								
								$body.="</th></tr>";

								$body.="<tr style='background:#00695C; color:white'>";
								$body.="<th>Asistencia</th>";
								$body.="<th>Link o ubicacion</th>";
								$body.="<th>Dia</th>";
								$body.="<th>Hora</th>";
								$body.="</thead>";

								$body.="<tbody>";

								$i=0;
								foreach( $inducciones as $ind){
										
										$color= $i%2==0 ? '#E0F2F1' : '#B2DFDB';
										$body.="<tr style='background:".$color."'>";
										$body.="<td style='padding: 4px 8px; color:black'>".( $ind['presencial']? 'Presencial' :'Virtual' )."</td>";
										$body.="<td style='padding: 4px 8px; color:black'>".$ind['direccion']."</td>";
										$body.="<td style='padding: 4px 8px; color:black'>".$ind['fecha']."</td>";
										$body.="<td style='padding: 4px 8px; color:black'>".$ind['hora']."</td>";	
										$body.="</tr>";
										$i++;
								}
								$body.="</tbody>";
								$body.="</table>";
						
						}else{
								
								$body ="<table>";
								$body.="<thead>";
								$body.="<tr>";
								$body.="<th>";
								$body.="<center><img src='https://todoacero.cl/images/logo.jpg'/></center>";
								$body.="</th>";
								$body.="</tr>";
								
								$body.="</thead>";

								$body.="<tbody>";
								
								$body.="<tr>";
								$body.="<td style='padding:20px;font-weight:normal'><p>";



								if( $inducciones[0]['presencial'] )
										$body.="Estimado postulante, para su proxima 
														induccion debe presentarse en 
														<strong>".$inducciones[0]['direccion']."</strong> 
														el dia <strong>".$inducciones[0]['fecha']."</strong> 
														a las <strong>".$inducciones[0]['hora']."</strong>."; 
								else{
										$body.="Estimado postulante, para su proxima 
														induccion debe asistir de forma virtual mediante el siguiente link 
														<strong>".$inducciones[0]['direccion']."</strong> 
														el dia <strong>".$inducciones[0]['fecha']."</strong> 
														a las <strong>".$inducciones[0]['hora']."</strong>.";
								}

								$body.="</p></td></tr>";
								$body.="</tbody>";

								$body.="</table>";
						
						}	
						

						$pt = new Rec_Postulante();
						$pt->setId( $post_id );
						$pt->setDatos();

						$email = $pt->getEmail();

						$mail = new PHPMailer(TRUE);
			
						$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
						$mail->addAddress($email, '');
						$mail->isHTML(true);
						$mail->Subject = 'Proxima induccion';
						//$body = "Estimado postulante, se ha fijado su próxima induccion para el ".$f."a las ".$h;
						$mail->Body = utf8_decode($body);

						$mail->send();

						unset( $pt );

					}
 


					break;



			case 'reclutamiento_guardar_induccion_evaluacion':
					
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

				
					if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);


					if( empty( $post->postulantes ) || empty( $post->postulantes ) || !is_array( $post->postulantes ) )
							throw new Exception("no hay postulantes para registrar la evaluacion");


					//$recetatip_id = isset( $post->tipo_etapa ) ? $post->tipo_etapa : null ;
					$reta_id = isset( $post->etapa_id ) ? $post->etapa_id : null ;
							
				
					$correos = $Usuario->getListaCorreoCOVIDpostulacion();
					
					$postulantes_aprobados = array();

					//throw new Exception('probando');
					foreach( (array)$post->postulantes as $postulante ){


							$induccion_id = $postulante->id; //postofeind_id
							
							$evaluacion = $postulante->evaluacion;
							
							$rocpeta_id = $postulante->rocpeta_id;
							
							$procp_id = $postulante->procp_id;
							
							$post_id = $postulante->post_id;

							$faena_nombre = $postulante->faena_nombre;
//						throw new Exception($induccion_id.' / '. $evaluacion.' / '. $rocpeta_id );	
							$estado =	$Usuario->registrarInduccionEvaluacion($induccion_id, $evaluacion, $rocpeta_id );
								//$estado=1;

							//if( $recetatip_id==5 && 1 == $estado ){
							if( $reta_id==5 && 1 == $estado ){
								  
									array_push( $postulantes_aprobados, 
											array( 'post_id' => $post_id, 'faena_nombre'=>$faena_nombre ) );

									$Usuario->registrarSolicitudExamenCOVID19Postulante( $procp_id );

							}


					}
 

				  if( $reta_id==5 && count($correos) && count($postulantes_aprobados) ){	
							
							require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';
							
							$archivo = 'FORMATO_CARGA_EXAMENES.xlsx';
							
							$inputFileType = PHPExcel_IOFactory::identify($archivo);
							
							$objReader = PHPExcel_IOFactory::createReader($inputFileType);
							
							$objReader->setLoadAllSheets();
							
							$objPHPExcel = $objReader->load($archivo);
							
							$objPHPExcel->setActiveSheetIndex(0);
							

							$row = 3;

							foreach( $postulantes_aprobados as $p){
									$obj = new Rec_Postulante();
									$obj->setId( $p['post_id'] );
									$obj->setDatos();

									$objPHPExcel->getActiveSheet()
											->setCellValue('B'.$row, $obj->getRut().'-'.$obj->getDV() )
											->setCellValue('C'.$row, $obj->getNombre().' '.$obj->getApaterno().' '.$obj->getAmaterno() )
											->setCellValue('E'.$row, $obj->getTelCelular() )
											->setCellValue('K'.$row, 'PCR' )
											->setCellValue('I'.$row, $p['faena_nombre'] )
									;

							}
							
							$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
							
							ob_start();
							
							$objWriter->save('php://output');
							
							$data = ob_get_contents();
							
							ob_end_clean();
	 
							//$data = $objWriter->getExcelData();

							$body="Por aprobacion de inducciones es necesario realizar examen correspondiente de COVID-19 al listado adjunto en formato Excel. ";

							$mail = new PHPMailer(TRUE);
			
							$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
									
							$mail->isHTML(true);
									
							$mail->Subject = 'Test COVID-19 para postulante';
									
							$mail->Body = utf8_decode($body);

							foreach( (array)$correos as $c ){
									
									$mail->addAddress($c['correo'], '');
							
							}

							
							$mail->AddStringAttachment($data, 'postulantes.xlsx');				
						
							$mail->send();

							unset($mail);
							unset($objWriter);
							unset($data);
							unset($objPHPExcel);

					}

					break;

			case 'reclutamiento_perfil_pdf':
					
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);
				
					if( !isset($post->perfil_id) || !is_numeric($post->perfil_id) )
						throw new Exception("Identificador del perfil no valido",1);

				
					if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

					$post_id = isset($post->post_id) ? $post->post_id : null;
					
					$perfil = new Rec_Perfil_Evaluado();

					$perfil->setId( $post->perfil_id );

				  //$perfil->setCompetencias();
					
					//$perfil->setExamenes();
					
					$pdf = $perfil->getPDF( $post_id );
					
					$pdf->Output('test.pdf', 'D');

					
					break;
			
			case 'reclutamiento_guardar_documentos_contrato':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);



				if(!isset( $_FILES['archivo']))
						throw new Exception("Debe adjuntar un archivo");
				


				//$postulante_id = $post->postulante_id;

				//$rocpeta_id = $post->rocpeta_id;
				
				//$procp_id = $post->procp_id;
				$rut = $post->rut;
				
				$archivo = $_FILES['archivo']['name'];

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut/contrato";

				if(!file_exists($ruta_archivo) )
						mkdir( $ruta_archivo, 0775, true );

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut/contrato/$archivo";


				if( file_exists($ruta_archivo) )	
						unlink($ruta_archivo);

				if( !move_uploaded_file( $_FILES['archivo']['tmp_name'], $ruta_archivo ) )
						throw new Exception('Error al subir el archivo');

				break;
			
			
			
			case 'reclutamiento_descargar_documento_contrato':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				
				if( !isset($post->rut) || empty( $post->rut ) )

						throw new Exception("Rut del postulante no recibido", 1);


				if( !isset($post->doc) || empty( $post->doc ) )
						throw new Exception("Nombre de documento no recibido", 1);


				$rut = $post->rut;

				$doc = $post->doc;
				

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut/contrato/$doc";

				if(!file_exists($ruta_archivo) )
						throw new Exception("No existen documentos");
				

				
				// Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
				
				header("Content-type: application/octet-stream");
				header("Content-disposition: attachment; filename='".basename( $ruta_archivo )."'");
				readfile( $ruta_archivo );


				break;



			case 'reclutamiento_descargar_documentos_contrato':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				
				if( !isset($post->rut) || empty( $post->rut ) )

						throw new Exception("Rut del postulante no recibido", 1);

				$rut = $post->rut;
				

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut/contrato";

				if(!file_exists($ruta_archivo) )
						throw new Exception("No existen documentos");
				
				$ruta_zip = $ruta_archivo."/documentos.zip";
				
				$zip = new ZipArchive();
				
				$zip->open( $ruta_zip , ZipArchive::CREATE);

				if ($gestor = opendir( $ruta_archivo ) ) {
						
						while (false !== ($entrada = readdir($gestor))) {
								
								if( !in_array( $entrada, array('.','..')) ){
										
										$doc= $ruta_archivo."/$entrada";
										//$doc= $entrada;
						
										$doc_zip= $entrada;
		
										$zip->addFile( $doc, $doc_zip );

								}

						}
						
						closedir($gestor);

				}
 //Añadimos un archivo dentro del directorio que hemos creado
 //$zip->addFile("imagen2.jpg",$dir."/mi_imagen2.jpg");
 // Una vez añadido los archivos deseados cerramos el zip.
 $zip->close();
 // Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
 header("Content-type: application/octet-stream");
 header("Content-disposition: attachment; filename=documentos.zip");
 // leemos el archivo creado
 readfile( $ruta_zip );
 // Por último eliminamos el archivo temporal creado
 unlink( $ruta_zip );//Destruye el archivo temporal


				break;



			case 'reclutamiento_listar_documentos_contrato':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				
				if( !isset($post->rut) || empty( $post->rut ) )

						throw new Exception("Rut del postulante no recibido", 1);

				$rut = $post->rut;
				

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut/contrato";

				if(!file_exists($ruta_archivo) )
						throw new Exception("No existen documentos");
				
				
				
				$resp['documentos'] = array();


				if ($gestor = opendir( $ruta_archivo ) ) {
						
						while (false !== ($entrada = readdir($gestor))) {
								
								if( !in_array( $entrada, array('.','..')) ){

										array_push( $resp['documentos']	, $entrada );					

								}

						}
						
						closedir($gestor);

				}

				break;
			
			
			case 'reclutamiento_obtener_detalle_etapa':
					
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

				
					if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

					$procp_id = $post->procp_id;
					
					$post_id = $post->post_id;
					
					$rocp_id = isset($post->rocp_id)? $post->rocp_id : null;
					
					$rocpeta_id= $post->rocpeta_id;
					
					
					$resp['detalle'] = $Usuario->getDetalleEtapaPostulacion( $procp_id, $rocpeta_id);
					
					$obj = new Rec_Postulante();
								
					$obj->setId( $post_id );

					if( is_numeric($rocp_id) )
						$resp['subetapas'] = $obj->getListaSubEtapasPostulacion( $rocp_id, $rocpeta_id );
					else
						$resp['subetapas'] = array();


					break;

			case 'reclutamiento_excel_pendiente_contrato':
					
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);

				
					if( ! $RRHH= RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);



					
					//$postulantes = $RRHH->getPostulantesEtapa( null , $reta_id = 8  );
					$postulantes = $post->postulantes;

					$marcar = $post->marcar===true;



							
							require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';
							
							$archivo = 'talana.xlsx';
							
							$inputFileType = PHPExcel_IOFactory::identify($archivo);
							
							$objReader = PHPExcel_IOFactory::createReader($inputFileType);
							
							$objReader->setLoadAllSheets();
							
							$objPHPExcel = $objReader->load($archivo);
							
							$objPHPExcel->setActiveSheetIndex(0);
							

							$row = 5;

							foreach( (array)$postulantes as $p){
									$obj = new Rec_Postulante();
									$obj->setId( $p->post_id );
									$obj->setDatos();

									$fec_nac = $obj->getFechaNacimiento();
									
									if(!empty( $fec_nac )){
										list($y,$m,$d) = explode('-', $fec_nac );
										$fec_nac = $d.'-'.$m.'-'.$y;
									}

									if( $marcar )								
											$RRHH->registrarEtapaPostulante( $p->rocpeta_id, $p->procp_id, $puntaje = null, $descripcion = null, $fecha_hora = date('Y-m-d H:i') , $direccion = null, $link = null, $observacion = null , $aprobado = null );



									$objPHPExcel->getActiveSheet()
											->setCellValue('A'.$row, $obj->getRut().'-'.$obj->getDV() )
											->setCellValue('B'.$row, $obj->getNombre() )
											->setCellValue('C'.$row, $obj->getApaterno() )
											->setCellValue('D'.$row, $obj->getAmaterno() )
											->setCellValue('E'.$row, $obj->getSexoId() )
											->setCellValue('F'.$row, $fec_nac )
											->setCellValue('G'.$row, null )
											->setCellValue('H'.$row, null )
											->setCellValue('I'.$row, null )
											->setCellValue('J'.$row, $obj->getNacionalidadNombre() )
											->setCellValue('K'.$row, $obj->getEmail() )
											->setCellValue('L'.$row, $obj->getTelCelular() )
											->setCellValue('M'.$row, $obj->getTelFijo() )
											->setCellValue('N'.$row, $obj->getProfesion() )
											->setCellValue('O'.$row, null )
											->setCellValue('P'.$row, $obj->getEstadoCivilNombre() )
											->setCellValue('Q'.$row, null )
											->setCellValue('R'.$row, $obj->getNivelEducacionalNombre() )
											->setCellValue('S'.$row, $obj->getNombreContacto().' '.$obj->getTelContacto() )
											->setCellValue('T'.$row, null )
											->setCellValue('U'.$row, null )
											->setCellValue('V'.$row, null )
											->setCellValue('W'.$row, null )
											->setCellValue('X'.$row, null )
											->setCellValue('Y'.$row, $obj->getDireccion() )
											->setCellValue('Z'.$row, null )//extrae numeraciondesde la direccion
											->setCellValue('AA'.$row, null )//direccion departamento
											->setCellValue('AB'.$row, null )//comuna
											->setCellValue('AC'.$row, $obj->getCiudad() )//ciudad
											->setCellValue('AD'.$row, null )//empleador:hh spa personas
											->setCellValue('AE'.$row, $p->cargo )//cargo
											->setCellValue('AF'.$row, null )//desc cargo
											->setCellValue('AG'.$row, null )//tipo contrato
											->setCellValue('AH'.$row, null )
											->setCellValue('AI'.$row, null )
											->setCellValue('AJ'.$row, null )
											->setCellValue('AK'.$row, null )
											->setCellValue('AL'.$row, null )
											->setCellValue('AM'.$row, null )
											->setCellValue('AN'.$row, null )
											->setCellValue('AO'.$row, null )
											->setCellValue('AP'.$row, null )
											->setCellValue('AQ'.$row, null )
											->setCellValue('AR'.$row, null )//unidad organizacional
											->setCellValue('AS'.$row, null )//sucursal
											->setCellValue('AT'.$row, $obj->getAfpNombre() )//afp
											->setCellValue('AU'.$row, $obj->getSaludNombre() )//isapre
											->setCellValue('AV'.$row, null )//moneda (UF)
											->setCellValue('AW'.$row, $obj->getSaludUf() )//monto en moneda de salud
											->setCellValue('AX'.$row, null )
											->setCellValue('AY'.$row, null )
											->setCellValue('AZ'.$row, null )
											->setCellValue('BA'.$row, null )
											->setCellValue('BB'.$row, null )
											->setCellValue('BC'.$row, null )
											->setCellValue('BD'.$row, null )
											->setCellValue('BE'.$row, null )
											->setCellValue('BF'.$row, null )
											->setCellValue('BG'.$row, null )
											->setCellValue('BH'.$row, null )
											->setCellValue('BI'.$row, null )
											->setCellValue('BJ'.$row, null )
											->setCellValue('BK'.$row, null )
											->setCellValue('BL'.$row, null )
											->setCellValue('BM'.$row, null )
											->setCellValue('BN'.$row, null )
											->setCellValue('BO'.$row, null )
											->setCellValue('BP'.$row, null )
											->setCellValue('BQ'.$row, null )
											->setCellValue('BR'.$row, null )
											->setCellValue('BS'.$row, null )
											->setCellValue('BT'.$row, null )
											->setCellValue('BU'.$row, null )
											->setCellValue('BV'.$row, null )
											->setCellValue('BW'.$row, null )
											->setCellValue('BX'.$row, null )
											->setCellValue('BY'.$row, null )
											->setCellValue('BZ'.$row, null )
											->setCellValue('CA'.$row, null )
											->setCellValue('CB'.$row, $obj->getBancoNombre() )
											->setCellValue('CC'.$row, $obj->getTipoCuentaNombre() )
											->setCellValue('CD'.$row, $obj->getCuentaBancaria() )
											->setCellValue('CE'.$row, null )
											->setCellValue('CF'.$row, null )
											->setCellValue('CG'.$row, null )
											->setCellValue('CH'.$row, null )
											->setCellValue('CI'.$row, null )
											->setCellValue('CJ'.$row, null )
											->setCellValue('CK'.$row, null )
											->setCellValue('CL'.$row, null )
											->setCellValue('CM'.$row, null )
											->setCellValue('CN'.$row, null )
											->setCellValue('CO'.$row, null )
											->setCellValue('CP'.$row, null )
											->setCellValue('CQ'.$row, null )
											->setCellValue('CR'.$row, null )
											->setCellValue('CS'.$row, null )
											->setCellValue('CT'.$row, null )
											->setCellValue('CU'.$row, null )
											->setCellValue('CV'.$row, null )
											->setCellValue('CW'.$row, null )
											->setCellValue('CX'.$row, null )
											->setCellValue('CY'.$row, null )
											->setCellValue('CZ'.$row, null )
											->setCellValue('DA'.$row, null )
											->setCellValue('DB'.$row, null )
											->setCellValue('DC'.$row, null )
											->setCellValue('DD'.$row, null )
											->setCellValue('DE'.$row, null )
											->setCellValue('DF'.$row, null )
											->setCellValue('DG'.$row, null )
											->setCellValue('DH'.$row, null )
											->setCellValue('DI'.$row, null )
											->setCellValue('DJ'.$row, null )
											->setCellValue('DK'.$row, null )
											->setCellValue('DL'.$row, null )
											->setCellValue('DM'.$row, null )
											->setCellValue('DN'.$row, null )
											->setCellValue('DO'.$row, null )
											->setCellValue('DP'.$row, null )
											->setCellValue('DQ'.$row, null )
											->setCellValue('DR'.$row, null )
											->setCellValue('DS'.$row, null )
											->setCellValue('DT'.$row, null )
											->setCellValue('DU'.$row, null )
											->setCellValue('DV'.$row, null )
											->setCellValue('DW'.$row, null )
											->setCellValue('DX'.$row, null )
											->setCellValue('DY'.$row, null )
											->setCellValue('DZ'.$row, null )
									;

									$row+=1;

							}
							
							$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
							
							//ob_start();
							
							$objWriter->save('php://output');
							
							//$data = ob_get_contents();
							
							//ob_end_clean();

							unset($objWriter);
							unset($data);
							unset($objPHPExcel);

					//$pdf->Output('test.pdf', 'D');

					break;
	
			case 'reclutamiento_descargar_documento':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

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

	
			case 'reclutamiento_postulaciones_documentos':

				
					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $RRHH = RRHH::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				$perfiles = $RRHH->getOfertasLaboralesPublicadas();


				$resp['ofertas'] = array_map( 

							function( $perfil ) use ( $RRHH) {

									$postulantes = $RRHH->getPostulantes( $perfil->getId());

									$perfil_id = $perfil->getId();

									$docs = array();


									$lista = array_map(

											function( $p ) use ( $perfil_id /*, $docs*/ ) {

													
													$rut = $p->getRut();


													$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut";

																
													$documentos = $p->getListaDocumentosPostulacion( $perfil_id );

													
													if (file_exists($ruta_archivo) && ($gestor = opendir( $ruta_archivo ) ) ) {

															try{
																	
																	while (false !== ($entrada = readdir($gestor))) {
																
																			if( !in_array( $entrada, array('.','..')) ){
																					
																					foreach( $documentos as &$doc){
																							
																							if( preg_match('/'.$doc['id'].'\.*/', $entrada ) )
																									
																									$doc['archivo'] = $entrada;
																					
																					}
																			}
																	
																	}
															
															}catch(Exception $e){
															
															}finally{
																	
																	if(!empty($gestor))
																			closedir($gestor);
															
															}
													
													}
													
													return array(
														'id' => $p->getId(),
														'postulacion_id' => $p->getPostulacionId(),
														'rut' => $p->getRut(),
														'dv' => $p->getDV(),
														'nombre' => $p->getNombre(),
														'apaterno' => $p->getApaterno(),
														'sexo' => $p->getSexoId(),	
														'email' => $p->getEmail(),	
														'documentos' => $documentos
													);
											
											}
									, $postulantes);
									

									return array(
											'oferta_id' =>	$perfil->getOfertaId(),
											'perfil_id' =>	$perfil->getId(),
										  'perfil_nombre' => $perfil->getDescripcion(),
											'fecha_cierre' => $perfil->getFechaReq(),
											'descripcion' => $perfil->getObservacion(),
											'cantidad' => $perfil->getCantidad(),
											'faena_nombre'=>$perfil->getFaenaNombre(),
											'area_nombre' =>	$perfil->getAreaNombre(),
										  'solicitante' => $perfil->getSolicitanteNombre(),
											'postulantes' =>$lista,
									);	

							
							}, $perfiles );
					

					break;
			
			case 'reclutamiento_postulaciones_validar_documentos':
				

					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);


					if( ! $RRHH = RRHH::LoginToken( $post->token ) )

							throw new Exception("Su tiempo de acceso ha expirado", 2);


					if( !isset($post->oferta_id) || empty($post->oferta_id) || !is_numeric($post->oferta_id) )
							
							throw new Exception("Identificador de la oferta no es valido", 2);


					if( !isset($post->perfil_id) || empty($post->perfil_id) || !is_numeric($post->perfil_id) )
							
							throw new Exception("Identificador del perfil no es valido", 2);


//					if( !isset($post->postulacion_id) || empty($post->postulacion_id) || !is_numeric($post->postulacion_id) )
							
	//						throw new Exception("Identificador de la postulacion no es valido", 2);

					if( !isset($post->documentos) || empty($post->documentos) || !is_array($post->documentos) )

							throw new Exception("No hay documentos que procesar", 2);

					foreach((array)$post->documentos as $d){

							$validado = ($d->validado===true || $d->validado == 'true') ? 1:0;

							$RRHH->validarDocumentoPostulante(  $d->id, $post->post_id ,  $validado );

					}
	
					$resp["mensaje"] = "El estado de los documentos fue actualizado";


					$Postulante = new Rec_Postulante();

					$Postulante->setId($post->post_id);
					
					$Postulante->setDatos();

					$rut = $Postulante->getRut();
					
					$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut";

					$documentos = $Postulante->getListaDocumentosPostulacion( $post->perfil_id );

													
					if (file_exists($ruta_archivo) && ($gestor = opendir( $ruta_archivo ) ) ) {
							
							try{

									while (false !== ($entrada = readdir($gestor))) {

											if( !in_array( $entrada, array('.','..')) ){

													foreach( $documentos as &$doc){

															if( preg_match('/'.$doc['id'].'\.*/', $entrada ) )

																	$doc['archivo'] = $entrada;

													}
											}

									}

							}catch(Exception $e){
															

							}finally{

									if(!empty($gestor))

											closedir($gestor);

							}

					}
					
					$resp['documentos'] = $documentos;
													


					break;
	
			case 'reclutamiento_postulaciones_solicitar_documentos':
				

					if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);


					if( ! $RRHH = RRHH::LoginToken( $post->token ) )

							throw new Exception("Su tiempo de acceso ha expirado", 2);


					if( !isset($post->oferta_id) || empty($post->oferta_id) || !is_numeric($post->oferta_id) )
							
							throw new Exception("Identificador de la oferta no es valido", 2);


					if( !isset($post->perfil_id) || empty($post->perfil_id) || !is_numeric($post->perfil_id) )
							
							throw new Exception("Identificador del perfil no es valido", 2);


					if( !isset($post->documentos) || empty($post->documentos) || !is_array($post->documentos) )

							throw new Exception("No hay documentos que procesar", 2);
					
					if( !isset($post->email) || empty($post->email)  )
							
							throw new Exception("No se ha recibido el email del postulante", 2);
	
					if( !isset($post->perfil_nombre) || empty($post->perfil_nombre)  )
							
							throw new Exception("No se ha recibido el cargo de postulacion", 2);


					$doc_no_validos= array();


					foreach((array)$post->documentos as $d){

							$validado = ($d->validado===true || $d->validado == 'true') ? 1:0;

							$RRHH->validarDocumentoPostulante(  $d->id, $post->post_id ,  $validado );


							if( $validado == 0 || $validado == false )
									
									array_push($doc_no_validos, $d);

					
					}

					$resp["mensaje"] = "El estado de los documentos fue actualizado";

					$Postulante = new Rec_Postulante();

					$Postulante->setId($post->post_id);
					
					$Postulante->setDatos();

					$rut = $Postulante->getRut();
					
					$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut";

					$documentos = $Postulante->getListaDocumentosPostulacion( $post->perfil_id );

													
					if (file_exists($ruta_archivo) && ($gestor = opendir( $ruta_archivo ) ) ) {
							
							try{

									while (false !== ($entrada = readdir($gestor))) {

											if( !in_array( $entrada, array('.','..')) ){

													foreach( $documentos as &$doc){

															if( preg_match('/'.$doc['id'].'\.*/', $entrada ) )

																	$doc['archivo'] = $entrada;

													}
											}

									}

							}catch(Exception $e){
															

							}finally{

									if(!empty($gestor))

											closedir($gestor);

							}

					}
					
					$resp['documentos'] = $documentos;
													


					$email = $post->email;

					$cargo = $post->perfil_nombre;

					$lista = "<ul>";

					$resp['no_validados'] = $doc_no_validos;
					$resp['email'] = $email;

					foreach((array)$doc_no_validos as $d){
							
							$lista.= "<li>".$d->descripcion."</li>";

					}
						
					$lista .= "<ul>";
				
					$body ="<table>";
					$body.="<thead>";
					$body.="<tr>";
					$body.="<th>";
					$body.="<center><img src='https://todoacero.cl/images/logo.jpg'/></center>";
					$body.="</th>";
					$body.="</tr>";
					$body.="</thead>";
					$body.="<tbody>";
					$body.="<tr>";
					$body.="<td style='padding:20px;font-weight:normal'><p>";

				
					$body.="Estimado postulante, es necesario que actualice los siguientes documentos en <a href='>https://reclutamiento.todoacero.cl'>reclutamiento.todoacero.cl</a> para el cargo <strong>".$cargo."</strong>";

					$body.=$lista;
	
					$body.="</p></td></tr>";
					$body.="</tbody>";
					$body.="</table>";
					
					$mail = new PHPMailer(TRUE);
			
				
					$mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
					$mail->addAddress($email, '');
					$mail->isHTML(true);
					$mail->Subject = 'Actulizacion de documentos para postulacion';
					$mail->Body = utf8_decode($body);
					$mail->send();



					break;


			case 'reclutamiento_descargar_documentos_postulacion':
					
				
				if( !isset($post->token) || empty( $post->token ) )

						throw new Exception("Token de acceso no encontrado", 1);



				if( ! $Usuario = Usuario::LoginToken( $post->token ) )

						throw new Exception("Su tiempo de acceso ha expirado", 2);

				
				if( !isset($post->rut) || empty( $post->rut ) )

						throw new Exception("Rut del postulante no recibido", 1);

				$rut = $post->rut;
				

				$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut";

				if(!file_exists($ruta_archivo) )
						throw new Exception("No existen documentos");
				
				/********************/

					$Postulante = new Rec_Postulante();

					$Postulante->setId($post->post_id);
					
	//				$Postulante->setDatos();

//					$rut = $Postulante->getRut();
					
					$ruta_archivo = "/home/admin/public_ftp/postulantes/$rut";

					$documentos = $Postulante->getListaDocumentosPostulacion( $post->perfil_id );

//$resp['docs'] =  $documentos; 
	//					throw new Exception("No existen documentos");


				/***********************/



				$ruta_zip = $ruta_archivo."/documentos.zip";
				
				$zip = new ZipArchive();
				
				$zip->open( $ruta_zip , ZipArchive::CREATE);

				if ($gestor = opendir( $ruta_archivo ) ) {
						
						while (false !== ($entrada = readdir($gestor))) {
								
								if( !in_array( $entrada, array('.','..')) ){
										
										$doc= $ruta_archivo."/$entrada";
						
										//$doc_zip= $entrada;
	

										foreach( $documentos as $d){

												if( preg_match('/'.$d['id'].'\.*/', $entrada ) ){
												
														$zip->addFile( $doc, $entrada );

														$zip->renameName($entrada, str_replace($d['id'], $d['descripcion'], $entrada) );
												}

										}
	

								}

						}
						
						closedir($gestor);

				}

				$zip->close();
				
				header("Content-type: application/octet-stream");
				
				header("Content-disposition: attachment; filename=documentos.zip");
				
				readfile( $ruta_zip );
				
				unlink( $ruta_zip );


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

