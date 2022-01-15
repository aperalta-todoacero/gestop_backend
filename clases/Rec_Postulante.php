<?php

require_once dirname(__DIR__).'/datos/Datos.php';

require_once '../servicios/JWT/JWT.php';

require_once '../servicios/JWT/ExpiredException.php';

require_once 'Rec_Documento.php';

use \Firebase\JWT\JWT;


class Rec_Postulante
{
		protected $id;
		protected $rut;
		protected $dv;
		protected $nombre;
		protected $apaterno;
		protected $amaterno;

		protected $email;
		protected $fecha_nacimiento;
		protected $nacionalidad_id;
		protected $nacionalidad_nombre;
		protected $sexo_id;
		protected $estado_civil_id;
		protected $estado_civil_nombre;
		protected $tel_celular;
		protected $tel_fijo;
		protected $tel_contacto;
		protected $nivel_educacional_id;
		protected $nivel_educacional_nombre;
		protected $profesion;
		protected $nombre_contacto;

		protected $afp_id;
		protected $afp_nombre;
		protected $salud_id;
		protected $salud_nombre;
		protected $salud_uf;
		

		protected $direccion;
		protected $ciudad;
		protected $talla_pantalon;
		protected $talla_calzado;
		protected $talla_camisa;
		protected $banco_id;
		protected $banco_nombre;
		protected $tipo_cuenta_id;
		protected $tipo_cuenta_nombr;

		protected $cuenta_bancaria;

		protected $estado_id;
		protected $estado_descripcion;

		protected $postulacion_id;

		protected $postulaciones;

		protected $documentos;

		protected $ponderacion;

		private $datos;

		private $error;

		function __construct()
		{
				$this->datos = new Datos();
		}
	 
		public static function LoginToken($token)
		{

				
				$key = date('dmY');

				try{
			
						$decoded = JWT::decode($token, $key, array('HS256'));
				
						if( isset( $decoded->sub ) && isset( $decoded->exp ) && $decoded->exp > time() ){
        						
								$instancia = new Self();
					
								//if( !$instancia->id = $instancia->datos->validaToken( $decoded->sub, $token ) )
								//		return false;

						$instancia->setId( $decoded->sub );
						//$instancia->setUsuario( $decoded->sub );
						//$instancia->setExpiracion( $decoded->exp );
						return $instancia;
				
				}else{
						
						return false;
				
				}      
	
		}catch(Exception $e){
		
				return false;
		
		}
	
		}

		
		public function getToken(){
				if( $this->id ){
						
						$key = date('dmY');//debe invalidarse a final del dia
						$time = time();
						//$exp = $time + 60*60*1;
						$exp = $time + 60*60*1;

						$payload = array(
								"iss" => "gestopweb",
								"sub" => $this->id,
								"iat" => $time, //emitido
								"nbf" => $time, //no usar antes de
								"exp" => $exp //no usar antes de
						);

						$jwt = JWT::encode($payload, $key);

						if( strlen($jwt) < 10 )
								return false;
						else
								return $jwt;

				}else{
						return false;
				}
		}
		
		public function login( $pass ){

				$r = $this->datos->postulanteLogin( $this->rut , $pass );

				if( $r === false ){

						$this->error = 'Usuario no encontrado';

						return false;
						$this->setId( $user_id );

				}else if( $r === 0 ){

						$this->error = 'Clave o usuario incorrecto';

						return 0;
				}else if( is_numeric($r) ){
						$this->setId($r);

						return true;				
				}else{

						$this->error ="Error en los datos devueltos";
						return false;

				}
		}

		public function crearClave( $pass ){
		
				if( $user_id = $this->datos->crearAccesoPostulante( $this->rut , $this->dv , $pass ) ){

						$this->setId( $user_id );

						return true;

				}else{

						$this->error = 'No fue posible crear el usuario';

						return false;

				}
		}
	
