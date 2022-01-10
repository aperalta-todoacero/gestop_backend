<?php
class Conexion{

  private static $instancia;
  public static $error;
  private function __construct(){
    $servidor = 'www.infomin.cl';
    $nombre_bd= 'infomin_todoacero_desa';
    $usuario  = 'infomin';
    $password = 'RandyMin2012';

    /*$opciones=array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_EMULATE_PREPARES => true,
      PDO::ATTR_PERSISTENT => true
    );*/
     $opciones=array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

    try{
      self::$instancia = new PDO("mysql:host=".$servidor."; dbname=".$nombre_bd, $usuario, $password, $opciones);
      //return $conexion;
    }catch(Exception $e){
      self::$error=$e->getMessage();
      return false;
    }
  }

  public static function getConexion(){

    if (  !self::$instancia )
      new self();
    return self::$instancia;
  }
}
 ?>
