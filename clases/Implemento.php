<?php 

class Implemento{

		private $id;
		private $descripcion;
		private $tipo_id;
		private $tipo_descripcion;
		private $icono;

		public function __construct(){
		
		}

		public function getId(){ return $this->id;}
		public function getDescripcion(){ return $this->descripcion;}
		public function getTipoId(){ return $this->tipo_id; }
		public function getTipoDescripcion(){ return $this->tipo_descripcion;}
		public function getIcono(){ return $this->icono; }

		public function setId( $id ){ 
				$this->id = $id;
		}

		public function setDescripcion( $descripcion){ 
				$this->descripcion = $descripcion;
		}

		public function setTipoId( $tipo_id ){ 
				$this->tipo_id = $tipo_id; 
		}

		public function setTipoDescripcion( $descripcion ){ 
				$this->tipo_descripcion = $descripcion;
		}

		public function setIcono( $icono ){
				$this->icono = $icono;
		}

}
?>