	public function cambiarClavePorRut( $rut, $new_pass )
	{

			if( empty( $rut ) || empty( $new_pass ) ){

					$this->error = "Faltan datos para cambiar la clave";
					
					return false;
			}
			else{
					
					$r = $this->datos->resetClavePorRutPostulacion( $rut , $new_pass ); 
					
					if(!$r)
							$this->error ="No fue posible actualizar su clave, por favor comuniquese con al área de Gestion de Personas";
					
					return $r;
			}
	}


		public function setDatos( $rocp_id = null ){

				if( $r = $this->datos->getDatosPostulante( $this->id, $rocp_id ) ){
				
						$datos = $r[0];

						$this->rut= $datos['RUT'] ;
						$this->dv = $datos['DV'] ;
						$this->nombre = $datos['NOMBRE'] ;
						$this->apaterno = $datos['APATERNO'] ;
						$this->amaterno = $datos['AMATERNO'] ;
						$this->email = $datos['EMAIL'];
						$this->fecha_nacimiento = $datos['FECHA_NACIMIENTO'];
						$this->nacionalidad_id = $datos['NACIONALIDAD'];
						$this->nacionalidad_nombre = $datos['NACIONALIDAD_NOMBRE'];
						$this->sexo_id = $datos['SEXO'];
						$this->estado_civil_id = $datos['ESTADO_CIVIL'];
						$this->estado_civil_nombre = $datos['ESTADO_CIVIL_NOMBRE'];
						$this->tel_celular = $datos['TEL_CELULAR'];
						$this->tel_fijo = $datos['TEL_FIJO'];
						$this->tel_contacto = $datos['TEL_CONTACTO'];
						$this->nivel_educacional_id = $datos['NIVEL_EDUCACIONAL'];
						$this->nivel_educacional_nombre = $datos['NIVEL_EDUCACIONAL_NOMBRE'];
						$this->profesion = $datos['PROFESION'];
						$this->nombre_contacto = $datos['NOMBRE_CONTACTO'];
						$this->afp_id = $datos['AFP_ID'];
						$this->afp_nombre = $datos['AFP_NOMBRE'];
						$this->salud_id = $datos['SALUD_ID'];
						$this->salud_nombre = $datos['SALUD_NOMBRE'];
						$this->salud_uf = $datos['SALUD_UF'];
						$this->direccion = $datos['DIRECCION'];
						$this->ciudad = $datos['CIUDAD'];
						$this->talla_pantalon = $datos['TALLA_PANTALON'];
						$this->talla_calzado = $datos['TALLA_CALZADO'];
						$this->talla_camisa = $datos['TALLA_CAMISA'];
						$this->banco_id = $datos['BE_ID'];
						$this->banco_nombre = $datos['BE_NOMBRE'];
						$this->tipo_cuenta_id = $datos['TCBE_ID'];
						$this->tipo_cuenta_nombre = $datos['TCBE_NOMBRE'];
						$this->cuenta_bancaria= $datos['CUENTA_BANCARIA'];

						$this->postulacion_id= $datos['PROCP_ID'];

						return true;

				}else{
						return false;
				}
				
		}
		
		public function cargarId(){

				if( $r = $this->datos->getIdPostulante( $this->rut) ){
						$this->id = $r;
						return true;
				}else{
						return false;
				}
		}

		public function actualizarDatosPersonales(){

				if( !is_numeric( $this->id ) ){
						$this->error ="No se encuentra el identificador";
						return false;
				}

				if( $this->datos->actualizarDatosPostulante( 
						$this->id, 
						$this->nombre, 
						$this->apaterno , 
						$this->amaterno, 
						$this->email,
						$this->fecha_nacimiento,
						$this->nacionalidad_id,
						$this->sexo_id,
						$this->estado_civil_id,
						$this->tel_celular,
						$this->tel_fijo,
						$this->tel_contacto,
						$this->nivel_educacional_id,
						$this->profesion,
						$this->nombre_contacto,
						$this->afp_id,
						$this->salud_id,
						$this->salud_uf,
						$this->direccion,
						$this->ciudad,
						$this->talla_pantalon,
						$this->talla_calzado,
						$this->talla_camisa,
						$this->banco_id,
						$this->tipo_cuenta_id,
						$this->cuenta_bancaria
			 	)
				){
						$this->error='';

						return true;

				}else{
						$this->error="Ocurrio un problema al intentar actualizar los datos personales";
						return false;
				}
				
		}
		
