<?php
/**
 * 
 */
require_once 'ObservacionSeguimiento.php';

class Seguimiento
{
	private $id;
	private $cerrado;
	private $observaciones;
	
	private $datos;
	
	function __construct($id = null)
	{

		$this->datos = new Datos();

		if(!empty($id) && is_numeric($id)){
			$this->id = $id;
			$this->setDetalle();
		}

		this->observaciones = array();

	}
	public function setObservaciones()
	{
		//$this->datos->getSeguimientoDetalle( $this->id );
	}

	public function registrarObservacion($tipo, $fecha, $descripcion, $fecha_inicial = null, $fecha_final = null )
	{
		/*$obs = new ObservacionSeguimiento();
		$obs->setDatos($tipo, $fecha, $descripcion, $fecha_inicial, $fecha_final );*/

	}

	public function getId()	{ return $this->id; }
	public function getEstado()	{ return $this->cerrado; }

	public function getDetalle()	{ return $this->observaciones; }


}

?>