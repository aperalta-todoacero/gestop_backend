<?php
/**
 * 
 */
class ObservacionSeguimiento
{
	private $id;
	private $tipo;
	private $fecha;
	private $descripcion;
	private $fecha_inicial;
	private $fecha_final;

	function __construct($id = null)
	{
		if(!empty($id) && is_numeric( $id ) ){
			$this->id = $id;
		}
	}
	public function setDatos($tipo, $fecha, $descripcion, $fecha_inicial = null , $fecha_final = null )
	{
		$this->tipo = $tipo;
		$this->fecha = $fecha;
		$this->descripcion = $descripcion;
		$this->fecha_inicial = $fecha_inicial;
		$this->fecha_final = $fecha_final;
	}
	
}
?>