		public function postular( $rocp_id ){

				if( !is_numeric( $this->id ) || !is_numeric( $rocp_id ) ){
						$this->error ="No se encuentra el identificador";
						return false;
				}

				if( ! $postulacion_id = $this->datos->guardarPostulacion(	$this->id, $rocp_id )){
						$this->error="Ocurrio un problema al intentar regiatrar la postulacion";
						return false;
				}
				
				return $postulacion_id;
		}
		


		public function getListaPostulaciones( $oferta_cargo_perfil_id = null ){

				$lista = $this->datos->getPostulacionesReclutamiento( $this->id , $oferta_cargo_perfil_id ); 


				return array_map( 

						function( $l ){

								return array(
										'rofe_id'=>$l['ROFE_ID'],
										'subetapa_id'=>$l['ROCP_ID'],
										'rocp_id'=>$l['ROCP_ID'],//es la misma subetapa_id
										'procp_id'=>$l['PROCP_ID'],
										'fecha_publicacion'=>$l['FECHA_PUBLICACION'],
										'fecha_cierre'=>$l['FECHA_CIERRE'],
										'faena_nombre'=>$l['FAENA_NOMBRE'],
										'area_nombre'=>$l['AREA_NOMBRE'],
										'perfil_nombre'=>$l['PERFIL_NOMBRE'],
								);
						
						}, (array)$lista );
		}
		
		
		public function getUltimaPostulacion(){

				$lista = $this->datos->getUltimaPostulacionReclutamiento( $this->id ); 


				return array_map( 

						function( $l ){

								return array(
										'rofe_id'=>$l['ROFE_ID'],
										'rocp_id'=>$l['ROCP_ID'],
										'procp_id'=>$l['PROCP_ID'],
										'fecha_publicacion'=>$l['FECHA_PUBLICACION'],
										'fecha_cierre'=>$l['FECHA_CIERRE'],
										'faena_nombre'=>$l['FAENA_NOMBRE'],
										'area_nombre'=>$l['AREA_NOMBRE'],
										'perfil_nombre'=>$l['PERFIL_NOMBRE'],
								);
						
						}, (array)$lista );
		}


