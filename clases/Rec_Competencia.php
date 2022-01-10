<?php
//require_once dirname(__DIR__).'/datos/Datos.php';

class Rec_Competencia
{
		protected $id;
		protected $titulo;
		protected $descripcion;
		protected $estado;
		protected $tipo_id;
		protected $tipo_descripcion;

		public function __construct()
		{

		}

		/****************  GETTERS ******************/

		public function getId(){
				return $this->id;
		}

		public function getTitulo(){
				return $this->titulo;
		}

		public function getDescripcion(){
				return $this->descripcion;
		}

		public function getTipoId(){
				return $this->tipo_id;
		}

		public function getTipoDescripcion(){
				return $this->tipo_descripcion;
		}

	
		/**************** SETTERS  ******************/

		public function setId( $id ){
				$this->id = $id;
		}

		public function setTitulo( $titulo ){
				$this->titulo = $titulo;
		}

		public function setDescripcion( $descripcion ){
				$this->descripcion = $descripcion;
		}

		public function setTipoId( $tipo_id ){
				$this->tipo_id = $tipo_id;
		}

		public function setTipoDescripcion( $descripcion ){
				$this->tipo_descripcion = $descripcion;
		}


}
?>
