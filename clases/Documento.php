<?php

class Documento
{
		protected $id;
		protected $tipo;
		protected $nombre_archivo;
		protected $extencion_archivo;
		protected $descripcion;
		protected $ruta_archivo;

		public function __construct() 
		{

		}

		/****** getters *************/

		public function getId(){
				return $this->id;
		}

		public function getTipo(){
				return $this->tipo;
		}

		public function getNombreArchivo(){
				return $this->nombre_archivo;
		}

		public function getDescripcion(){
				return $this->descripcion;
		}

		public function getRutaArchivo(){
				return $this->ruta_archivo;
		}

		/***********  setters  **************/

		public function setId( $id ){
				$this->id = $id;
		}

		public function setTipo( $tipo ){
				$this->tipo = $tipo;
		}

		public function setNombreArchivo( $nombre){
				$this->nombre_archivo = $nombre;
		}

		public function setDescripcion( $descripcion ){
				$this->descripcion = $descripcion;
		}

		public function setRutaArchivo( $ruta ){
				$this->ruta_archivo = $ruta;
		}

}
?>
