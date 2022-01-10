<?php
/**
 *
 */
class Examen
{
  private $id;
  private $tipo_id;
  private $tipo_nombre;
  private $fecha;
  private $hora;
  private $resultado;
  //private $cuarentena;
  //private $cuarentena_fecha_ini;
  //private $cuarentena_fecha_fin;
  private $observacion;

  private $rut;
  private $nombre;
  private $email;
  private $celular;
  private $faena;
  private $turno;
  private $laboratorio;
  private $lugar;

  function __construct($rut)
  {
    $this->rut = $rut;
  }
  public function setInfoPersonal($nombre='', $email=null, $celular=null)
  {
    $this->nombre = $nombre;
    $this->email = $email;
    $this->celular= $celular;
  }
  public function setTipo($tipo='', $nombre='')
  {
    $this->tipo_id = $tipo;
    $this->tipo_nombre = $nombre;
  }
  public function setResultado($resultado='')
  {
    $this->resultado = $resultado;
  }
  /*public function setCuarentena($cuarentena = false, $fecha_ini = null, $fecha_fin=null)
  {
    $this->cuarentena = $cuarentena;
    $this->cuarentena_fecha_ini = $fecha_ini;
    $this->cuarentena_fecha_fin = $fecha_fin;
  }*/
  public function setFecha($fecha='')
  {
    $this->fecha = $fecha;
  }
  public function setHora($hora='')
  {
    $this->hora = $hora;
  }
  public function setFaena($faena='')
  {
    $this->faena = $faena;
  }
  public function setTurno($turno='')
  {
    $this->turno = $turno;
  }
  public function setLaboratorio($laboratorio='')
  {
    $this->laboratorio = $laboratorio;
  }
  public function setLugar($lugar='')
  {
    $this->lugar = $lugar;
  }
  public function setObservacion($observacion='')
  {
    $this->observacion = $observacion;
  }
  public function formatFecha($fecha='')
  {
    if( !empty($fecha)){
      return date("Y-m-d", strtotime($fecha));
    }else {
      return $fecha;
    }

  }
  public function getId()  {  return $this->id;  }
  public function getTipo()  {  return $this->tipo_id;  }
  public function getTipoNombre()  {  return $this->tipo_nombre;  }
  public function getResultado()  {  return $this->resultado;  }
  /*public function getCuarentena()  {  return $this->cuarentena;  }
  public function getCuarentenaInicio($bd=false)  {
    return ($bd) ? $this->formatFecha($this->cuarentena_fecha_ini) : $this->cuarentena_fecha_ini;
  }
  public function getCuarentenaFin($bd= false)  {
    return ($bd) ? $this->formatFecha($this->cuarentena_fecha_fin) : $this->cuarentena_fecha_fin;
  }*/

  public function getFecha($bd = false)  {
      return ($bd) ? $this->formatFecha($this->fecha) : $this->fecha;
  }
  public function getObservacion()  {  return $this->observacion;  }
  public function getRut()  {  return $this->rut;  }
  public function getFaena()  {  return $this->faena;  }
  public function getNombre()  {  return $this->nombre;  }
  public function getEmail()  {  return $this->email;  }
  public function getCelular()  {  return $this->celular;  }
  public function getHora()  {  return $this->hora;  }
  public function getLaboratorio()  {  return $this->laboratorio;  }
  public function getLugar()  {  return $this->lugar;  }
  public function getTurno()  {  return $this->turno;  }

}

 ?>