		public function getListaNacionalidad(){

				$lista = $this->datos->getNacionalidades();

				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'nombre'=>$l['NOMBRE'],
								);

						}, (array)$lista );
		}


		public function getListaNivelEducacional(){

				$lista = $this->datos->getNivelesEducacional();

				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'nombre'=>$l['DESCRIPCION'],
								);

						}, (array)$lista );
		}

		
		public function getListaEstadoCivil(){

				$lista = $this->datos->getEstadosCivil();

				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'nombre'=>$l['DESCRIPCION'],
								);

						}, (array)$lista );
		}
		
		public function getListaAFP(){

				$lista = $this->datos->getListaAFP();

				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'nombre'=>$l['NOMBRE'],
										'porcentaje'=>$l['PORCENTAJE']
								);

						}, (array)$lista );
		}
		
		public function getListaSalud(){

				$lista = $this->datos->getListaSalud();

				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'nombre'=>$l['NOMBRE'],
										'porcentaje'=>$l['PORCENTAJE']
								);

						}, (array)$lista );
		}

		
		public function getListaBanco(){

				$lista = $this->datos->getListaBanco();

				$bancos = $lista[0];

				$tipo_cuentas = $lista[1];

				$r = array();

				$r['bancos'] = array_map( 
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'nombre'=>$l['NOMBRE'],
								);

						}, (array)$bancos );

				
				$r['tipo_cuentas'] = array_map(
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'nombre'=>$l['NOMBRE'],
								);

						}, (array)$tipo_cuentas );

				return $r;
		}

		public function getListaDocumentosRequeridos( $oferta_cargo_perfil_id = null){

				$lista = $this->datos->getReqCargoPerfilOferta($oferta_cargo_perfil_id, $this->id );

				return array_map( 
						function( $l ){

								$doc = new Rec_Documento();
								$doc->setId( $l['ID'] );
								$doc->setTipoId( $l['TIPO_ID'] );
								$doc->setDescripcion( $l['DESCRIPCION'] );
								$doc->setIdDocPostulacion( $l['PDO_ID'] );

								return $doc;								
						}, (array)$lista );
		}
	
		public function getListaEtapasPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getListaEtapasOfertaCargoPerfil($oferta_cargo_perfil_id, $this->id );

				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ID'],
										'tipo_id'=>$l['TIPO_ID'],
										'reta_id'=>$l['ETAPA_ID'],
										'descripcion'=>$l['DESCRIPCION'],
										'tipo_descripcion'=>$l['TIPO_DESCRIPCION'],
										'puntaje'=>$l['PUNTAJE'],
										'puntaje_descripcion'=>$l['PUNTAJE_DESCRIPCION'],
										'fecha'=>$l['FECHA'],
										'fecha_corta'=>$l['FECHA_CORTA'],
										'hora'=>$l['HORA'],
										'minuto'=>$l['MINUTO'],
										'link'=>$l['LINK'],
										'direccion'=>$l['DIRECCION'],
										'observacion'=>$l['OBSERVACION'],
										'aprobado'=>$l['POSTOFEETA_APROBADO'],
										'estado'=>$l['ESTADO'],

								);
						
						}, (array)$lista );

		}

		
		public function getListaExamenesPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getListaExamenOfertaCargoPerfil($oferta_cargo_perfil_id, $this->id );

				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['REX_ID'],
										'tipo_id'=>$l['REXTIP_ID'],
										'titulo'=>$l['REX_TITULO'],
										'descripcion'=>$l['REX_DESCRIPCION'],
										'apto'=>$l['APTO'],
								);
						
						}, (array)$lista );
		}
		
		public function getListaCompetenciasPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getCompetenciasOfertaPerfil($oferta_cargo_perfil_id, $this->id ,null );
				
				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ROFECOMP_ID'],
										'tipo_id'=>$l['COMP_ID'],
										'titulo'=>$l['COMP_TITULO'],
										'descripcion'=>$l['COMP_DESCRIPCION'],
										'puntaje_minimo'=>$l['ROFECOMP_PUNTAJE_MINIMO'],
										'puntaje_maximo'=>$l['ROFECOMP_PUNTAJE_MAXIMO'],
										'puntaje'=>$l['POEV_PUNTAJE'],
										'observacion'=>$l['POEV_OBSERVACION'],
								);
						
						}, (array)$lista );
		}
		
		public function getListaCompetenciasTecnicasPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getCompetenciasOfertaPerfil($oferta_cargo_perfil_id, $this->id , $tipo=2 );
				
				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ROFECOMP_ID'],
										'tipo_id'=>$l['COMP_ID'],
										'titulo'=>$l['COMP_TITULO'],
										'descripcion'=>$l['COMP_DESCRIPCION'],
										'puntaje_minimo'=>$l['ROFECOMP_PUNTAJE_MINIMO'],
										'puntaje_maximo'=>$l['ROFECOMP_PUNTAJE_MAXIMO'],
										'puntaje'=>$l['POEV_PUNTAJE'],
										'observacion'=>$l['POEV_OBSERVACION'],
								);
						
						}, (array)$lista );
		}
		
		public function getListaCompetenciasBlandasPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getCompetenciasOfertaPerfil($oferta_cargo_perfil_id, $this->id , '1,3' );
				
				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['ROFECOMP_ID'],
										//'tipo_id'=>$l['COMP_ID'],
										'comp_id'=>$l['COMP_ID'],
										'tipo_id'=>$l['COMPTIP_ID'],
										'titulo'=>$l['COMP_TITULO'],
										'descripcion'=>$l['COMP_DESCRIPCION'],
										'puntaje_minimo'=>$l['ROFECOMP_PUNTAJE_MINIMO'],
										'puntaje_maximo'=>$l['ROFECOMP_PUNTAJE_MAXIMO'],
										'puntaje'=>$l['POEV_PUNTAJE'],
										'observacion'=>$l['POEV_OBSERVACION'],
								);
						
						}, (array)$lista );
		}

		public function getListaInduccionesGeneralesPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getListaInduccionOfertaCargoPerfil($oferta_cargo_perfil_id, $this->id  , $tipo_induccion = 6);
				//$lista = $this->datos->getListaInduccionOfertaCargoPerfil($oferta_cargo_perfil_id, $this->id  , $reta_id = 3 );
				
				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['CPIND_ID'],
										'tipo_id'=>$l['RIND_ID'],
										'nombre'=>$l['RIND_NOMBRE'],
										'evaluacion_id'=>$l['POSTOFEIND_ID'],
										//'fecha'=>$l['POSTOFEIND_FECHA'],
										'fecha'=>$l['FECHA'],
										'hora'=>$l['HORA'],
										'evaluacion'=>$l['POSTOFEIND_EVALUACION'],
										'direccion'=>$l['POSTOFEIND_DIRECCION'],
										'link'=>$l['POSTOFEIND_LINK'],
								);
						
						}, (array)$lista );
		}

	
		public function getListaInduccionesEspecificasPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getListaInduccionOfertaCargoPerfil($oferta_cargo_perfil_id, $this->id , $tipo_induccion = 7);
				
				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['CPIND_ID'],
										'tipo_id'=>$l['RIND_ID'],
										'nombre'=>$l['RIND_NOMBRE'],
										'evaluacion_id'=>$l['POSTOFEIND_ID'],
										//'fecha'=>$l['POSTOFEIND_FECHA'],
										'fecha'=>$l['FECHA'],
										'hora'=>$l['HORA'],
										'evaluacion'=>$l['POSTOFEIND_EVALUACION'],
										'direccion'=>$l['POSTOFEIND_DIRECCION'],
										'link'=>$l['POSTOFEIND_LINK'],

								);
						
						}, (array)$lista );
		}
		
		public function getListaCursosOpeConductores( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getListaInduccionOfertaCargoPerfil($oferta_cargo_perfil_id, $this->id , $tipo_induccion = 8);
				
				return array_map( 
						function( $l ){

								return array(
										'id'=>$l['CPIND_ID'],
										'tipo_id'=>$l['RIND_ID'],
										'nombre'=>$l['RIND_NOMBRE'],
										'evaluacion_id'=>$l['POSTOFEIND_ID'],
										//'fecha'=>$l['POSTOFEIND_FECHA'],
										'fecha'=>$l['FECHA'],
										'hora'=>$l['HORA'],
										'direccion'=>$l['POSTOFEIND_DIRECCION'],
										'link'=>$l['POSTOFEIND_LINK'],
										'evaluacion'=>$l['POSTOFEIND_EVALUACION'],
								);
						
						}, (array)$lista );
		}
			
		public function getListaSubEtapasSGCASPostulacion( $oferta_cargo_perfil_id ){

				$lista = $this->datos->getSubEtapaReclutamiento( $oferta_cargo_perfil_id , $this->id , $recetatip_id = 7 ); 
				return array_map( 

						function( $l ){

								return array(
										'postofeeta_id'=>$l['POSTOFEETA_ID'],
										'subetapa_id'=>$l['RSETA_ID'],
										'descripcion'=>$l['RSETA_DESCRIPCION'],
										'aprobado'=>$l['POSETA_APROBADO'],
										'estado'=>$l['ESTADO'],
										'fecha'=>$l['POSETA_FECHA'],
										'fecha_correo'=>$l['FECHA_ULTIMO_CORREO'],
										'etiqueta'=>$l['RSETA_ETIQUETA'],
								);
						
						}, (array)$lista );
		}
	
		
		public function getListaSubEtapasPostulacion( $oferta_cargo_perfil_id, $rocpeta_id){

				$lista = $this->datos->getSubEtapaEtapaReclutamiento( $oferta_cargo_perfil_id , $this->id , $rocpeta_id ); 
				return array_map( 

						function( $l ){

								return array(
										'postofeeta_id'=>$l['POSTOFEETA_ID'],
										'subetapa_id'=>$l['RSETA_ID'],
										'descripcion'=>$l['RSETA_DESCRIPCION'],
										'aprobado'=>$l['POSETA_APROBADO'],
										'estado'=>$l['ESTADO'],
										'fecha'=>$l['POSETA_FECHA'],
										'fecha_correo'=>$l['FECHA_ULTIMO_CORREO'],
										'etiqueta'=>$l['RSETA_ETIQUETA'],
								);
						
						}, (array)$lista );
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

	public function getUltimoExamenCovidPostulante( ){

			$lista = $this->datos->getUltimoExamenCovidPostulante( $this->id );

			if( empty( $lista) )
					return array();

			return array(
				'examen_id'=>$lista[0]['EXAM_ID'],
				'fecha'=>$lista[0]['EXAM_FECHA'],
				'hora'=>$lista[0]['EXAM_HORA'],
				'lugar'=>$lista[0]['EXAM_LUGAR'],
				'laboratorio'=>$lista[0]['EXAM_LABORATORIO'],
				'resultado'=>$lista[0]['RESULTADO_DESCRIPCION'],
				'tipo_examen'=>$lista[0]['TIPEXAM_TITULO'],
			);
	
	}


/*************  GETTERS   **************/

		public function getError(){
				return $this->error;
		}

		public function getId()
		{
				return $this->id;
		}

		public function getPostulacionId(){ //deberia se objeto
				return $this->postulacion_id;
		}

		public function getRut()
		{
				return $this->rut;
		}
		public function getDV() {
				return $this->dv;
		}

		public function getNombre() {
				return $this->nombre;
		}

		public function getApaterno() {
				return $this->apaterno;
		}

		public function getAmaterno() {
				return $this->amaterno;
		}

		public function getEmail(){
				return $this->email;
		}

		public function getEstadoId() {
				return $this->estado_id;
		}


		public function getFechaNacimiento(){
				return $this->fecha_nacimiento;
		}

		public function getNacionalidadId(){
				return $this->nacionalidad_id;
		}

		public function getNacionalidadNombre(){
				return $this->nacionalidad_nombre;
		}


		public function getSexoId(){
				return $this->sexo_id;
		}

		public function getEstadoCivilId(){
				return $this->estado_civil_id;
		}

		public function getEstadoCivilNombre(){
				return $this->estado_civil_nombre;
		}


		public function getTelCelular(){
				return $this->tel_celular;
		}

		public function getTelFijo(){
				return $this->tel_fijo;
		}

		public function getTelContacto(){
				return $this->tel_contacto;
		}

		public function getNivelEducacionalId(){
				return $this->nivel_educacional_id;
		}

		public function getNivelEducacionalNombre(){
				return $this->nivel_educacional_nombre;
		}


		public function getProfesion(){
				return $this->profesion;
		}

		public function getNombreContacto(){
				return $this->nombre_contacto;
		}

		public function getAfpId(){
				return $this->afp_id;
		}

		public function getAfpNombre(){
				return $this->afp_nombre;
		}


		public function getSaludId(){
				return $this->salud_id;
		}

		public function getSaludNombre(){
				return $this->salud_nombre;
		}


		public function getSaludUf(){
				return $this->salud_uf;
		}

		public function getDireccion(){
				return $this->direccion;
		}

		public function getCiudad(){
				return $this->ciudad;
		}
		public function getTallaPantalon(){
				return $this->talla_pantalon;
		}

		public function getTallaCalzado(){
				return	$this->talla_calzado;
		}

		public function getTallaCamisa(){
				return $this->talla_camisa;
		}

		public function getBancoId(){
				return	$this->banco_id;
		}

		public function getBancoNombre(){
				return	$this->banco_nombre;
		}

		public function getTipoCuentaId(){
				return $this->tipo_cuenta_id;
		}

		public function getTipoCuentaNombre(){
				return $this->tipo_cuenta_nombre;
		}

		public function getCuentaBancaria(){
				return $this->cuenta_bancaria;
		}

		public function getPonderacion(){
				return $this->ponderacion;
		}


		public function getEstadoDescripcion(){
				return $this->estado_descripcion;
		}

		public function getPostulacion( $id_postulacion ){
				
				$array_filtrado = array_filter( $this->postulaciones, function( $postulacion, $k ) use ( $id_postulacion ){
						
						if( $id_postulacion == $postulacion->getId() )
								return true;

						return false;
				}, ARRAY_FILTER_USE_BOTH);

				//no deberia existir mas de una postulacion
				//por tipo

				return array_pop( $array_filtrado );

		}

		
/*
		private function getRefPostulacion(){
				
				$array_filtrado = array_filter( $this->postulaciones, function( &$postulacion, $k ) use ( $id_postulacion ){
						
						if( $id_postulacion == $postulacion->getId() )
								return true;

						return false;
				}, ARRAY_FILTER_USE_BOTH);

				return empty( $array_filtrado) ? array_key_first( $array_filtrado ) : false ;
			 
		}
*/
		/************    SETTERS  *******************************/

		public function setId( $id ){
				$this->id = $id;
		}

		public function setPostulacionId( $id ){
				$this->postulacion_id = $id;
		}

		public function setRut( $rut ) {
				$this->rut = $rut;
		}

		public function setDV( $dv ){
				$this->dv = $dv;
		}

		public function setNombre( $nombre ){
				$this->nombre = $nombre;
		}

		public function setApaterno( $apellido ){
				$this->apaterno = $apellido;
		}

		public function setAmaterno( $apellido ){
				$this->amaterno = $apellido;
		}

		public function setEmail( $email ){
				$this->email = $email;
		}
		
		
		public function setFechaNacimiento( $fecha ){
				$this->fecha_nacimiento = $fecha;
		}

		public function setNacionalidadId( $nacionalidad_id ){
				$this->nacionalidad_id = $nacionalidad_id;
		}

		public function setSexoId( $sexo_id ){
				$this->sexo_id = $sexo_id ;
		}

		public function setEstadoCivilId( $estado_civil_id){
				$this->estado_civil_id = $estado_civil_id;
		}

		public function setTelCelular( $tel ){
				$this->tel_celular = $tel;
		}

		public function setTelFijo( $tel ){
				$this->tel_fijo = $tel;
		}

		public function setTelContacto( $tel ){
				$this->tel_contacto = $tel;
		}

		public function setNivelEducacionalId( $nivel_educ_id ){
				$this->nivel_educacional_id = $nivel_educ_id;
		}

		public function setProfesion( $profesion ){
				$this->profesion = $profesion;
		}

		public function setNombreContacto( $nombre ){
				$this->nombre_contacto = $nombre;
		}

		public function setAfpId( $afp_id){
				$this->afp_id = $afp_id;
		}

		public function setSaludId( $salud_id ){
				$this->salud_id = $salud_id;
		}

		public function setSaludUf( $uf ){
				$this->salud_uf = $uf;
		}
		
		public function setDireccion( $direccion ){
				$this->direccion = $direccion;
		}

		public function setCiudad( $ciudad ){
				$this->ciudad = $ciudad;
		}

		public function setTallaPantalon( $talla ){
				$this->talla_pantalon = $talla;
		}

		public function setTallaCalzado( $talla ){
				$this->talla_calzado = $talla;
		}

		public function setTallaCamisa( $talla ){
				$this->talla_camisa = $talla;
		}

		public function setBancoId( $banco_id ){
				$this->banco_id = $banco_id;
		}

		public function setTipoCuentaId( $tipo_cuenta_id ){
				$this->tipo_cuenta_id = $tipo_cuenta_id;
		}

		public function setCuentaBancaria( $cuenta ){
				$this->cuenta_bancaria = $cuenta;
		}

		public function setPonderacion( $ponderacion ){
				$this->ponderacion = $ponderacion;
		}

		public function setEstado(){
		////funcion para cambiar el estado de un postulante
			//no se si ejecutar el cambio directo en la BD.
		}
		
		public function addDocumento( $documento){
				
				if( $documento instanceof Rec_Documento ){
						
						if( empty( $this->documentos ) )
								$this->documentos = array();

						array_push( $this->documentos , $documento);
						return true;

				}
				else{
						return false;
				}
		}

		public function guardarDocumentos(){

				
				$this->datos->begin();

				foreach( (array)$this->documentos as $doc){

						$this->datos->registrarDocumentoPostulante($doc->getId(), $this->id, $observacion = null );
				
				}

				
				$this->datos->commit();

		}

		public function addPostulacion( $postulacion ){
				
				if( $postulacion instanceof Rec_Postulacion ){
						
						if( empty( $this->postulaciones ) )
								$this->postulaciones = array();

						array_push( $this->postulaciones , $postulacion );

						return true;

				}
				else{
						return false;
				}
		}

	public function registrarEtapaPostulacion( $rocpeta_id, $procp_id, $puntaje, $descripcion, $fecha_hora , $direccion, $link, $observacion , $aprobado ){
		return $this->datos->registrarEtapaPostulacion( $rocpeta_id, $procp_id, $puntaje, $descripcion, $fecha_hora, $direccion, $link , $observacion, $aprobado , null);
	}


		public function getListaDocumentosPostulacion( $perfil_id ){//rocp_id
		
			if( !empty( $this->getId() ) ){
				
					$lista = $this->datos->getDocumentosPerfilOferta($perfil_id,  $this->getId() );

					//foreach( (array)$lista as $t){
						return array_map( function($d){		
								
								return array(
										'id'=> $d['ID'],
										'descripcion' => $d['DESCRIPCION'],
										'tipo_id'=> $d['TIPO_ID'],
										'validado' => $d['PDO_VALIDADO'] 
								);
						}, (array) $lista);
						}
		}


		public function actualizarDocumento( $pRDOCOFE_ID/*, $pPOST_ID */ ){

			return $this->datos->actualizarReclutamientoOfertaDocumentoEstado( $pRDOCOFE_ID, $this->getId() , $pVALIDADO = null );

	}
	
	public function getListaCorreoPostulacionDocumentos(){

			$lista = $this->datos->getListaCorreoProceso('POSTULANTE_ACTUALIZA_DOCUMENTOS');

			return array_map(
					function($c){

							return array(
									'correo' => $c['CORREO'],
									'tipo' => $c['TIPO']
							);
					
					}, $lista
			);
	
	}
	
	public function getListaCorreosInteresadosPostulacion($rocp_id){

			$lista = $this->datos->getPostulacionCorreosInteresados($rocp_id);

			if(empty($lista))
			
					return array();

			$lista = $lista[0];

			$correos = array();

			if( !empty( $lista['CORREO_EVALUADOR'] ) )
					array_push($correos, array( 
							'correo' =>$lista['CORREO_EVALUADOR'],
							'tipo' => ''
					));

			if( !empty( $lista['CORREO_SOLICITANTE'] ) )
					array_push($correos, array( 
							'correo' =>$lista['CORREO_SOLICITANTE'],
							'tipo' => ''
					));
	
			if( !empty( $lista['CORREO_RRHH'] ) )
					array_push($correos, array( 
							'correo' =>$lista['CORREO_RRHH'],
							'tipo' => ''
					));

			return $correos;

	}
	/*
	public function getCantidadDocumentosInvalidos( $rocp_id ){
			
			$r = $this->datos->getCantidadDocumentosInvalidos($rocp_id, $this->getId() );

			return $r[0]['CANTIDAD'];
	}
*/

	
}
?>
