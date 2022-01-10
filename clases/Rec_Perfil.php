<?php

require_once dirname(__DIR__).'/datos/Datos.php';

require_once 'Rec_Competencia.php';
require_once 'Rec_Documento.php';
require_once 'Rec_Examen.php';
require_once 'Implemento.php';

class Rec_Perfil
{
		private $id;
		//private $cargo_perfil_id
		private $competencias;
		private $documentos;
		private $implementos;
		private $examenes;
		private $titulo;
		private $descripcion;
		private $observacion;
		private $sueldo;

		private $cantidad; //en caso de ser solicitado

		public function __construct(){

				$this->datos = new Datos();

		}

		/************* GETTERS ********************/

		public function getId(){
				return $this->id;
		}

		public function getTitulo(){
				return $this->titulo;
		}
		public function getSueldo(){
				return $this->sueldo;
		}

		public function getCantidad(){
				return $this->cantidad;
		}

		public function getDescripcion(){
				return $this->descripcion;
		}
		public function getObservacion(){
				return $this->observacion;
		}

		public function getCompetencias(){
				return $this->competencias;
		}

		public function getDocumentos(){
				return $this->documentos;
		}

		public function getImplementos(){
				return $this->implementos;
		}
		
		public function getExamenes(){
				return $this->examenes;
		}
		/*********** SETTERS ************/

		public function setId( $id ){
				$this->id = $id;
		}

		public function setTitulo( $titulo ){
				$this->titulo = $titulo;
		}

		public function setSueldo( $valor ){
				$this->sueldo = $valor;
		}

		public function setCantidad( $cantidad ){
				$this->cantidad = $cantidad;
		}

		public function setDescripcion( $descripcion ){
				$this->descripcion = $descripcion;
		}

		public function setObservacion( $observacion ){
				$this->observacion = $observacion;
		}

		public function addCompetencia( $competencia ){
		
				if( $competencia instanceof Rec_Competencia ){

						if( empty( $this->competencias ) )
								$this->competencias = array();

						array_push( $this->competencias, $competencia );
						
						return true;

				}
				else{
						return false;
				}

		}

		public function addDocumento( $documento ){

				if( $documento instanceof Rec_Documento ){

						if( empty( $this->documentos ) )
								$this->documentos = array();

						array_push( $this->documentos, $documento );

						return true;
				}
				else{
						return false;
				}
		}

		public function addImplemento( $implemento ){
				
				if( $implemento instanceof Implemento ){

						if( empty( $this->implementos ) )
								$this->implementos = array();

						array_push( $this->implementos, $implemento );

						return true;
				}
				else{
						return false;
				}

		}
		
		public function addExamen( $examen ){
				
				if( $examen instanceof Rec_Examen ){

						if( empty( $this->examenes ) )
								$this->examenes = array();

						array_push( $this->examenes, $examen );

						return true;
				}
				else{
						return false;
				}

		}

	public function setCompetencias( ){

			if( !empty( $this->id ) ){
				
					$lista = $this->datos->getCompetencias( $this->id );

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

		public function setImplementos(){

				if( !empty( $this->id ) ){

						$lista = $this->datos->getImplementos( $this->id );

						foreach((array)$lista as $t){

								$implemento = new Implemento();
								$implemento->setId( $t['ID']);
								$implemento->setDescripcion($t['DESCRIPCION']);
								$implemento->setTipoId( $t['TIPO_ID']);
								$implemento->setTipoDescripcion( $t['TIPO_DESCRIPCION']);

								$this->addImplemento( $implemento );
						}
				}
		}
		
		public function setExamenes(){

				if( !empty( $this->id ) ){

						$lista = $this->datos->getRecExamenes( $this->id );

						foreach((array)$lista as $t){

								$examen = new Rec_Examen();
								$examen->setId( $t['REX_ID']);
								$examen->setDescripcion($t['REX_DESCRIPCION']);
								$examen->setTitulo( $t['REX_TITULO']);

								$this->addExamen( $examen);
						}
				}
		}

	/*	
		public function setDocumentos(){

			if( !empty( $this->id ) ){
				
					$lista = $this->datos->getDocumentos( $this->id );

					foreach( $lista as $t){
								
							$documento= new Rec_Documento();
							$documento->setId($t['ID']);
							$documento->setDescripcion($t['DESCRIPCION']);
							
							$this->addDocumento( $documento );

					}

			}
	
		}
	 */
}
?>
