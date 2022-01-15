<?php

require_once dirname(__DIR__).'/datos/Datos.php';
require_once '../servicios/JWT/JWT.php';
require_once '../servicios/JWT/SignatureInvalidException.php';
require_once '../servicios/JWT/ExpiredException.php';
require_once '../servicios/JWT/BeforeValidException.php';
require_once '../servicios/JWT/JWT.php';

require_once 'Login.php';
require_once 'Faena.php';
require_once 'Encuesta.php';
require_once 'Pasaporte.php';
require_once 'Persona.php';
require_once 'Rec_Solicitud.php';
require_once 'Rec_Perfil.php';
require_once 'Rec_Perfil_Solicitud.php';
require_once 'Rec_Perfil_Evaluado.php';
require_once 'Rec_Postulante.php';

use \Firebase\JWT\JWT;

class Usuario extends Persona  implements iLogin
{
  protected $usuario;
  protected $nombre_completo;
  protected $usuario_id;
  protected $expiracion;
//  private $nombre;
//  private $rut;
  protected $email;
  protected $celular;
  protected $faena;
  protected $pasaporte;
	protected $vacuna_covid19;
	
		
	private $solicitudes_oferta_laboral;
	
	private $error;

	public $lineaError;

  protected function __construct()
  {
    /*if( !is_numeric( $rut ) )
      return false;

    $this->rut=$rut;*/
    $this->datos = new Datos();
    //$this->nombre=$nombre;
    $this->pasaporte = new Pasaporte();
  }

  public static function LoginInvitado($rut)
  {
    $instancia = new Self();
    $instancia->setRut($rut);
    return $instancia;
  }

  public static function LoginPass($usr, $pass)
  {

    $instancia = new Self();
    
    if( $rut = $instancia->datos->validaLogin( $usr, $pass ) ){
      $instancia->setRut($rut);
      $instancia->setUsuario($usr);
      return $instancia;
    }else{
      $instancia->error ="Usuario y password incorrectos";
      return false;
    }
  }

  public static function LoginToken($token)
  {
    $key = date('dmY');

    try{
			
				$decoded = JWT::decode($token, $key, array('HS256'));
				
				if( isset( $decoded->rut ) && isset( $decoded->exp ) && $decoded->exp > time() ){
        
						
						$instancia = new Self();
					
						if( !$instancia->usuario_id = $instancia->datos->validaToken( $decoded->sub, $token ) ){
								
								return false;
						
						}

						$instancia->setRut( $decoded->rut );
						$instancia->setUsuario( $decoded->sub );
						$instancia->setExpiracion( $decoded->exp );
						
						return $instancia;
				
				}else{
						
						return false;
				
				}      
	
		}catch(Exception $e){
		
				return false;
		
		}
	
	}

  public function registrarToken($token )
  {

    return $this->datos->registrarToken( $this->usuario, $token ) ;
   
  }

  public function setExpiracion($time)
  {
    $this->expiracion = $time;
	}

	public function cambiarClave( $old_pass, $new_pass )
	{

			if( empty( $this->usuario_id ) || !is_numeric( $this->usuario_id ) ){

					$this->error = "Identificador de usuario no valido";
					
					return false;
			}
			else{
					
					$r = $this->datos->cambiarClave( $this->usuario_id , $old_pass, $new_pass ); 

					if(!$r)
							$this->error ="No fue posible actualizar su clave, por favor verifique los datos ingresados";
					
					return $r;

			}

	}
	public function cambiarClavePorCorreo( $correo, $new_pass )
	{

			if( empty( $correo ) || empty( $new_pass ) ){

					$this->error = "Faltan datos para cambiar la clave";
					
					return false;
			}
			else{
					
					$r = $this->datos->resetClavePorCorreo( $correo , $new_pass ); 
					
					if(!$r)
							$this->error ="No fue posible actualizar su clave, por favor comuniquese con al área de Gestion de Personas";
					
					return $r;
			}
	}

	public function setDatos(){

			if( !empty( $this->usuario_id ) ){
					
					$datos = $this->datos->getDatosUsuario( $this->usuario_id );
					
					if( !empty($datos) ){
							
							$this->email = $datos[0]['USER_CORREO'];
							$this->nombre_completo = $datos[0]['EMP_NOMBRE'];
					
					}
			}
	
	}

  public function setUsuario($usr='')
  {
    $this->usuario = $usr;
  }
	
	public function setNombre($nombre='')
  {
    $this->nombre = $nombre;
  }

  public function setEmail($email='')
  {
    $this->email = $email;
  }

  public function setCelular($celular='')
  {
    $this->celular = $celular;
  }
  public function setFaena($faena_id, $faena_nombre='')
  {
    $this->faena = new Faena($faena_id, $faena_nombre);
  }

  public function setDatosDesdeEncuestas()
  {
    $r = $this->datos->getPersonas($this->rut);
    if (is_array($r) && count($r)) {
      $this->nombre=$r[0]['PERS_NOMBRE'];
      $this->email=$r[0]['PERS_EMAIL'];
      $this->celular=$r[0]['PERS_CELULAR'];
			$this->vacuna_covid19 = $r[0]['VACUNA_COVID19'];
    }
  }

  //public function getRut()  {  return $this->rut ;}
  //public function getNombre()  {  return $this->nombre ;}
  public function getUsuario()  {  return $this->usuario ;}
  public function getNombreCompleto()  {  return $this->nombre_completo ;}
  public function getExpiracion()  {  return $this->expiracion ;}
  public function getEmail()  {  return $this->email ;}
  public function getCelular()  {  return $this->celular ;}
  public function getFaena()  {  return $this->faena ;}
  public function getEstadoPasaporte()  {  return $this->pasaporte->getEstadoDescripcion() ;}
	public function getError(){ return $this->error; }
	public function getEstadoVacunaCovid19(){ return $this->vacuna_covid19; }

	public function getDatosPasaporte( $fecha ){

			$datos = $this->datos->obtenerPasaporte( $this->rut, $fecha );

			if( empty($datos) )
					return false;
			else{

					$p = $datos[0];

					return array(
						'rut'=> $p['RUT'],
						'nombre'=> $p['NOMBRE'],
						'estado'=> $p['ESTCONT_DESCRIPCION'],
						'faena'=> $p['FA_NOMBRE'],
						'fecha' =>$p['FECHA']
					); 
			
			}
	
	}

  public function getArbolModulos(){
    

    $datos = $this->datos->getArbolModulos( $this->usuario );
    
		
		if( !is_array($datos) ){

				$this->error = "Módulos vacios";

				return [];

		}

    $modulos = array_filter($datos, function($v,$k) {
      return isset($v['MO_ID']) && is_numeric($v['MO_ID']) && empty($v['SM_ID']);
    }, ARRAY_FILTER_USE_BOTH);

    $modulos = array_map(
      function ($t) use ($datos)
      {
        
        $moid = $t['MO_ID'];

        $app = array_filter($datos, function($v,$k) use( $moid ) {
          return $v['MO_ID'] == $moid && is_numeric($v['SM_ID']);
        }, ARRAY_FILTER_USE_BOTH ) ;

        $app = array_map(
          function( $a ){
            return array(
              'id'=> $a['SM_ID'],
              'nombre'=> $a['NOMBRE'],
              'descripcion'=> $a['DESCRIPCION'],
              'componente'=> $a['COMPONENTE'],
              'carpeta'=> $a['CARPETA'],
              'icono'=> $a['ICONO'],
              'icono_color'=> $a['ICONO_COLOR'],
            );            
          }, $app);

        return array(
          'id'=> $t['MO_ID'],
          'nombre'=> $t['NOMBRE'],
          'descripcion'=> $t['DESCRIPCION'],
          'carpeta'=> $t['CARPETA'],
          'icono'=> $t['ICONO'],
          'icono_color'=> $t['ICONO_COLOR'],
          'app' => $app
        );
      },
      $modulos
    );  

    return $modulos;
  }

