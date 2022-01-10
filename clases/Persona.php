<?php
require_once dirname(__DIR__).'/datos/Datos.php';

class Persona
{
  protected $nombre;
  protected $rut;
	protected $dv;
  protected $id;
	protected $telefono;
	protected $comuna;

  function __construct($rut='', $nombre='')
  {
    $this->datos = new Datos();
    $this->rut=$rut;
    $this->nombre=$nombre;
	}

	public static function getInstancia( $id )
	{
			$instancia = new Self('','');
			$instancia->setId( $id );
			return $instancia;
	}

	public function getId() 
	{
			return $this->id;
	}

	public function getRut()
	{
			return $this->rut;
	}

	public function getDV(){

			return $this->dv;

	}

	public function getNombre()
	{
			return $this->nombre;
	}

	public function getTelefono(){

			return $this->telefono;
	}

	public function getComuna(){

			return $this->comuna;

	}

	public function setTelefono( $telefono ){

			$this->telefono = $telefono;
	}

	public function setComuna( $comuna ){

			$this->comuna = $comuna;

	}

	public function setId( $id ) 
	{
			$this->id = $id ;
	}

	public function setRut( $rut ) 
	{
			$this->rut = $rut;
	}

	public function setDV( $dv ){

			$this->dv = $dv;
	}

  public function cargar()
  {
    // code...
  }
	public function getPerfilDisc()
	{
			$r = $this->datos->getPerfilEncuestaDisc( $this->id );

			if( is_array($r) && count($r) && isset($r[0]) )
			{
					return array(
							'patron' => $r[0]['DPATRON_PATRON'],
							'emocion' => $r[0]['DPATRON_EMOCION'],
							'meta' => $r[0]['DPATRON_META'],
							'juzga' => $r[0]['DPATRON_JUZGA'],
							'influye' => $r[0]['DPATRON_INFLUYE'],
							'su_valor' => $r[0]['DPATRON_SU_VALOR'],
							'abusa' => $r[0]['DPATRON_ABUSA'],
							'bajo_presion' => $r[0]['DPATRON_BAJO_PRESION'],
							'teme' => $r[0]['DPATRON_TEME'],
							'seria_eficaz' => $r[0]['DPATRON_SERIA_EFICAZ'],
							'observacion1' => $r[0]['DPATRON_OBSERVACION1'],
							'observacion2' => $r[0]['DPATRON_OBSERVACION2'],
							'observacion3' => $r[0]['DPATRON_OBSERVACION3'],
					);
			}else{
					return false;
			}
	}
}

 ?>
