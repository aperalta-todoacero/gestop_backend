<?php
/**
 * 
 */
class CasoParticular
{
	private $id;
	private $rut;
	private $nombre;
	private $faena_id;
	private $estado_contagio;
	private $fecha_sintoma;
	private $fecha_conocimiento;
	private $observacion;

	private $contactos_estrecho;

	function __construct($id= null)
	{
		$this->id = $id ;
		$this->contactos_estrecho= array();
	}

	public function getId()	{ return $this->id; }
	public function getRut(){ return $this->rut ;}
	public function getNombre(){ return	$this->nombre ;}
	public function getFaenaId(){ return $this->faena_id ;}
	public function getEstadoContagio(){ return	$this->estado_contagio ;}
	public function getFechaSintoma(){ return $this->fecha_sintoma ;	}
	public function getFechaConocimiento(){ return $this->fecha_conocimiento ;}
	public function getObservacion(){ return $this->observacion ;}
	public function getContactosEstrechos(){ return $this->contactos_estrecho ;}


	public function setId($valor){
		$this->id = $valor;
	}
	public function setRut($valor){
		$this->rut = $valor;
	}
	public function setNombre($valor){
		$this->nombre = $valor;
	}
	public function setFaenaId($valor){
		$this->faena_id = $valor;
	}
	public function setEstadoContagio($valor){
		$this->estado_contagio = $valor;
	}
	public function setFechaSintoma($valor){
		$this->fecha_sintoma = $valor;
	}
	public function setFechaConocimiento($valor){
		$this->fecha_conocimiento = $valor;
	}
	public function setObservacion($valor){
		$this->observacion = $valor;
	}
	public function agregarContactoestrecho($cce)
	{
		if($cce instanceof ContactoEstrecho)
			array_push( $this->contactos_estrecho, $cce );
	}
}
?>