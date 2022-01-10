<?php

require_once 'Documento.php';

class Rec_Documento extends Documento
{
		private $tipo_id;

		private $id_doc_postulacion;

		private $validado;

		public function __construct() {

				$this->validado = false;
		
		}

		public function setTipoId( $tipo_id ){
				$this->tipo_id = $tipo_id;
		}

		public function getTipoId(){
				return $this->tipo_id;
		}

		public function getIdDocPostulacion(){
				return $this->id_doc_postulacion;
		}

		public function getValidado(){

				return $this->validado;
		}

		public function setIdDocPostulacion( $id ){
				$this->id_doc_postulacion = $id;
		}

		public function setValidado( $validado = false){

				$this->validado = !$validado? false : true;

		}

}
?>
