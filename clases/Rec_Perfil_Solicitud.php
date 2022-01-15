<?php

require_once dirname(__DIR__).'/datos/Datos.php';

require_once 'Rec_Competencia.php';
require_once 'Rec_Documento.php';
require_once 'Rec_Perfil.php';
require_once 'Implemento.php';

class Rec_Perfil_Solicitud extends Rec_Perfil
{
		/*
		private $id;
		private $competencias;
		private $documentos;
		private $titulo;
		private $descripcion;
		private $observacion;

		private $cantidad; //en caso de ser solicitado
		 */
		private $tipo_id;

		private $turno_id;

		private $turno_descripcion;

		private $fecha;

		private $faena_id;
		
		private $area_id;
		
		private $faena_nombre;

		private $area_nombre;

		private $fecha_requerida;

		private $evaluador_id;

		private $evaluador_nombre;

		private $nro_contrato;


		public function __construct(){

				$this->datos = new Datos();

		}

		/************* GETTERS ********************/


		public function getTipoId(){
				return $this->tipo_id;
		}

		public function getTurnoId(){

				return $this->turno_id;
		}

		public function getTurnoDescripcion(){
				return $this->turno_descripcion;
		}

		public function getFaena(){

				return $this->faena_id;
		}

		public function getArea(){
				return $this->area_id;
		}
		
		public function getFaenaNombre(){

				return $this->faena_nombre;
		}

		public function getAreaNombre(){
				return $this->area_nombre;
		}

		public function getFechaReq(){
				return $this->fecha_requerida;
		}
		
		public function getEvaluadorUsrId(){
				return $this->evaluador_id;
		}

		
		public function getEvaluadorNombre(){
				return $this->evaluador_nombre;
		}

		public function getNroContrato(){
				return $this->nro_contrato;
		}

		/*************** setters **********/

		public function setTipoId( $tipo ){
				$this->tipo_id = $tipo;
		}

		public function setTurnoId($turno_id){
				$this->turno_id = $turno_id;
		}

		public function setTurnoDescripcion( $descripcion ){
				$this->turno_descripcion = $descripcion;
		}

		public function setFaena( $id ){
				$this->faena_id = $id;
		}

		public function setArea( $id ){
				$this->area_id = $id;
		}
		
		public function setFaenaNombre( $nombre ){
				$this->faena_nombre = $nombre;
		}

		public function setAreaNombre( $nombre ){
				$this->area_nombre = $nombre;
		}

		public function setFechaReq( $fecha ){
				$this->fecha_requerida = $fecha;
		}
		public function setEvaluadorUsrId( $usr_id ){
				$this->evaluador_id = $usr_id;
		}
		
		public function setEvaluadorNombre( $nombre ){
				$this->evaluador_nombre = $nombre;
		}

		public function setNroContrato( $nro_contrato ){
				$this->nro_contrato = $nro_contrato;
		}
		
		public function setCompetencias( ){

			if( !empty( $this->getId() ) ){
				
					$lista = $this->datos->getCompetenciasPerfilSolReclutamiento( $this->getId() );

					foreach( $lista as $t){
								
							$competencia = new Rec_Competencia();
							$competencia->setId($t['ID']);
							$competencia->setTitulo($t['TITULO']);
							$competencia->setDescripcion( $t['DESCRIPCION'] );
							$competencia->setTipoId($t['TIPO_ID']);
							$competencia->setTipoDescripcion($t['TIPO_DESCRIPCION']);
							
							$this->addCompetencia( $competencia );

					}

			}
	
	}
		
		public function setDocumentos(){

			if( !empty( $this->getId() ) ){
				
					$lista = $this->datos->getDocumentosPerfilSolReclutamiento( $this->getId() );

					foreach( (array)$lista as $t){
								
							$documento= new Rec_Documento();
							$documento->setId($t['ID']);
							$documento->setDescripcion($t['DESCRIPCION']);
							$documento->setTipo($t['TIPO_ID']);
							
							$this->addDocumento( $documento );

					}

			}
	
		}
		
		public function setImplementos(){

				if( !empty( $this->getId() ) ){

						$lista = $this->datos->getImplementosPerfilSolReclutamiento( $this->getId() );

						foreach( (array)$lista as $t){

								$implemento = new Implemento();
								$implemento->setId( $t['ID']);
								$implemento->setDescripcion($t['DESCRIPCION']);
								$implemento->setTipoId( $t['TIPO_ID']);
								$implemento->setTipoDescripcion( $t['TIPO_DESCRIPCION']);

								$this->addImplemento( $implemento );
						}
				}
		}

	 
}
?>