	public function getUsuariosSubModEntrevistaTecnica(){

			$lista = $this->datos->getUsuariosSubModulo( $submodulo = 369 );

			return array_map(
					function($t){
							return array(
									'usuario_id'=> $t['USER_ID'],
									'usuario_nombre'=> $t['MU_USUARIO'],
									'persona_nombre'=> $t['PERS_NOMBRE'],
							);
					}, $lista
			);
	}

  public function getTiposExamen(){
    return array_map(
      function ($t)
      {
        return array(
          'id'=> $t['TIPEXAM_ID'],
          'nombre'=> $t['TIPEXAM_TITULO'],
        );
      },
      $this->datos->getTiposExamen()
    );
  }

	
	public function getTurnos( $turno_id = null){
    return array_map(
      function ($t)
      {
        return array(
          'id'=> $t['ID'],
          'descripcion'=> $t['DESCRIPCION'],
        );
      },
      $this->datos->getTurnos( $turno_id )
    );
  }

  public function registrarEncuesta( $Encuesta )
  {
    $pas_id = null;
    $this->datos->begin();

		$faena_id = null;
		
		if( $this->faena instanceof Faena && !empty( $this->faena ))
				$faena_id = $this->faena->getId();

    $r = $this->datos->registrarEncuesta($this->rut, $this->nombre, $this->email, $this->celular, $Encuesta->getId(), $faena_id );
		
		if(!is_array($r)){
				$this->error ="registrar encuesta";
				return false;
		}

		if( isset( $r[0]['ERROR']) && $r[0]['ERROR'] == 'EXISTE' ){

				$this->error = 'Ya existe un pasaporte para hoy';

				return false;

		}

				

    if(isset($r[0]['vRESP_ID']) && is_numeric($r[0]['vRESP_ID'])){
      $resp_id= $r[0]['vRESP_ID'];
      $res = array();
      $r = $this->datos->registrarPasaporte($resp_id, $estado=1, $temperatura=null);

			if( !isset($r[0]['PAS_ID']) || !is_numeric($r[0]['PAS_ID']) ){
						$this->error ="registrar pasaporte";
						return false;
		  }

      $pas_id = $r[0]['PAS_ID'];

      foreach ($Encuesta->getPreguntas() as $pregunta) {
        if(!$this->datos->registrarPregunta($Encuesta->getId(), $resp_id, $pregunta->getId(), $pregunta->getRespuestaCerrada(), $pregunta->getRespuestaAbierta() ) ){
						$this->error ="registrar pregunta";
          $this->datos->rollback();
          return false;
				}
			}

      $this->datos->commit();

      $estado_pas= $this->datos->getEstadoPasaporte($pas_id);//array[0]['']
      $this->pasaporte->set($pas_id, $estado_pas[0]['ESTCONT_ID'], $estado_pas[0]['ESTCONT_DESCRIPCION'] );
      return true;
    }else{
						$this->error ="otro error";
      $this->datos->rollback();
      return false;
    }
  }

  public function registrarEncuestaDISC( $Encuesta )
  {

		$this->error="";
		//$this->lineaError = 286;
    $this->datos->begin();
		

    $r = $this->datos->registrarEncuesta($this->rut, $this->nombre, $this->email, $this->celular, $Encuesta->getId(), $faena_id = null );
		

		if(!is_array($r)){
				
				$this->error ="No ha sido posible registrar el TEST";
				
				return false;
		}
				

    if(isset($r[0]['vRESP_ID']) && is_numeric($r[0]['vRESP_ID'])){
	
		  $resp_id= $r[0]['vRESP_ID'];


      foreach ($Encuesta->getPreguntas() as $pregunta) {

        if(!$this->datos->registrarPregunta($Encuesta->getId(), $resp_id, $pregunta->getId(), $pregunta->getRespuestaCerrada(), $pregunta->getRespuestaAbierta() ) ){
						
						$this->error ="No fue posible registrar una de las respuestas";
						
						$this->datos->rollback();
						
						return false;
				
				}
			
			}

      $this->datos->commit();

			return true;

    }else{

				if( isset( $r[0]['ERROR'])){
						
						if ( $r[0]['ERROR']=='EXISTE' ){

								$this->error ="El usuario parece ya haber realizado este TEST con anterioridad, en caso contrario comuniquese con el área de Gestión Personas.";

						}else{

								$this->error = "No fue posible registrar el test, si esto persiste, comuniquese con el área de Gestion Pesonas.";
						}
				}else{
						$this->error = "Ocurrio un error inesperado, si esto persiste, comuniquese con el área de Gestion Pesonas.";
				}

				$this->datos->rollback();

				return false;
    }
  }


  public function registrarEncuestaCasino( $sexo,$edad, $ip, $empresa, $casino, $Encuesta )
  {

		$this->error="";
		
		$this->datos->begin();
		

    $r = $this->datos->registrarEncuestaCasino( $sexo, $edad, $ip, $empresa, $casino, $Encuesta->getId());
		

		if(!is_array($r)){
				
				$this->error ="No ha sido posible registrar la encuesta";
				
				return false;
		}
				

    if(isset($r[0]['vRESP_ID']) && is_numeric($r[0]['vRESP_ID'])){
	
		  $resp_id= $r[0]['vRESP_ID'];


      foreach ($Encuesta->getPreguntas() as $pregunta) {

        if(!$this->datos->registrarPreguntaCasino($Encuesta->getId(), $resp_id, $pregunta->getId(), $pregunta->getRespuestaCerrada(), $pregunta->getRespuestaAbierta() ) ){
						
						$this->error ="No fue posible registrar una de las respuestas";
						
						$this->datos->rollback();
						
						return false;
				
				}
			
			}

      $this->datos->commit();

			return true;

    }else{

				if( isset( $r[0]['ERROR'])){
						
						if ( $r[0]['ERROR']=='EXISTE' ){

								$this->error ="El usuario parece ya haber realizado esta encuesta con anterioridad, en caso contrario comuniquese con el área de Soporte.";

						}else{

								$this->error = "No fue posible registrar la encuesta, si esto persiste, comuniquese con el área de Soporte.";
						}
				}else{
						$this->error = "Ocurrio un error inesperado, si esto persiste, comuniquese con el área de Soporte.";
				}

				$this->datos->rollback();

				return false;
    }
  }


