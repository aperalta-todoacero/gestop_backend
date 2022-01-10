<?php

class Pasaporte
{
  private $id;
  private $estado;
  private $estado_descripcion;

  function __construct($id='', $estado='', $estado_descripcion='')
  {
    $this->id = $id;
    $this->estado = $estado;
    $this->estado_descripcion = $estado_descripcion;
  }
  public function set($id, $estado='', $estado_descripcion='')
  {
    $this->id = $id;
    $this->estado = $estado;
    $this->estado_descripcion = $estado_descripcion;
  }
  public function getId()  { return $this->id; }
  public function getEstado()  { return $this->estado; }
  public function getEstadoDescripcion()  { return $this->estado_descripcion; }
}
 ?>
