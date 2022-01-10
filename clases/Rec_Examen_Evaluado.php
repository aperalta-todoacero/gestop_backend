<?php

require_once 'Rec_Examen.php';

class Rec_Examen_Evaluado extends Rec_Examen{

		private $puntaje;

		private $puntaje_descripcion;

		public function __construct(){
		}

		public function gePuntaje() { return $this->puntaje; }

		public function getPuntajeDescripcion(){ return $this->puntaje_descripcion; }
		
		public function setPuntaje( $pts ){
				$this->puntaje=$pts;
		}

		public function setPuntajeDescripcion( $desc ){				$this-
				$this->puntaje_descripcion = $desc;
		}

}
?>