  public function registrarEncuestaAmbiental( $Encuesta )
  {

		$this->error="";
		//$this->lineaError = 286;
    $this->datos->begin();
		

    $r = $this->datos->registrarEncuesta($this->rut, $this->nombre, $this->email, $this->celular, $Encuesta->getId(), $faena_id = null );
		

		if(!is_array($r)){
				
				$this->error ="No ha sido posible registrar el formulario";
				
				return false;
		}
				

    if(isset($r[0]['vRESP_ID']) && is_numeric($r[0]['vRESP_ID'])){
	
		  $resp_id= $r[0]['vRESP_ID'];


      foreach ($Encuesta->getPreguntas() as $pregunta) {

        if(!$this->datos->registrarPregunta($Encuesta->getId(), $resp_id, $pregunta->getId(), $pregunta->getRespuestaCerrada(), $pregunta->getRespuestaAbierta() ) ){
						$this->error ="No fue posible registrar una de las respuestas";
						
						$this->datos->rollback();
						
						return false;
				
				}
			
			}

      $this->datos->commit();

			return true;

    }else{

				$this->error = "Ocurrio un error inesperado, si esto persiste, comuniquese con el área de Soporte.";

				$this->datos->rollback();

				return false;
    }

  }



	public function getTipoCompetencias(){

			$lista = $this->datos->getTiposCompetencias();
			return array_map(
					function($t){
							return array(
									'id'=> $t['ID'],
									'descripcion' => $t['DESCRIPCION']
							);
					}, $lista
			);
	}
/*
	public function getCompetenciasOferta( $perfil_id ){

			$lista = $this->datos->getCompetenciasOfertaPerfil($perfil_id);


	}
 */
	public function getTiposDocumento( $perfil_id = null ){

			if( ! $lista = $this->datos->getDocumentoTipos( $perfil_id ) )
					return false;

			return array_map(
					function($t){
							return array(
									'id'=> $t['ID'],
									'descripcion' => $t['DESCRIPCION']
							);
					}, $lista
			);
	}

public function getPerfilesCargo( $perfil_id = null ){

			$lista = $this->datos->getPerfilesCargo( $perfil_id );

			return array_map(
					function($t){

							$perfil= new Rec_Perfil();
							$perfil->setId($t['ID']);
							$perfil->setDescripcion($t['DESCRIPCION']);
							$perfil->setSueldo($t['VALOR']);

							return $perfil;							
							
					}, $lista
			);
	}
	
	public function getPlantillasEtapasReclutamiento(){
			
		
			$lista = $this->datos->getPlantillasEtapas();

			$detalle = $lista[1];

			return array_map(
				function ($t) use( $detalle )
				{
						$id = $t['ID'];

						$etapas = array_filter( $detalle,
								function( $etapa ) use ( $id ) {
										return $etapa['PLANTILLA_ID'] == $id;
								}, ARRAY_FILTER_USE_BOTH
						);

						$etapas = array_map(
								function( $e ){
										return array(
												'id' =>$e['ETAPA_ID'],
												'descripcion' =>$e['ETAPA_DESCRIPCION'],
												'orden' =>$e['ORDEN'],
												'tipo_id'=>$e['TIPO_ID'],
												'tipo_descipcion'=>$e['TIPO_DESCRIPCION'],
										);
								}
						, $etapas);

						return array(
								'id'=> $t['ID'],
								'descripcion'=> $t['DESCRIPCION'],
								'etapas' => $etapas
						);
				},
				$lista[0]
				
			);
  }

	public function getCompetenciasPerfil( $perfil_id ){

			$perfil = new Rec_Perfil();
			
			$perfil->setId( $perfil_id );

			$perfil->setCompetencias();

			return $perfil->getCompetencias();

	}
	
	public function getCompetenciasOfertaPerfil( $perfil_id ){

			$perfil = new Rec_Perfil_Evaluado();
			
			$perfil->setId( $perfil_id );

			$perfil->setCompetencias();

			return $perfil->getCompetencias();

	}

	public function getImplementosPerfil( $perfil_id ){

			$perfil = new Rec_Perfil();
			
			$perfil->setId( $perfil_id );

			$perfil->setImplementos();

			return $perfil->getImplementos();

	}

	public function getCompetenciasPerfilSolicitado( $perfil_id ){

			$perfil = new Rec_Perfil_Solicitud();
			
			$perfil->setId( $perfil_id );

			$perfil->setCompetencias();

			return $perfil->getCompetencias();

	}
	
	public function getExamenesPerfil( $perfil_id ){

			$perfil = new Rec_Perfil();
			
			$perfil->setId( $perfil_id );

			$perfil->setExamenes();

			return $perfil->getExamenes();

	}
	
	public function getExamenesOfertaPerfil( $perfil_id ){

			$perfil = new Rec_Perfil_Evaluado();
			
			$perfil->setId( $perfil_id );

			$perfil->setExamenes();

			return $perfil->getExamenes();

	}

	public function getDocumentosPerfilSolicitado( $perfil_id ){

			$perfil = new Rec_Perfil_Solicitud();
			
			$perfil->setId( $perfil_id );

			$perfil->setDocumentos();

			return $perfil->getDocumentos();

	}
	
	public function getDocumentosOfertaPerfil( $perfil_id ){

			$perfil = new Rec_Perfil_Evaluado();
			
			$perfil->setId( $perfil_id );

			$perfil->setDocumentos();

			return $perfil->getDocumentos();

	}
	
	public function getImplementosPerfilSolicitado( $perfil_id ){

			$perfil = new Rec_Perfil_Solicitud();
			
			$perfil->setId( $perfil_id );

			$perfil->setImplementos();

			return $perfil->getImplementos();

	}

	
	public function getSolicitudOfertaLaboral( $sol_id){

			//puede iniciarse otros datos
			return new Rec_Solicitud( $sol_id );

	}

	
	public function getMisSolicitudesOfertaLaboral( $id_solicitud = null ){
			
			$lista = $this->datos->getSolicitudesReclutamientoUsuario( $this->usuario_id );

			if( ! $lista )
					return false;

			$this->solicitudes_oferta_laboral = array_map( function( $fila ){

					$sol = new Rec_Solicitud();
					$sol->setId( $fila['ID'] );
					$sol->setFechaString( $fila['FECHA']);
					$sol->setEstadoId($fila['ESTADO_ID']);
					$sol->setEstadoDescripcion( $fila['ESTADO_DESC']);

					return $sol;
			},
					$lista[0] );

			foreach( $this->solicitudes_oferta_laboral as &$sol){

					$sol_id = $sol->getId();

					$perfiles = array_filter( $lista[1],
							function( $p , $k ) use ( $sol_id ){
								return $p['SOL_ID'] == $sol_id;
							}, ARRAY_FILTER_USE_BOTH
					);

					foreach( $perfiles as $p ){

							$perfil = new Rec_Perfil_Solicitud();
							$perfil->setId( $p['ID'] );// id_solicitud_perfil
							$perfil->setDescripcion( $p['PERFIL_DESC'] );//nombre del perfil
							$perfil->setCantidad( $p['CANTIDAD'] );
							$perfil->setObservacion( $p['OBSERVACION'] );

							$sol->addPerfil( $perfil);

					}

			}
			
			return $this->solicitudes_oferta_laboral;
	}
	
