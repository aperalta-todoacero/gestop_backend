<?php

class Pregunta
{

    private  $tipo;
    private  $descripcion;
    private  $id;
		private  $grupo;
    private  $respuestaCerrada=null;
    private  $respuestaAbierta=null;
    private $alternativas;//puede ser en otra clase
    private $subRespuestaCerrada;//usado actualmente para array de contacto estrecho
    private $respuestaLista;//usado actualmente para array de contacto estrecho

    public function __construct($id, $descripcion='', $tipo='', $grupo = null )
    {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->descripcion  = $descripcion;
        $this->alternativas = array();
        $this->subRespuestaCerrada = null;
				$this->respuestaLista = array();
				$this->grupo = $grupo;
    }

    /**
     *
     */
    public function set()
    {
        // TODO implement here
    }
    public function addAlternativa($id, $texto){
      array_push( $this->alternativas, array('id' => $id, 'texto' => $texto) );
    }
    public function setRespuesta($respuestaCerrada=null, $respuestaAbierta=null, $subRespuestaCerrada=null, $respuestaLista= array() )
    {
      $this->respuestaCerrada = $respuestaCerrada;
      $this->respuestaAbierta = $respuestaAbierta;
      $this->subRespuestaCerrada = $subRespuestaCerrada;
      $this->respuestaLista = $respuestaLista;
    }
    /*public function setListaRespuesta( $lista=array() )
    {
      $this->respuestaLista = $lista;
    }*/
    public function getId(){ return $this->id; }
    public function getTipo(){ return $this->tipo; }
    public function getDescripcion(){ return $this->descripcion; }
    public function getAlternativas(){ return $this->alternativas; }
    public function getRespuestaCerrada(){ return $this->respuestaCerrada; }
    public function getRespuestaAbierta(){ return $this->respuestaAbierta; }
    public function getSubRespuestaCerrada(){ return $this->subRespuestaCerrada; }
		public function getRespuestaLista(){ return $this->respuestaLista; }
		public function getGrupo(){ return $this->grupo;}


}
