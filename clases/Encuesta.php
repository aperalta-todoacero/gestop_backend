<?php
require_once dirname(__DIR__).'/datos/Datos.php';
require_once 'Pregunta.php';

class Encuesta
{
  protected $id;
  protected $preguntas;

  function __construct($id)
  {
    $this->id = $id;
    $this->datos = new Datos();
    $this->preguntas = array();
    $this->set();
  }
  public function test($value='')
  {
    return "hola, llegue";
  }

  private function set()
  {
    $pregunta = $this->datos->getPreguntasEncuesta($this->id);

    foreach ((array)$pregunta as $p) {
      $obj = new Pregunta( $p['PREG_ID'],$p['PREG_TEXTO'],$p['PREG_TIPO'] , $p['PREG_GRUPO']);
      $alternativas = $this->datos->getAlternativas($this->id, $p['PREG_ID']);

      foreach ($alternativas as $alt) {
        $obj->addAlternativa($alt['ALT_ID'], $alt['ALT_TEXTO']);
      }
      array_push($this->preguntas,  $obj);
    }
    unset($p);
  }
  public function responderPregunta($preg_id='', $valorCerrado=null, $valorAbierto=null, $subValorCerrado=null, $valorLista= array())
  {
    foreach ($this->preguntas as &$preg) {
      if($preg_id===$preg->getId()){
        $preg->setRespuesta($valorCerrado, $valorAbierto, $subValorCerrado, $valorLista);
        break;
      }
    }
  }

  public function getPreguntas()
  {
    return $this->preguntas;
  }
  public function getEstadosContagio()
  {
    return $this->datos->getEstadosContagio();
  }

  public function getId()  {  return $this->id;  }

}

 ?>