	public function getSolicitudesPorAprobarOfertaLaboral( $id_solicitud = null ){
			
			$lista = $this->datos->getSolPendienteAprobRecluamiento( $this->usuario_id );

			if( ! $lista )
					return false;

			$this->solicitudes_oferta_laboral = array_map( function( $fila ){

					$sol = new Rec_Solicitud();
					$sol->setId( $fila['ID'] );
					$sol->setFechaString( $fila['FECHA']);
					$sol->setEstadoId($fila['ESTADO_ID']);
					$sol->setEstadoDescripcion( $fila['ESTADO_DESC']);
					$sol->setUsuarioNombre( $fila['PERS_NOMBRE']);
					$sol->setUrgente( $fila['RSOL_URGENTE']==1 || $fila['RSOL_URGENTE']==true );

					return $sol;
			},
					$lista[0] );

			foreach( $this->solicitudes_oferta_laboral as &$sol){

					$sol_id = $sol->getId();

					$perfiles = array_filter( $lista[1],
							function( $p , $k ) use ( $sol_id ){
								return $p['SOL_ID'] == $sol_id;
							}, ARRAY_FILTER_USE_BOTH
					);

					foreach( $perfiles as $p ){

							$perfil = new Rec_Perfil_Solicitud();
							$perfil->setId( $p['ID'] );// id_solicitud_perfil
							$perfil->setDescripcion( $p['PERFIL_DESC'] );//nombre del perfil
							$perfil->setCantidad( $p['CANTIDAD'] );
							$perfil->setFaenaNombre( $p['FA_NOMBRE'] );
							$perfil->setAreaNombre( $p['AREA_NOMBRE'] );
							$perfil->setObservacion( $p['OBSERVACION'] );
							$perfil->setSueldo( $p['SUELDO'] );
							$perfil->setTurnoDescripcion( $p['TURNO_DESCRIPCION'] );
						  $perfil->setEvaluadorUsrId( $p['EVALUADOR_USR_ID'] );

							$sol->addPerfil( $perfil);

					}

			}
			
			return $this->solicitudes_oferta_laboral;
	}


	public function registrarSolicitudOfertaLaboral( $solicitud , $urgente = false){

			if( $solicitud instanceof Rec_Solicitud ){
						
					$this->datos->begin();

					$id_solicitud = $this->datos->registrarSolicitudCargo( 
							$estado = 1, 
							$solicitud->getDescripcion(), 
							$fecha = date('Y-m-d'), 
							$solicitud->getFaena(), //solicitud? 
							$solicitud->getArea(),
							$this->usuario_id,
							$urgente
				 	);

					if( ! is_numeric( $id_solicitud ) ){
							$this->error = "No fue posible registrar la solicitud de cargo";
							return false;
					}

					$solicitud->setId($id_solicitud);
		
					foreach ($solicitud->getPerfiles() as $perfil) {

						
							$id_cargo = $this->datos->registrarSolicitudCargoDetalle(
									$perfil->getId(), 
									$solicitud->getId(),
									$perfil->getTurnoId(),
									$perfil->getDescripcion(),
									$estado = 1,
									$perfil->getCantidad(), 
									$perfil->getFaena(),//faena
									$perfil->getArea(),//area
									$perfil->getFechaReq(),//fecha
									$perfil->getSueldo(),
									$perfil->getNroContrato(),
								  $perfil->getEvaluadorUsrId()
							);

							if( ! $id_cargo ){
									
									$this->error ="Ocurrio un problema al intentar registrar uno de los cargos solicitados";
									
									$this->datos->rollback();
									
									return false;							
							}
							else{

									foreach( $perfil->getCompetencias() as $competencia ){
											$comp_id = $this->datos->registrarSolicitudCargoCompetencia($competencia->getId(), $id_cargo , $estado = 1 );

											//var_dump( $competencia->getId(), $id_cargo, $comp_id);
											if( ! $comp_id ){
													
													$this->error ="Ocurrio un problema al intentar registrar una de las competencias para los cargos solicitados";
													
													$this->datos->rollback();
													
													return false;												
											}
									
									}
								
									foreach( $perfil->getDocumentos() as $documento ){

											$doc_id = $this->datos->registrarSolicitudCargoDocumento($documento->getTipo(), $id_cargo , $estado = 1 );
											//var_dump( $competencia->getId(), $id_cargo, $comp_id);
											if( ! $doc_id ){
													
													$this->error ="Ocurrio un problema al intentar registrar uno de los documentos para los cargos solicitados";
													
													$this->datos->rollback();
													
													return false;												
											}
									
									}
		
									foreach( $perfil->getImplementos() as $implemento ){

											$ok = $this->datos->registrarSolicitudCargoImplemento( $implemento->getId(), $id_cargo , $estado = 1 );
											//var_dump( $competencia->getId(), $id_cargo, $comp_id);
											if( ! $ok ){
													
													$this->error ="Ocurrio un problema al intentar registrar uno de los implementos para los cargos solicitados";
													
													$this->datos->rollback();
													
													return false;												
											}
									
									}

							}
						
					}

					$this->datos->commit();


					return $id_solicitud;
			}
			else{

					$this->error = "El parametro no corresponde a una solicitud de oferta laboral";

					return false;
			}
	}
	
	public function getOfertasLaboralesPublicadas(){

			//esta lista trae los perfiles
			$lista = $this->datos->getOfertasPublicadas();

			if( ! $lista )
					return false;

			$perfiles = array_map( 
					function( $l ){

							$perfil = new Rec_Perfil_Evaluado();
							$perfil->setOfertaId( $l['OFERTA_ID'] );
							$perfil->setId( $l['PERFIL_ID'] );
							$perfil->setDescripcion($l['PERFIL_NOMBRE']);
							$perfil->setFechaReq($l['FECHA_CIERRE']);
							$perfil->setObservacion($l['DESCRIPCION']);
							$perfil->setCantidad($l['CANTIDAD']);
							$perfil->setFaenaNombre( $l['FAENA_NOMBRE']);
							$perfil->setAreaNombre( $l['AREA_NOMBRE']);
							$perfil->setSolicitanteNombre( $l['SOLICITANTE_NOMBRE']);
							$perfil->setNroContrato( $l['NRO_CONTRATO']);
								

							return $perfil;

					}, (array)$lista);

			
			return $perfiles;
	}

	public function getPostulantes( $rocp_id = null, $recetatip_id = null )
	{
			$lista = $this->datos->getListaPostulantes( $rocp_id , $recetatip_id);//oferta perfil id

			$postulantes = array_map(
					function( $l){
							$post = new Rec_Postulante();
							$post->setId($l['ID']);
							$post->setPostulacionId($l['PROCP_ID']);
							$post->setRut($l['RUT']);
							$post->setDV($l['DV']);
							$post->setNombre($l['NOMBRE']);
							$post->setApaterno($l['APATERNO']);
							$post->setEmail($l['EMAIL']);
							$post->setPonderacion($l['PONDERACION']);

							return $post;
					}, $lista);
			return $postulantes;
	}
	
