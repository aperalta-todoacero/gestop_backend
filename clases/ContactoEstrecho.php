<?php
/**
 * 
 */
class ContactoEstrecho
{
	private $id;
	private $rut;
	private $nombre;
	private $fecha;

	function __construct( $id = null )
	{
		$this->id = $id;
		$this->fecha = date('Y-m-d');
	}

	public function setId($value='')
	{
		$this->id = $value;
	}
	public function setRut($value='')
	{
		$this->rut = $value;
	}
	public function setNombre($value='')
	{
		$this->nombre = $value;
	}
	public function setFecha($value='')
	{
		$this->fecha = $value;
	}

	public function getId(){ return $this->id; }
	public function getRut(){ return $this->rut; }
	public function getNombre(){ return $this->nombre; }
	public function getFecha(){ return $this->fecha; }
}
?>