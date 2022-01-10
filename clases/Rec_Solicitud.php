<?php

require_once dirname(__DIR__).'/datos/Datos.php';

require_once 'Rec_Perfil.php';
require_once 'Rec_Perfil_Solicitud.php';

class Rec_Solicitud
{
		private $id;
		private $titulo;
		private $descripcion;

		private $faena;

		private $area;

//		private $turno_id;

		private $fecha;

		private $fecha_string;

		private $estado_id;

		private $estado_descripcion;

		private $perfiles;

		private $usuario_nombre;

		private $usuario_id;

		private $urgente;

		public function __construct( $sol_id = null){

				$this->id = $sol_id;

				$this->urgente = false;

				$this->datos = new Datos();
		}

		/********* getters ***********************/

		public function getId() {
				return $this->id;
		}

		public function getTitulo(){
				return $this->titulo;
		}

		public function getDescripcion(){
				return $this->descripcion;
		}

		public function getFaena(){
				return $this->faena;
		}

		public function getArea(){
				return $this->area;
		}

		public function getPerfiles(){
				return $this->perfiles;
		}

		public function getFechaString(){
				return $this->fecha_string;
		}

		public function getEstadoId(){
				return $this->estado_id;
		}

		public function getEstadoDescripcion(){
				return $this->estado_descripcion;
		}

		public function getUsuarioNombre(){
				return $this->usuario_nombre;
		}

		public function getUrgente(){
				return $this->urgente;
		}

		public function getCantidadPersonasSolicitadas(){
				$cantidad = 0;

				foreach((array)$this->perfiles as $p){

						$c = $p->getCantidad();
						$cantidad+= is_numeric($c) ? $c : 0;
				}

				return $cantidad;
		}

				/********** setters ********************/

		public function setId( $id ){
				$this->id = $id;
		}

		public function setTitulo( $titulo ){
				$this->titulo = $titulo;
		}

		public function setFechaString( $fecha ){
				$this->fecha_string = $fecha;
		}

		public function setDescripcion( $descripcion ){
				$this->descripcion = $descripcion;
		}

		public function setEstadoId( $estado ){
				$this->estado_id = $estado;
		}

		public function setEstadoDescripcion( $estado ){
				$this->estado_descripcion = $estado;
		}

		public function setFaena( $faena_id ){
				$this->faena = $faena_id;
		}

		public function setArea( $area_id ){
				$this->area = $area_id;
		}

		public function setUsuarioNombre( $nombre ){
				$this->usuario_nombre = $nombre;
		}

		public function setUrgente( $urgente ){
				$this->urgente = $urgente;
		}

		
		public function addPerfil( $perfil ){
				
				if(!is_array( $this->perfiles ) || empty( $this->perfiles ) )
						$this->perfiles = array();

				if( $perfil instanceof Rec_Perfil_Solicitud )
						array_push( $this->perfiles, $perfil );
				else
						return false;
		}

		public function setPerfiles( $con_oferta = true ){

				$this->perfiles = array();

				$perfiles = $this->datos->getPerfilesSolReclutamiento( $this->id , null );
				
				foreach( $perfiles as $p ){

						if( $con_oferta === true || ( !$con_oferta && empty( $p['OFERTA_ID'] ) ) ){

							$perfil = new Rec_Perfil_Solicitud();
							$perfil->setId( $p['ID'] );// id_solicitud_perfil
							$perfil->setTipoId( $p['PERFIL_ID'] );// id_solicitud_perfil
							$perfil->setDescripcion( $p['PERFIL_DESC'] );//nombre del perfil
							$perfil->setCantidad( $p['CANTIDAD'] );
							$perfil->setObservacion( $p['OBSERVACION'] );
							$perfil->setFaena( $p['FA_ID'] );
							$perfil->setArea( $p['AREA_ID'] );
							$perfil->setTurnoId( $p['TURNO_ID'] );
							$perfil->setFechaReq( $p['FECHA'] );
							$perfil->setSueldo( $p['SUELDO'] );
							$perfil->setEvaluadorUsrId( $p['EVALUADOR_USR_ID'] );
							$perfil->setEvaluadorNombre( $p['EVALUADOR_NOMBRE'] );
							
							$perfil->setDocumentos();
							$perfil->setCompetencias();
							$perfil->setImplementos();

							$this->addPerfil( $perfil );
						}

					}

		}
}
?>