	public function getPostulantesEtapa( $rocp_id = null, $reta_id = null )
	{
			$lista = $this->datos->getListaPostulantes( $rocp_id , $reta_id, $this->usuario_id );//oferta perfil id

			$postulantes = array_map(

					function( $l){
							/*
							$post = new Rec_Postulante();
							$post->setId($l['ID']);
							$post->setPostulacionId($l['PROCP_ID']);
							$post->setRut($l['RUT']);
							$post->setDV($l['DV']);
							$post->setNombre($l['NOMBRE']);
							$post->setApaterno($l['APATERNO']);
						*/
							return array(
							'id' => $l['ID'],
							'postulacion_id' => $l['PROCP_ID'],
							'rut' => $l['RUT'],
							'dv' => $l['DV'],
							'nombre' => strtoupper ($l['NOMBRE']),
							'apaterno' =>  strtoupper ($l['APATERNO']),
							'amaterno' =>  strtoupper ($l['AMATERNO']),
							
							'perfil_id' => isset( $l['ROCP_ID']) ? $l['ROCP_ID'] : null ,
							'rocpeta_id' => isset( $l['ROCPETA_ID']) ? $l['ROCPETA_ID'] : null ,
							'perfil_nombre' => isset($l['PERFIL_NOMBRE'])? $l['PERFIL_NOMBRE'] : null ,
							'fecha_cierre' =>  isset( $l['FECHA_CIERRE'] )? $l['FECHA_CIERRE'] : null ,
							'fecha_evaluacion' => isset($l['FECHA_EVALUACION'])? $l['FECHA_EVALUACION']: null ,
							'faena_nombre' => isset($l['FA_NOMBRE'])? $l['FA_NOMBRE'] : null ,
							'area_nombre' => isset($l['AREA_NOMBRE'])? $l['AREA_NOMBRE'] : null ,
							//		'solicitante' => 'falta solicitante',
							'aprobaciones'=> isset($l['APROBACIONES'])? $l['APROBACIONES']:null,
							'reprobaciones'=> isset($l['REPROBACIONES'])? $l['REPROBACIONES']:null,
							//'sgcas'=> isset($l['SGCAS'])? $l['SGCAS']:null,
							//'wkmt'=> isset($l['WKMT'])? $l['WKMT']:null,
							//'fecha_wkmt'=> isset($l['FECHA_WKMT'])? $l['FECHA_WKMT']:null,
							
							'plantilla_id' => $l['RPLA_ID'],
							'covid_result_desc'=> isset($l['COVID_RESULT_DESC'])? $l['COVID_RESULT_DESC']:null,
							'covid_exam_fecha'=> isset($l['COVID_EXAM_FECHA'])? $l['COVID_EXAM_FECHA']:null,
							'documentos_validados'=> isset($l['DOCUMENTOS_VALIDADOS'])? $l['DOCUMENTOS_VALIDADOS']:null,
							'sueldo'=> isset($l['SUELDO'])? $l['SUELDO']:null,
							'nro_contrato'=> isset($l['NRO_CONTRATO'])? $l['NRO_CONTRATO']:null,
							);
					}, (array)$lista);
			return $postulantes;
	}

	
	public function getListaInduccionesGeneralesProgramadas()
	{
			$lista = $this->datos->getListaInduccionesProgramadas( $tipo_induccion= 6 );

			$inducciones = array_map(
					function( $l){
							
							return array(
							'tipo_id' => $l['RIND_ID'],
							'nombre' => $l['RIND_NOMBRE'],
							'fecha' => $l['FECHA'],
							'fecha_cl' => $l['FECHA_CL'],
							'hora' => $l['HORA'],
							'direccion' => $l['DIRECCION'],
							'link' => $l['LINK'],
							'cantidad' => $l['CANTIDAD'],
							);
					}, $lista);

			return $inducciones;
	}

	public function getListaInduccionesEspecificasProgramadas()
	{
			$lista = $this->datos->getListaInduccionesProgramadas( $tipo_induccion= 7 );

			$inducciones = array_map(
					function( $l){
							
							return array(
							'tipo_id' => $l['RIND_ID'],
							'nombre' => $l['RIND_NOMBRE'],
							'fecha' => $l['FECHA'],
							'fecha_cl' => $l['FECHA_CL'],
							'hora' => $l['HORA'],
							'direccion' => $l['DIRECCION'],
							'link' => $l['LINK'],
							'cantidad' => $l['CANTIDAD'],
							);
					}, $lista);

			return $inducciones;
	}

	public function getListaCursosOpeConductoresProgramados()
	{
			$lista = $this->datos->getListaInduccionesProgramadas( $tipo_induccion= 8 );

			$inducciones = array_map(
					function( $l){
							
							return array(
							'tipo_id' => $l['RIND_ID'],
							'nombre' => $l['RIND_NOMBRE'],
							'fecha' => $l['FECHA'],
							'fecha_cl' => $l['FECHA_CL'],
							'hora' => $l['HORA'],
							'direccion' => $l['DIRECCION'],
							'link' => $l['LINK'],
							'cantidad' => $l['CANTIDAD'],
							);
					}, $lista);

			return $inducciones;
	}


	public function getListaPostulantesInduccion( $ind_id, $fecha )
	{
			$lista = $this->datos->getListaPostulantesInduccion($ind_id, $this->usuario_id , $fecha );

			$inducciones = array_map(
					function( $l){
							
							return array(
							'id' => $l['POSTOFEIND_ID'],
							'tipo_id' => $l['RIND_ID'],
							'procp_id' => $l['PROCP_ID'],
							'post_id' => $l['POST_ID'],
							'nombre' => $l['NOMBRE'],
							'apaterno' => $l['APATERNO'],
							'celular' => $l['CELULAR'],
							'evaluacion' => $l['POSTOFEIND_EVALUACION'],
							'evaluacion_anterior' => $l['EVALUACION_ANTERIOR'],
							'cantidad_rechazos' => $l['CANTIDAD_RECHAZOS'],
							);
					}, $lista);

			return $inducciones;
	}

	public function getListaCorreoCOVIDpostulacion(){

			$lista = $this->datos->getListaCorreoProceso('POSTULANTE_PCR');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}
	
	public function getListaCorreoSGCASpostulacion(){

			$lista = $this->datos->getListaCorreoProceso('POSTULANTE_SGCAS');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}
	
