<?php
require_once dirname(__DIR__).'/datos/Datos.php';
require_once 'Faena.php';

class Empresa
{
  private $faenas;
  private $datos;

  function __construct()
  {
    $this->datos= new Datos();
    $this->faenas = array();
    $this->set();
  }

  public function set()
  {
    $faenas = $this->datos->getFaenas();

    $this->faenas = array_map(
        function($f){
          return new Faena($f['FA_ID'], $f['FA_NOMBRE']);
        },
        $faenas
      );

	}

  public function getFaenas()
  {
    return $this->faenas;
  }

  public function getTiposExamen(){
    return array_map(
      function ($t)
      {
        return array(
          'id'=> $t['TIPEXAM_ID'],
          'nombre'=> $t['TIPEXAM_TITULO'],
        );
      },
      $this->datos->getTiposExamen()
    );
	}

	public function getAreas(){
		
			$areas = $this->datos->getAreas();
		
			return array_map(function( $a ){
					return array(
							'id'=> $a['ID'],
							'nombre' => $a['NOMBRE']
					);
		}, $areas);

	}

}
 ?>
