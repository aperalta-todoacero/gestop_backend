<?php

class Rec_Examen{

		private $id;
		private $titulo;
		private $descripcion;

		public function __construct(){
		
		}
		
		public function getId(){ return $this->id;}
		
		public function getTitulo(){ return $this->titulo;}
		
		public function getDescripcion(){ return $this->descripcion;}
		
		

		public function setId( $id ){ 
				$this->id = $id;
		}

		public function setDescripcion( $descripcion){ 
				$this->descripcion = $descripcion;
		}

		public function setTitulo( $titulo ){ 
				$this->titulo = $titulo; 
		}

}
?>