	public function getListaCorreoWKMTpostulacion(){

			$lista = $this->datos->getListaCorreoProceso('POSTULANTE_WKMT');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoEncuestaAmbiental(){

			$lista = $this->datos->getListaCorreoProceso('ENCUESTA_AMBIENTAL');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoProgramacionTurno(){

			$lista = $this->datos->getListaCorreoProceso('PROGRAMACION_TURNO_MODIFICACION_TURNO');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoProgramacionTurnoCron(){

			$lista = $this->datos->getListaCorreoProceso('PROGRAMACION_TURNO_ENVIO_DOCUMENTO_CIERRE');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoProgramacionTurnoCreacion(){

			$lista = $this->datos->getListaCorreoProceso('PROGRAMACION_TURNO_REGISTRO_TURNO');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}


	public function getListaCorreoReclutamientoSolicitud(){

			$lista = $this->datos->getListaCorreoProceso('RECLUTAMIENTO_SOLICITUD');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoReclutamientoSolicitudValidacion(){

			$lista = $this->datos->getListaCorreoProceso('RECLUTAMIENTO_SOLICITUD_VALIDACION');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoPostulanteApruebaEntrevistaTecnica(){

			$lista = $this->datos->getListaCorreoProceso('POSTULANTE_APRUEBA_E_TECNICA');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoPostulanteApruebaExamenPreocupacional(){

			$lista = $this->datos->getListaCorreoProceso('POSTULANTE_APRUEBA_EX_PREOCUPACIONAL');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}

	public function getListaCorreoPostulanteApruebaContrato(){

			$lista = $this->datos->getListaCorreoProceso('POSTULANTE_APRUEBA_CONTRATACION');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}









	public function rechazarSolicitudOfertaLaboralPendiente( $sol_id ){
			return $this->datos->cambiarEstadoSolicitudReclutamiento( $sol_id, 2, $this->usuario_id );
	}

	public function rechazarCargoPerfilSolicitudOfertaLaboral( $cargoperfil_id ){
		return $this->datos->cambiarEstadoCargoPerfilSolReclutamiento( $cargoperfil_id, 0 , $this->usuario_id );
	}
	
	public function aprobarSolicitudOfertaLaboralPendiente( $sol_id ){
			return $this->datos->cambiarEstadoSolicitudReclutamiento( $sol_id, 3 , $this->usuario_id );
	}

	public function aprobarCargoPerfilSolicitudOfertaLaboral( $cargoperfil_id ){
		return $this->datos->cambiarEstadoCargoPerfilSolReclutamiento( $cargoperfil_id, 1 , $this->usuario_id );
	}
	
	public function cambiarEvaluadorCargoPerfilSolicitud( $perfil_id, $evaluador_usr_id ){
		return $this->datos->cambiarEvaluadorCargoPerfilSolReclutamiento( $perfil_id, $evaluador_usr_id , $this->usuario_id );
	}
	
	public function registrarEtapaPostulante( $rocpeta_id, $procp_id, $puntaje, $descripcion, $fecha_hora , $direccion, $link, $observacion , $aprobado ){
		$r = $this->datos->registrarEtapaPostulacion( $rocpeta_id, $procp_id, $puntaje, $descripcion, $fecha_hora, $direccion, $link , $observacion, $aprobado , $this->usuario_id );

		if( !is_array( $r ) || empty($r))
				return false;
//var_dump($r);
	//	$r = array_shift( $r );

		return array(
				'id'=>$r['ID'],
				'siguiente_etapa'=>$r['SIGUIENTE_ETAPA'],
				'solicitar_examen'=>$r['SOLICITAR_EXAMEN'],
		);
 
		/*
		$postofeeta_id = $r['ID'];

		if( $r['SIGUIENTE_ETAPA'] == 6){

				$this->datos->registrarSolExamenCovid19( $procp_id, $this->usuario_id );

				$correos = $this->getListaCorreoCOVIDpostulacion();
		
		}

		return $postofeeta_id;
*/
	}
	
	public function registrarSubEtapaPostulante( $postofeeta_id, $rseta_id, $aprobado ){
		return $this->datos->registrarSubEtapaPostulacion( $postofeeta_id, $rseta_id, $aprobado, $this->usuario_id );
	}
	
	public function registrarSolicitudSubEtapaPostulante( $postofeeta_id, $rseta_id, $mensaje = null){
		return $this->datos->registrarSolicitudEtapaExternaPostulacion( $postofeeta_id, $rseta_id, $mensaje );
	}

	public function registrarSolicitudEtapaPostulante( $post_id, $rocp_id, $tipo_etapa, $mensaje = null){
		return $this->datos->registrarSolicitudEtapaExternaPostulacion2( $post_id, $rocp_id, $tipo_etapa , $mensaje );
	}

	public function registrarEtapaExternaPostulante( $post_id, $rocp_id, $etapa_id , $puntaje, $aprobado ){
		return $this->datos->registrarEtapaExternaPostulacion(  $post_id, $rocp_id, $etapa_id , $puntaje, $aprobado , $this->usuario_id );
	}
	public function registrarEvaluacionCompetenciaPostulante( $rofecomp_id, $procp_id, $puntaje, $observacion ){
		return $this->datos->registrarEvaluacionCompetenciaPostulacion( $rofecomp_id, $procp_id, $puntaje, $observacion, $this->usuario_id );
	}
	
	public function registrarEvaluacionExamenPostulante( $procp_id, $rex_id, $apto = 0 ){
			return $this->datos->registrarEvaluacionExamenPostulacion( $procp_id, $rex_id, $apto );
	}
	
	public function registrarInduccionPostulante( $rocpeta_id,
																$post_id,
																$rind_id,
																$fecha_hora, 
																$direccion , 
																$link ){

			return $this->datos->registrarInduccionPostulacion( $rocpeta_id,
																$post_id,
																$rind_id,
																$this->usuario_id, 
																$fecha_hora, 
																$direccion , 
																$link );
	}
	
	public function registrarInduccionEvaluacion( $induccion_id, $evaluacion, $rocpeta_id  ){

			return $this->datos->registrarInduccionEvaluacion(  $induccion_id, $evaluacion, $rocpeta_id, $this->usuario_id );
	}

	public function registrarSolicitudExamenCOVID19Postulante($procp_id ){

			//return $this->datos->registrarExamen( $rut ,$nombre ,$email ,$celular ,$tipo ,$faena, $turno ,$laboratorio ,$lugar ,$fecha ,$hora ,$estado , $observacion , $this->usuario_id );
			return $this->datos->registrarSolExamenCovid19( $procp_id, $this->usuario_id );

	}

	public function getDetalleEtapaPostulacion( $procp_id, $rocpeta_id){

			$lista = $this->datos->getDetalleEtapasPostulacion( $procp_id, $rocpeta_id );

			if( empty( $lista) )
					return array();

			return array(
				'etapa_nombre'=>$lista[0]['ETAPA_NOMBRE'],
				'aprobado'=>$lista[0]['APROBADO'],
				'puntaje'=>$lista[0]['PUNTAJE'],
				'descripcion'=>$lista[0]['DESCRIPCION'],
				'fecha'=>$lista[0]['FECHA'],
				'hora'=>$lista[0]['HORA'],
				'minuto'=>$lista[0]['MINUTO'],
			);
	
	}

	public function getProgramacionTurnosSinValidar(){

			$lista = $this->datos->getProgramacionTurnoSinValidar();

			if( empty( $lista) )
					return array();
			
			return array_map(
					function($l){
						
							return array(
								'pt_id'=>$l['PT_ID'],
								'fecha'=>$l['FECHA'],
								'fecha_subida'=>$l['FECHA_SUBIDA'],
								'turno'=>$l['TURNO'],
								'cantidad'=>$l['CANTIDAD'],
								'cantidad_pendiente'=>$l['CANTIDAD_PENDIENTE'],
							);

					}, (array)$lista
			);
	
	}

	public function getProgramacionTurnosValidados( $por_vencer = null){

			$lista = $this->datos->getProgramacionTurnoPorEstado( $por_vencer );
//var_dump($lista);
			if( empty( $lista) )
					return array();
			
			return array_map(
					function($l){
						
							return array(
								'pt_id'=>$l['PT_ID'],
								'fecha'=>$l['FECHA'],
								'fecha_subida'=>$l['FECHA_SUBIDA'],
								'turno'=>$l['TURNO'],
								'cantidad'=>$l['CANTIDAD'],
								'cantidad_pendiente'=>$l['CANTIDAD_PENDIENTE'],
							);

					}, (array)$lista
			);
	
	}


	public function getProgramacionTurnoDetalle( $turno_id){

			$lista = $this->datos->getProgramacionTurnoDetalle( $turno_id );

			if( empty( $lista) )
					return array();
			
			return array_map(
					function($l){

							$turnos = array();
							$turnos_2 = array();

							if(isset($l['OTRO_TURNO']) && !empty($l['OTRO_TURNO']) ){

									$turnos = explode( ';', $l['OTRO_TURNO'] );

									foreach( (array)$turnos as $t){

											if(!empty($t)){
													
													list($turno, $fecha) = explode('|', $t);
													
													array_push( $turnos_2, 
															array(
																	'turno' =>$turno,
																	'fecha'=>$fecha
															));
											}
									}
							}

							return array(
								'id'=>$l['PTD_ID'],
								'pt_id'=>$l['PT_ID'],
								'estado'=>$l['PTE_ID'],
								'estado_descripcion'=>$l['ESTADO_DESCRIPCION'],
								'rut'=>$l['RUT'],
								'dv'=>$l['DV'],
								'nombre'=>$l['NOMBRE'],
								'cargo'=>$l['CARGO'],
								'turno'=>$l['TURNO'],
								'telefono'=>$l['TELEFONO'],
								'comuna'=>$l['COMUNA'],
								'observacion'=>$l['OBSERVACION'],
								'contrato'=>$l['CONTRATO'],
								'examen'=>$l['EXAMEN'],
								'encuesta'=>$l['ENCUESTA'],
								'fecha_venc_induccion'=>$l['FECHA_VENC_INDUCCION'],
								'fecha_venc_altura_geo'=>$l['FECHA_VENC_ALTURA_GEO'],
								'estado_induccion'=>$l['ESTADO_INDUCCION'],
								'estado_altura_geo'=>$l['ESTADO_ALTURA_GEO'],
								'conflicto'=> $turnos_2
							);

					}, (array)$lista
			);
	
	}

	public function getProgramacionTurnosTransportes(){

			$lista = $this->datos->getProgramacionTurnoTransportes();

			if( empty( $lista ) )
					return array();
			
			return array_map(
					function($l){
						
							return array(
								//'pt_id'=>$l['PT_ID'],
								'ptt_id'=>$l['PTT_ID'],
								'patente'=>$l['PATENTE'],
								'fecha_subida'=>$l['FECHA_SUBIDA'],
								'fecha_cierre'=>$l['PTT_FECHA_CIERRE'],
								'cierre'=>$l['PTT_CIERRE'],
								'cantidad'=>$l['CANTIDAD'],
							);

					}, (array)$lista
			);
	
	}


	public function getProgramacionTurnoTransporteDetalle( $transporte_id, $patente ){

			$lista = $this->datos->getProgramacionTurnoTransporteDetalle( $transporte_id, $patente );

			if( empty( $lista) )
					return array();
			
			return array_map(
					function($l){
						
							return array(
								'id'=>$l['PTD_ID'],
								'pt_id'=>$l['PT_ID'],
								'rut'=>$l['RUT'],
								'dv'=>$l['DV'],
								'nombre'=>$l['NOMBRE'],
								'cargo'=>$l['CARGO'],
								'turno'=>$l['TURNO'],
								'telefono'=>$l['TELEFONO'],
								'comuna'=>$l['COMUNA'],
								'observacion'=>$l['OBSERVACION'],
								'contrato'=>$l['CONTRATO'],
								'examen'=>$l['EXAMEN'],
								'encuesta'=>$l['ENCUESTA'],
								'fecha_venc_induccion'=>$l['FECHA_VENC_INDUCCION'],
								'fecha_venc_altura_geo'=>$l['FECHA_VENC_ALTURA_GEO'],
								'estado_induccion'=>$l['ESTADO_INDUCCION'],
								'estado_altura_geo'=>$l['ESTADO_ALTURA_GEO'],
							);

					}, (array)$lista
			);
	
	}

	public function getProgramacionTurnoEstados(){

			$lista = $this->datos->getProgramacionTurnoEstados();

			if( empty( $lista ) )
					return array();
			
			return array_map(
					function($l){
						
							return array(
								'id'=>$l['PTE_ID'],
								'descripcion'=>$l['PTE_DESCRIPCION'],
							);

					}, (array)$lista
			);
	
	}
	
	public function getProgramacionTurnoRutFecha($rut, $turno, $fecha){

			$lista = $this->datos->getProgramacionTurnoRutFecha( $rut, $turno, $fecha);

			if( empty( $lista ) )
					return array();
			
			return array_map(
					function($l){
						
							return array(
								'pt_id'=>$l['PT_ID'],
								'ptd_id'=>$l['PTD_ID'],
								'pte_id'=>$l['PTE_ID'],
								'turno'=>$l['PT_TURNO'],
								'fecha_subida'=>$l['FECHA_SUBIDA'],
								'rut'=>$l['RUT'],
								'dv'=>$l['DV'],
								'nombre'=>$l['NOMBRE'],
							);

					}, (array)$lista
			);
 
	}
	
	public function getInformeTurnosPDF( $fecha = null ){//yyyy-mm-dd
		
				require_once('../servicios/TCPDF/tcpdf.php');

				$nombre_completo ="";

				$telefono = "";

				$email = "";

				$competencias = array();

				$transportes = $this->datos->getProgramacionTurnoTransportesSinVencer($fecha);

				if( empty($transportes) )
					 return false;	
			

				//$oferta_id = $oferta[0]['ROFE_ID'];

				//$oferta_cargo = $oferta[0]['CPERF_DESCRIPCION'];

				$pdf = new TCPDF('p', 'mm', array( 216, 330 ) , true, 'UTF-8', false);
				
				$pdf->SetPrintHeader(false);
				
				$pdf->SetPrintFooter(false);

				
				$pdf->SetMargins(10, 10,10, 10);
				
				//$pdf->SetAutoPageBreak(TRUE, 5);
				$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_FOOTER);

				$pdf->AddPage();
		
				//$txt="TRANSPORTE DE PERSONAL";
				
				$pdf->SetFont('helvetica', 'B', 10);


				$pdf->MultiCell(0, 5, date('d-m-Y'), 0, 'R', 0, 0, '', '', true );
			
			//	$pdf->MultiCell(0, 5, $txt, 0, 'L', 0, 0, '', '', true );
				$pdf->Image('https://medioambiente.todoacero.cl/img/ta2018_.3f09f372.png', 10, 10, 130, '', '', 'https://www.todoacero.cl', '', false, 300 );
				$pdf->Ln();
				$pdf->Ln();
				$pdf->SetTextColor( 93, 109, 126 );
				$pdf->SetFont('helvetica', 'B', 17);
				//$pdf->Cell(0, 0, $oferta_cargo , 0, 0, $align = 'L', $fill = false);

				$pdf->Ln();

				
				$pdf->SetFont('helvetica', 'N', 12);

				$pdf->SetTextColor( 0 , 0 , 0 );

	
				$pdf->Ln();
				$pdf->Ln();

				$pdf->Ln();

				$contador=0;
				foreach ( $transportes as $t ){
				
						$lista = $this->datos->getProgramacionTurnoTransporteDetalle( $t['PTT_ID'], $t['PATENTE'] );
						$contador+=count($lista);
						unset( $lista );
				}

				$pdf->SetFont('helvetica', 'B', 11);
				$pdf->Cell(0, 0, "TRANSPORTE DE PERSONAL - ".$contador, 0, 0, $align = 'L', $fill = false);
				$pdf->Ln();


			//	$pdf->SetFont('helvetica', 'B', 11);
			//	$pdf->Cell(0, 0, $txt , 0, 0, $align = 'L', $fill = false);
			//	$pdf->Ln();

				
				$style = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 10, 'color' => array(0, 0, 0));

				$j = 0;


				foreach ( $transportes as $t ){
						
						
						$pdf->SetFont('helvetica', 'B', 11);
						$estado = !$t['PTT_CIERRE'] ? 'ABIERTO' : 'CERRADO';
						
						$fecha_cierre = $t['PTT_FECHA_CIERRE'];
						$fecha_cierre = empty($fecha_cierre)? null : date_format(date_create($fecha_cierre), 'd-m-Y H:i');

						$fecha_subida = $t['FECHA_SUBIDA'];
						$fecha_subida = empty($fecha_subida)? null : date_format(date_create($fecha_subida), 'd-m-Y');
	
						$pdf->Ln();
						$pdf->Ln();


						$pdf->Cell(30, 0, 'Patente' ,1 , 0, $align = 'L', $fill = false);
						$pdf->Cell(30, 0,  'Estado' ,1 , 0, $align = 'L', $fill = false);

						$pdf->Cell(40, 0, 'Fecha de subida',1 , 0, $align = 'L', $fill = false);
						$pdf->Cell(40, 0, 'Fecha de cierre',1 , 0, $align = 'L', $fill = false);
						
						$pdf->Cell(0, 0, 'Pasajeros' ,1 , 0, $align = 'L', $fill = false);
						$pdf->Ln();

						$pdf->SetFont('helvetica', 'N', 11);

						$pdf->Cell(30, 0, $t['PATENTE'] ,1 , 0, $align = 'L', $fill = false);
						$pdf->Cell(30, 0,  $estado ,1 , 0, $align = 'L', $fill = false);

						$pdf->Cell(40, 0, $fecha_subida ,1 , 0, $align = 'L', $fill = false);
						$pdf->Cell(40, 0, $fecha_cierre ,1 , 0, $align = 'L', $fill = false);

						$pdf->Cell(0, 0, $t['CANTIDAD'] ,1 , 0, $align = 'R', $fill = false);
						
						$pdf->Ln();
					
						$lista = $this->datos->getProgramacionTurnoTransporteDetalle( $t['PTT_ID'], $t['PATENTE'] );


						$pdf->SetFont('helvetica', 'B', 10);

						$pdf->Cell(25, 10, 'RUT' ,0 , 0, $align = 'L', $fill = false);
						$pdf->Cell(55, 10, 'NOMBRE',0 , 0, $align = 'L', $fill = false);
						$pdf->Cell(25, 10, 'TELEFONO' ,0 , 0, $align = 'L', $fill = false);
						//$pdf->Cell(32, 10, 'COMUNA' ,0 , 0, $align = 'L', $fill = false);
						$pdf->Cell(32, 10, 'PASE MOV.' ,0 , 0, $align = 'L', $fill = false);
						$pdf->Cell(0, 10, 'CARGO' ,0 , 0, $align = 'L', $fill = false);

						$pdf->Ln();
					
						$pdf->SetFont('helvetica', 'N', 9);
						
						$i = 0;

						foreach( $lista as $l){

								if( $i>0  )
										$pdf->Ln();
								

								$x = $pdf->getX();
								$y = $pdf->getY();

								$rut = number_format($l['RUT'], 0, ',','.') .'-'. $l['DV'];
								$nombre= ucwords( strtolower( $l['NOMBRE'] ) );
								$telefono = $l['TELEFONO'];
								$comuna = ucfirst( strtolower( $l['COMUNA']));
								$cargo= ucfirst( strtolower( $l['CARGO']));
								$turno = $l['TURNO'];
								$validacion = $l['PTD_VALIDACION_APP'];
								
								$pase_movilidad = '';

								switch( $l['PTD_PASE_MOVILIDAD_HABILITADO']){
										case -1: $pase_movilidad = 'NO PRESENTA'; break;
										case 0: $pase_movilidad = 'NO HABILITADO';  break;
										case 1: $pase_movilidad = 'HABILITADO';  break;
								}

								$pdf->Cell(25, 0, $rut ,0 , 0, $align = 'L', $fill = false);
								$x = $pdf->getX();
								//$pdf->Cell(55, 0, $nombre ,0 , 0, $align = 'L', $fill = false);
								$pdf->MultiCell(55, 0, $nombre, 0, 'L', 0, 0, $x, '', true );
								$y1 = $pdf->getY();
								$pdf->Cell(25, 0, $telefono ,0 , 0, $align = 'L', $fill = false);
								//$pdf->Cell(32, 0, $comuna ,0 , 0, $align = 'L', $fill = false);
								$pdf->Cell(32, 0, $pase_movilidad ,0 , 0, $align = 'L', $fill = false);
								$x = $pdf->getX();
								$pdf->setY($y1);
								$pdf->MultiCell(0, 0, $cargo, 0, 'L', 0, 0, $x, '', true );
								$y2 = $pdf->getY();
		
								$i++;

						}
						
						$pdf->Ln();
						$pdf->Ln();

						$this->datos->actualizarProgramacionTurnoTransporteInformeEnviado( $t['PTT_ID'] );
				
				}

				$pdf->Ln();

				$transportes_id = array_column( $transportes, 'PT_ID');

				$transportes_id = array_unique( $transportes_id );

				$contador=0;

				$lista = $this->datos->getProgramacionTurnoDetalleNoAbordo( $fecha );
		
				$contador=count($lista);
				
				$pdf->SetFont('helvetica', 'B', 10);
				$pdf->Cell(0, 0, 'PERSONAL SIN TRANSPORTAR - '.$contador,0 , 0, $align = 'L', $fill = false);

				$pdf->Ln();
				$pdf->Ln();
				$pdf->SetFont('helvetica', 'N', 9);



						$i = 0;

						foreach( $lista as $l){

								//$x = $pdf->getX();
								$y = $pdf->getY();

								$rut = number_format($l['RUT'], 0, ',','.') .'-'. $l['DV'];

								$nombre= ucwords( strtolower( $l['NOMBRE'] ) );

								$telefono = $l['TELEFONO'];

								$comuna = ucfirst( strtolower( $l['COMUNA']));

								$cargo= ucfirst( strtolower( $l['CARGO']));

								$turno = $l['TURNO'];
								
								$estado = ( $l['PTE_DESCRIPCION']=='ACTIVO' ) ? 'SIN ABORDAR': $l['PTE_DESCRIPCION'];

								$validacion = $l['PTD_VALIDACION_APP'];

								$pdf->Cell(25, 0, $rut ,0 , 0, $align = 'L', $fill = false);
								//$pdf->Cell(55, 0, $nombre ,0 , 0, $align = 'L', $fill = false);
								$x = $pdf->getX();
								$pdf->MultiCell(55, 0, $nombre, 0, 'L', 0, 0, $x, '', true );
								
								$y1 = $pdf->getY();
								$pdf->Cell(25, 0, $telefono ,0 , 0, $align = 'L', $fill = false);
								//$pdf->Cell(32, 0, $comuna ,0 , 0, $align = 'L', $fill = false);
								$x = $pdf->getX();
								$pdf->setY($y1);
								$pdf->setX($x+55);
								$pdf->Cell(32, 0, $estado ,0 , 0, $align = 'L', $fill = false);

								$pdf->setX($x);
								$pdf->MultiCell(55, 0, $cargo, 0, 'L', 0, 0, '', '', true );
								
								$pdf->Ln();

								$i++;

						}


				return $pdf;
	}


/*
	public function marcarProgramacionTurnoInformeTransporte( $pPTT_ID ){

			$this->datos->actualizarProgramacionTurnoTransporteInformeEnviado( $pPTT_ID );

	}
 */




}

 ?>
