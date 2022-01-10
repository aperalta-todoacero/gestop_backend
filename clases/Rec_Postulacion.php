<?php

require_once dirname(__DIR__).'/datos/Datos.php';

require_once 'Rec_Documento.php';

class Rec_Postulacion
{
		private $id;
		private $fecha_postulacion;
		private $etapa_actual;
		private $perfil_evaluado;
		private $documentos_requeridos;

		public function __construct(){
		
		}

		/********* GETTERS ******************/

		public function getId(){
				return $this->id;
		}

		public function getFechaPostulacion(){
				return $this->fecha_postulacion;
		}

		public function getEtapaActual(){
				return $this->etapa_actual;
		}

		public function getDocumento( $id_documento ){

				$array_filtrado = array_filter( $this->documentos_requeridos, function( $documento ,$k) use ( $id_documento ){
						return $id_documento == $documento->getId();
				},ARRAY_FILTER_USE_BOTH);

				return array_pop( $array_filtrado );

		}
		/********** SETTERS ****************/

		public function setId( $id ){
				$this->id = $id;
		}

		public function setFechaPostulacion( $fecha ){
				$this->fecha_postulacion = $fecha;
		}

		public function setEtapaActual( $etapa ){
				$this->etapa_actual = $etapa;
		}

		public function cargarDocumentosRequeridos(){
				//cargar documentos requeridos para la postulacion
		}

		public function addDocumento( $documento ){
				
				if( $documento instanceof Rec_Documento ){
						
						if( empty( $this->documentos_requeridos ) )
								$this->documentos_requeridos = array();

						array_push( $this->documentos_requeridos , $documento );

						return true;
				}
				else{
						return false;
				}
		}
}
?>
