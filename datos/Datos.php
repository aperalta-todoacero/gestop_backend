<?php
/**
 *
 */

 ini_set('display_errors', 'On');
 error_reporting(E_ALL);

require_once('Conexion.php');

class Datos
{
  private $query;
  private $stmt;
  private $dbh= null;
  private $transacciones=0;
  private $autocommit = true;

  public function __construct()
  {
    $this->dbh =  Conexion::getConexion();
    $this->query="";
    $this->stmt = null;
    $this->autocommit = true;

    if($this->dbh ===false)
      return false;
  }
  public function begin()
  {
    $this->autocommit = false;
    $this->dbh->setAttribute( PDO::ATTR_PERSISTENT , true);
    $this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $this->dbh->beginTransaction();
  }
  public function commit()
  {
    if($this->dbh->inTransaction()){
      $this->autocommit = true;
      $this->dbh->commit();
      $this->dbh->setAttribute( PDO::ATTR_PERSISTENT , false);
      $this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }
  }

  public function rollback()
  {
    if($this->dbh->inTransaction()){
      $this->autocommit = true;
      $this->dbh->rollback();
      $this->dbh->setAttribute( PDO::ATTR_PERSISTENT , false);
      $this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }
  }
/*
  private function setQuery($query='')
  {
    $this->query=$query;
    $this->stmt = $this->dbh->prepare($this->query);
    $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
  }

  private function execQuery( $parametros=array() )
  {
    if( $this->stmt->execute( $parametros ) )
      return $this->stmt->fetchAll();
    else
      return false;
  }
*/
  private function select($query='', $parametros = array())
  {
    $this->query=$query;
    $this->dbh->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND , 'SET NAMES utf8' );
    $this->stmt = $this->dbh->prepare($this->query);
    $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
    if( $this->stmt->execute( $parametros ) ){
      $res= $this->stmt->fetchAll();
      $this->stmt->closeCursor();
      return $res;
    }
    else
      return false;
  }

  private function procedure($query='', $parametros=array(), $fetchAll = true)
  {

    $this->query=$query;
    $this->stmt = $this->dbh->prepare($this->query);
    $this->stmt->setFetchMode(PDO::FETCH_ASSOC);

    $respuestas= array();

    if( $res = $this->stmt->execute( $parametros ) ){

      $i=0;
      
      if($fetchAll){
        array_push($respuestas, $this->stmt->fetchAll() );
        /*do {
            array_push($respuestas, $this->stmt->fetchAll() );
            $i++;
        } while ( $this->stmt->nextRowset() );*/
        while ( $this->stmt->nextRowset() ){
          array_push($respuestas, $this->stmt->fetchAll() );
            $i++;
        }
        //$res = $this->stmt->fetchAll();
        $this->stmt->closeCursor();
        
        return ( $i <= 1 ) ? $respuestas[0] : $respuestas ;

      }else{
        return $res;
      }
      //return ($i <= 1) ? $respuestas[0] : $respuestas ;
    }else{
      $this->rollback();
      return false;
    }
  }

  ////////////////query y sus retornos/////////////////////
  public function validaLogin( $usr, $pass )
  {
    $r=  $this->procedure("CALL USUARIO_VALIDAR_LOGIN(:pUSUARIO, :pPASS )",
      array(
        ':pUSUARIO'=> $usr , 
        ':pPASS'=>$pass
      )
    );
    //retorna rut o false
    return ( is_array($r) && isset($r[0]['AUTORIZADO']) && intval($r[0]['AUTORIZADO']) >0 ) ? $r[0]['AUTORIZADO'] : false ; 
  }

  public function validaToken( $usr, $token )
  {
    $r=  $this->procedure("CALL USUARIO_VALIDAR_TOKEN(:pUSUARIO, :pTOKEN )",
      array(
        ':pUSUARIO'=> $usr , 
        ':pTOKEN'=>$token
      )
    );
    //retorna usuario_id o false
    return ( is_array($r) && isset($r[0]['AUTORIZADO']) && intval($r[0]['AUTORIZADO']) >0 ) ? $r[0]['AUTORIZADO'] : false ; 
  }

  public function registrarToken( $usr, $token )
  {
    return $this->procedure("CALL USUARIO_REGISTRAR_TOKEN(:pUSUARIO, :pTOKEN )",
      array(
        ':pUSUARIO'=> $usr , 
        ':pTOKEN'=>$token
      )
    );
  }

	public function getDatosUsuario( $user_id ){

			return $this->procedure("CALL USUARIO_DATOS_POR_ID( :pUSER_ID )",
				array( ':pUSER_ID' => $user_id )
			);
	
	}

  public function getArbolModulos($usr)
  {
    return  $this->procedure("CALL LISTAR_ARBOL_MODULOS(:pUSUARIO)",
      array(':pUSUARIO' => $usr )
    );
  }

	public function getUsuariosSubModulo( $pSM_ID ){
		return  $this->procedure("CALL LISTAR_USUARIOS_SUBMODULO(:pSM_ID)",
      array(':pSM_ID' => $pSM_ID )
    );
	}

  public function getFaenas()
  {
    return  $this->procedure("CALL ENCUESTA_LISTAR_FAENA()");

	}
  public function getPreguntasEncuesta($id)
  {
    return $this->procedure("CALL ENCUESTA_LISTAR_PREGUNTAS(:pENCTA_ID)",
            array(':pENCTA_ID' =>$id )
          );
  }
 public function getPreguntasEncuestaCasino($id)
  {
    return $this->procedure("CALL ancestra_externo_produccion.ENCUESTA_LISTAR_PREGUNTAS(:pENCTA_ID)",
            array(':pENCTA_ID' =>$id )
          );
  }


  public function getAlternativas($idEnc, $idPreg)
  {
    return $this->procedure("CALL ENCUESTA_LISTAR_ALTERNATIVAS(:pENCTA_ID,:pPREG_ID)",
            array(':pENCTA_ID' =>$idEnc, ':pPREG_ID'=>$idPreg )
          );
  }

  public function getAlternativasCasino($idEnc, $idPreg)
  {
    return $this->procedure("CALL ancestra_externo_produccion.ENCUESTA_LISTAR_ALTERNATIVAS(:pENCTA_ID,:pPREG_ID)",
            array(':pENCTA_ID' =>$idEnc, ':pPREG_ID'=>$idPreg )
          );
  }


  public function getEstadosContagio()
  {
    return $this->procedure("CALL LISTAR_ESTADO_CONTAGIO()");
  }
  public function getEstadoPasaporte($pas_id)
  {
    return $this->procedure("CALL PASAPORTE_ESTADO(:pPAS_ID)",
          array(
            ':pPAS_ID'=> $pas_id
          ));
  }

  public function registrarEncuesta($rut, $nombre, $email, $celular, $encta_id, $faena)
  {
    return $this->procedure("CALL RESPONDER_ENCUESTA(:pRUT , :pNOMBRE, :pEMAIL, :pCELULAR, :pENC_ID, :pFA_ID)",
            array(
              ':pRUT' => $rut,
              ':pNOMBRE' => $nombre,
              ':pEMAIL' => $email,
              ':pCELULAR' => $celular,
              ':pENC_ID' => $encta_id,
              ':pFA_ID' => $faena
            ));
  }

  public function registrarEncuestaCasino( $sexo, $edad, $ip, $empresa, $casino, $encta_id )
  {
	//		var_dump( $sexo, $edad, $ip, $empresa, $encta_id );
    $r = $this->procedure("CALL ancestra_externo_produccion.RESPONDER_ENCUESTA(:pSEXO, :pEDAD, :pIP, :pEMPRESA, :pCASINO, :pENC_ID )",
            array(
							':pSEXO' => $sexo,
						  ':pEDAD'=> $edad,
              ':pIP' => $ip,
							':pEMPRESA' => $empresa,
							':pCASINO' => $casino,
              ':pENC_ID' => $encta_id,
						));

//			var_dump( $r );

			return $r;
  }


  public function registrarPasaporte($resp_id, $estado=1, $temperatura=null)
  {
    return $this->procedure("CALL REGISTRAR_PASAPORTE(:pPAS_ID , :pESTCONT_ID, :pRESP_ID, :pPAS_TEMPERATURA)",
            array(
              ':pPAS_ID'=> null,
              ':pESTCONT_ID'=> $estado, //cambiar
              ':pRESP_ID'=>$resp_id,
              ':pPAS_TEMPERATURA'=> $temperatura
            ));
  }

  public function obtenerPasaporte($rut, $fecha=null)
  {
    return $this->procedure("CALL PASAPORTE_PERSONA( :pRUT, :pFECHA)",
            array(
              ':pRUT'=> $rut,
              ':pFECHA'=>$fecha,
            ));
  }

  public function registrarPregunta($encta_id, $resp_id, $preg_id, $valorCerrado, $valorAbierto)
  {
    $r = $this->procedure("CALL RESPONDER_PREGUNTA(:pENCTA_ID, :pRESP_ID, :pPREG_ID, :pALT_ID, :pRESP_ABIERTA )",
            $arr = array(
              ':pENCTA_ID' => $encta_id,
              ':pRESP_ID' => $resp_id,
              ':pPREG_ID' => $preg_id,
              ':pALT_ID' => ( empty($valorCerrado) ? null: $valorCerrado ) ,
              ':pRESP_ABIERTA' => $valorAbierto
						), false);
		
		//var_dump( $r, $arr);

		return $r;
  }

  public function registrarPreguntaCasino($encta_id, $resp_id, $preg_id, $valorCerrado, $valorAbierto)
  {
    return $this->procedure("CALL ancestra_externo_produccion.RESPONDER_PREGUNTA(:pENCTA_ID, :pRESP_ID, :pPREG_ID, :pALT_ID, :pRESP_ABIERTA )",
            array(
              ':pENCTA_ID' => $encta_id,
              ':pRESP_ID' => $resp_id,
              ':pPREG_ID' => $preg_id,
              ':pALT_ID' => $valorCerrado,
              ':pRESP_ABIERTA' => $valorAbierto
            ), false);
  }


  public function registrarContactoEstrecho($rut, $tipo, $cuarentena_id, $fecha )
  {
    $r = $this->procedure("CALL REGISTRAR_CONTACTO_ESTRECHO(:pPERS_ID, :pCONTESTIP_ID, :pCUAR_ID, :pCONTEST_FECHA )",
            array(
              ':pPERS_ID' => $rut,
              ':pCONTESTIP_ID' => $tipo,
              ':pCUAR_ID' => $cuarentena_id,
              ':pCONTEST_FECHA' => $fecha,
            ));
    return (isset($r[0]['CONTEST_ID']) && is_numeric($r[0]['CONTEST_ID'])) ? $r[0]['CONTEST_ID'] : false ;
  }

  public function registrarContactoEstrechoDet( $contest_id, $nombre )
  {
    return $this->procedure("CALL REGISTRAR_CONTACTO_ESTRECHO_DET(:pCONTEST_ID , :pCONTESTDET_NOMBRE )",
            array(
              ':pCONTEST_ID' => $contest_id,
              ':pCONTESTDET_NOMBRE' => $nombre
            ),
            false);
  }

  public function registrarCasoContactoEstrecho( $rut, $nombre,$fecha, $pas_id=null, $exam_id=null, $cp_id=null )
  {
    return $this->procedure("CALL REGISTRAR_CASO_CONTACTO_ESTRECHO( :pRUT, :pNOMBRE, :pPAS_ID , :pEXAM_ID , :pCP_ID , :pCCE_FECHA )",
            array(
              ':pRUT' => $rut,
              ':pNOMBRE' => $nombre,
              ':pCCE_FECHA' => $fecha,
              ':pPAS_ID' => $pas_id,
              ':pEXAM_ID' => $exam_id,
              ':pCP_ID' => $cp_id

            ),
            false);
  }

  public function getPersonas( $rut = null )
  {
    return $this->procedure("CALL LISTAR_PERSONAS(:pPERS_RUT )",
            array(
              ':pPERS_RUT' => $rut,
            ));
  }

  public function getTiposExamen( $id = null )
  {
    return $this->procedure("CALL LISTAR_TIPO_EXAMEN(:pTIPEXAM_ID )",
            array(
              ':pTIPEXAM_ID' => $id,
            ));
  }

  public function getUltimosExamenesCovid()
  {
    return $this->procedure("CALL LISTAR_ULTIMOS_EXAMENES_COVID()",
            array(
            ));
  } 

  public function getSeguimientosActivos()
  {
    return $this->procedure("CALL LISTAR_SEGUIMIENTOS_ACTIVOS()",
            array(
            ));
  }
  public function getSeguimientoTipoDetalle()
  {
    return $this->procedure("CALL LISTAR_TIPO_SEGUIMIENTO_DET()",
            array(
            ));
  }

  public function getSeguimientos($id)
  {
    return $this->procedure("CALL LISTAR_SEGUIMIENTOS(:pPERS_ID)",
            array(
              ':pPERS_ID' => $id
            ));
  }

  public function getSeguimientoDetalle($id)
  {
    return $this->procedure("CALL LISTAR_SEGUIMIENTO_DETALLE(:pSEG_ID)",
            array(
              ':pSEG_ID' => $id
            ));
  }

  public function getExamenesCovid($id)
  {
    return $this->procedure("CALL LISTAR_EXAMENES_COVID(:pPERS_ID)",
            array(
              ':pPERS_ID' => $id
            ));
  }

  public function registrarExamen( $rut ,$nombre ,$email ,$celular ,$tipo ,$faena, $turno ,$laboratorio ,$lugar ,$fecha ,$hora ,$estado , $observacion , $usuario_id)
  {
   //var_dump ( $rut ,$nombre ,$email ,$celular ,$tipo ,$faena, $turno ,$laboratorio ,$lugar ,$fecha ,$hora ,$estado , $observacion , $usuario_id);
    return $this->procedure("CALL REGISTRAR_EXAMEN( 
                                                  :pRUT, 
                                                  :pNOMBRE, 
                                                  :pEMAIL,
                                                  :pCELULAR,
                                                  :pTIPEXAM_ID,
                                                  :pFA_ID,
                                                  :pTURNO,
                                                  :pLABORATORIO,
                                                  :pLUGAR,
                                                  :pEXAM_FECHA,
                                                  :pEXAM_HORA,
                                                  :pESTCONT_ID,
																									:pOBSERVACION,
																								  :pUSER_ID )",

            array(
              ':pRUT'=>  $rut ,
              ':pNOMBRE'=> $nombre ,
              ':pEMAIL'=>  $email ,
              ':pCELULAR'=>  $celular ,
              ':pTIPEXAM_ID'=> $tipo ,
              ':pFA_ID'=>  $faena ,
              ':pTURNO'=>  $turno ,
              ':pLABORATORIO'=>  $laboratorio ,
              ':pLUGAR'=>  $lugar ,
              ':pEXAM_FECHA'=> $fecha ,
              ':pEXAM_HORA'=>  $hora ,
              ':pESTCONT_ID'=> $estado ,
							':pOBSERVACION'=>  $observacion,
							':pUSER_ID' => $usuario_id
            )
          ,false);
  }

  public function registrarSeguimiento( $id, $estado_contagio, $pers_id, $cerrado = 0 , $usuario_id )
  {
    
    $r = $this->procedure("CALL REGISTRAR_SEGUIMIENTO( :pSEG_ID, :pESTCONT_ID, :pPERS_ID, :pSEG_CERRADO, :pUSER_ID )",

            array(
              ':pSEG_ID'=>  $id ,
              ':pESTCONT_ID'=> $estado_contagio ,
              ':pPERS_ID'=>  $pers_id ,
              ':pSEG_CERRADO'=>  $cerrado,
						  ':pUSER_ID' => $usuario_id
            )
          );
    return ( is_array($r) && isset($r[0]['SEG_ID']) && $r[0]['SEG_ID']!=0) ? $r[0]['SEG_ID'] : false ; 
  }

  public function registrarSeguimientoDetalle( $seg_id, $tipo, $fecha, $descripcion, $fecha_inicio, $fecha_termino , $usuario_id)
  {
    
    $r = $this->procedure("CALL REGISTRAR_SEGUIMIENTO_DETALLE( :pSEG_ID, :pTIPO_ID, :pFECHA, :pDESCRIPCION, :pFECHA_INICIO, :pFECHA_TERMINO , :pUSER_ID)",

            array(
              ':pSEG_ID'=>  $seg_id ,
              ':pTIPO_ID'=> $tipo ,
              ':pFECHA'=>  $fecha ,
              ':pDESCRIPCION'=>  $descripcion,
              ':pFECHA_INICIO'=>  $fecha_inicio,
							':pFECHA_TERMINO'=>  $fecha_termino,
							':pUSER_ID' => $usuario_id
            )
          );
    return ( is_array($r) && isset($r[0]['ID']) && $r[0]['ID']!=0) ? $r[0]['ID'] : false ; 
  }

  public function getCasosCovid()
  {
    
    return $this->procedure("CALL LISTAR_CASOS_COVID()",

            array(
            )
          );
  }

  public function getCasosParticulares($rut = null)
  {
    
    return $this->procedure("CALL LISTAR_CASOS_PARTICULARES( :pRUT )",

            array(
              ':pRUT' => $rut
            )
          );
  }

  public function registrarCasoCovid($rut, $nombre, $faena, $estado_contagio, $fecha_sintoma, $fecha_conocimiento, $observacion, $usuario_id )
  {
    
    $r = $this->procedure("CALL REGISTRAR_CASO_PARTICULAR( :pRUT , :pNOMBRE , :pESTCONT_ID , :pFA_ID , :pFECHA_SINTOMA, :pFECHA_CONOCIMIENTO , :pOBSERVACION , :pUSER_ID )",

            array(
              ':pRUT' => $rut,
              ':pNOMBRE' => $nombre,
              ':pESTCONT_ID' => $estado_contagio,
              ':pFA_ID' => $faena,
              ':pFECHA_SINTOMA' => $fecha_sintoma,
              ':pFECHA_CONOCIMIENTO' => $fecha_conocimiento,
							':pOBSERVACION' => $observacion,
							':pUSER_ID' => $usuario_id
            )
          );

    return ( is_array($r) && isset($r[0]['ID']) && $r[0]['ID']!=0) ? $r[0]['ID'] : false ; 
  }

  public function getDatosCovid($pENCTA_ID, $pFECHA_INI, $pFECHA_FIN, $pFAENA, $pESTADO)
  {
    
    return $this->procedure("CALL ENCUESTA_LISTAR_ESTADO_CONTAGIO( :pENCTA_ID, :pFECHA_INI, :pFECHA_FIN , :pFAENA, :pESTADO )",
            array(
              ':pENCTA_ID'=>$pENCTA_ID,
              ':pFECHA_INI'=>$pFECHA_INI,
              ':pFECHA_FIN'=>$pFECHA_FIN,
              ':pFAENA'=>$pFAENA,
              ':pESTADO'=>$pESTADO,
            )
          );

  }
  
  public function getRespuestasResumen( $pENCTA_ID )
  {
    
    return $this->procedure("CALL LISTAR_RESPUESTAS_RESUMEN( :pENCTA_ID )" , 
            array(
              ':pENCTA_ID'=>$pENCTA_ID,
            )
          );
  }

	public function getPerfilEncuestaDisc( $pPERS_ID )
	{
			return $this->procedure('CALL ENCUESTA_DISC_CALCULAR_PATRON( :pPERS_ID)',
					array(
							':pPERS_ID' => $pPERS_ID
					)
			);
	}

	public function getPerfilEncuestaDiscPostulacion( $pPROCP_ID )
	{
			return $this->procedure('CALL RECLUTAMIENTO_DISC_CALCULAR_PATRON( :pPROCP_ID)',
					array(
							':pPROCP_ID' => $pPROCP_ID
					)
			);
	}


	public function getRespuestasEncuestaDisc( $pRESP_ID )
	{
			return $this->procedure('CALL LISTAR_RESPUESTAS( :pRESP_ID)',
					array(
							':pRESP_ID' => $pRESP_ID
					)
			);
	}
	public function eliminarRespuestaEncuesta( $pRESP_ID )
	{
			$r = $this->procedure('CALL ELIMINAR_RESPUESTA_ENCUESTA( :pRESP_ID)',
					array(
							':pRESP_ID' => $pRESP_ID
					)
			);
		
			return ( is_array($r) && isset($r[0]['RESULTADO']) && $r[0]['RESULTADO']=='OK' ) ? true : false ; 
	
	}

	public function eliminarExamen( $pEXAM_ID, $pUSER_ID )
	{
			$r = $this->procedure('CALL  ELIMINAR_EXAMEN( :pEXAM_ID, :pUSER_ID )',
					array(
							':pEXAM_ID' => $pEXAM_ID,
							':pUSER_ID' => $pUSER_ID
					)
			);
		
			return ( is_array($r) && isset($r[0]['RESULTADO']) && $r[0]['RESULTADO']=='OK' ) ? true : false ; 
	
	}

	public function cambiarClave( $pUSER_ID , $pPASS_ANTIGUA, $pPASS_NUEVA )
	{
			$r = $this->procedure('CALL USUARIO_CAMBIAR_CLAVE( :pUSER_ID , :pPASS_ANTIGUA, :pPASS_NUEVA)',
					array(
						':pUSER_ID' => $pUSER_ID ,
						':pPASS_ANTIGUA' => $pPASS_ANTIGUA,
						':pPASS_NUEVA' => $pPASS_NUEVA
					)
			);
		
			return ( is_array($r) && isset($r[0]['USER_ID']) && $r[0]['USER_ID']==$pUSER_ID ) ? true : false ; 
	
	}

public function resetClavePorCorreo( $pCORREO , $pPASS_NUEVA )
	{
			$r = $this->procedure('CALL USUARIO_RESET_CLAVE_XCORREO( :pCORREO , :pPASS_NUEVA)',
					array(
						':pCORREO' => $pCORREO ,
						':pPASS_NUEVA' => $pPASS_NUEVA
					)
			);
		
			return ( is_array($r) && isset($r[0]['USER_ID']) && is_numeric($r[0]['USER_ID']) && $r[0]['USER_ID'] > 0 ) ? true : false ; 
	
}

public function resetClavePorRutPostulacion( $pRUT , $pPASS_NUEVA )
	{
			$r = $this->procedure('CALL POSTULANTE_RESET_CLAVE_XRUT( :pRUT , :pPASS_NUEVA)',
					array(
						':pRUT' => $pRUT ,
						':pPASS_NUEVA' => $pPASS_NUEVA
					)
			);
		
			//return ( is_array($r) && isset($r[0]['POST_ID']) && is_numeric($r[0]['POST_ID']) && $r[0]['POST_ID'] > 0 ) ? true : false ; 	
			return ( is_array($r) && isset($r[0]['POST_EMAIL']) && !empty($r[0]['POST_EMAIL'])  ) ? $r[0]['POST_EMAIL']: false ; 	
}


public function getAreas()
  {
    return  $this->procedure("CALL RECLUTAMIENTO_AREAS()");
  }

public function getTiposCompetencias()
	{
			$r = $this->procedure('CALL RECLUTAMIENTO_COMPETENCIAS_TIPO()',
					array()
			);
		
			return ( is_array($r) && !empty( $r ) ) ? $r : false ;
	
}
public function getPerfilesCargo( $perf_id)
	{
			$r = $this->procedure('CALL RECLUTAMIENTO_CARGO_PERFIL( :pPERFID )',
					array(':pPERFID' => $perf_id )
			);
		
			return ( is_array($r) && !empty( $r ) ) ? $r : false ;
}

public function getCompetencias( $perf_id)
	{
			$r = $this->procedure('CALL RECLUTAMIENTO_COMPETENCIAS( :pPERFID )',
					array(':pPERFID' => $perf_id )
			);
		
			return ( is_array($r) && !empty( $r ) ) ? $r : false ;
	
}

public function getImplementos( $perf_id )
	{
			$r = $this->procedure('CALL RECLUTAMIENTO_IMPLEMENTOS( :pPERFID )',
					array(':pPERFID' => $perf_id )
			);
		
			return ( is_array($r) && !empty( $r ) ) ? $r : false ;
	
}

public function getRecExamenes( $perf_id )
	{
			$r = $this->procedure('CALL RECLUTAMIENTO_EXAMENES( :pPERFID )',
					array(':pPERFID' => $perf_id )
			);
		
			return ( is_array($r) && !empty( $r ) ) ? $r : false ;
	
}

public function getTurnos( $turno_id = null )
	{
			$r = $this->procedure('CALL RECLUTAMIENTO_TURNOS(:pTURNO_ID)',
					array(':pTURNO_ID' => $turno_id )
			);
		
			return ( is_array($r) && !empty( $r ) ) ? $r : false ;
}

public function getDocumentoTipos( $perf_id = null  ){
		$r = $this->procedure('CALL RECLUTAMIENTO_DOCUMENTO_TIPOS(:pPERFID)',
					array(':pPERFID' => $perf_id )
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;
}

public function registrarSolicitudCargo( $pESTADO_ID, $pDESCRIPCION, $pFECHA, $pFA_ID, $pAREA_ID , $pUSER_ID , $pURGENTE = 0 )
	{
			
			$pURGENTE = $pURGENTE==false? 0:1;
			
			$r = $this->procedure('CALL RECLUTAMIENTO_GUARDAR_SOLICITUD(:pESTADO_ID, :pDESCRIPCION, :pFECHA, :pFA_ID, :pAREA_ID, :pUSER_ID, :pURGENTE )',
			

					$ar = array(
						':pESTADO_ID' => $pESTADO_ID ,
						':pDESCRIPCION' => $pDESCRIPCION,
						':pFECHA' => $pFECHA,
						':pFA_ID' => $pFA_ID,
						':pAREA_ID' => $pAREA_ID,
						':pUSER_ID' => $pUSER_ID,
						':pURGENTE' => $pURGENTE
					)
			);
		//var_dump($ar, $r);
			return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
	
	}

  public function registrarSolicitudCargoDetalle($pPERF_ID, $pSOL_ID, $pTUR_ID,$pOBSERVACION, $pESTADO, $pCANTIDAD, $pFA_ID, $pAREA_ID, $pFECHA, $pSUELDO, $pNRO_CONTRATO,$pEVALUADOR_ID )
  {
    $r = $this->procedure("CALL RECLUTAMIENTO_GUARDAR_SOLICITUD_DETALLE(:pPERF_ID, :pSOL_ID, :pTUR_ID,:pOBSERVACION, :pESTADO, :pCANTIDAD, :pFA_ID, :pAREA_ID, :pFECHA, :pSUELDO, :pNRO_CONTRATO, :pEVALUADOR_ID )",
            array(
              ':pPERF_ID' => $pPERF_ID,
              ':pSOL_ID' => $pSOL_ID,
              ':pTUR_ID' => $pTUR_ID,
              ':pOBSERVACION' => $pOBSERVACION,
              ':pESTADO' => $pESTADO,
				':pCANTIDAD' => $pCANTIDAD,
			  ':pFA_ID'=> $pFA_ID,
							':pAREA_ID' => $pAREA_ID,
							':pFECHA' => $pFECHA,
							':pSUELDO' => $pSUELDO,
              ':pNRO_CONTRATO' => $pNRO_CONTRATO,
						  ':pEVALUADOR_ID' => $pEVALUADOR_ID
						) 
		);
		
		//var_dump($r);
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 

  }

	public function registrarSolicitudCargoCompetencia($comp_id, $perfil_id, $p_estado )
  {
    return $this->procedure("CALL RECLUTAMIENTO_GUARDAR_SOLICITUD_COMPETENCIA(:pCOMP_ID, :pRECSCP_ID, :pESTADO)",
            array(
              ':pCOMP_ID' => $comp_id,
              ':pRECSCP_ID' => $perfil_id,
              ':pESTADO' => $p_estado,
            ),
            false);
			//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
  }
	
	public function registrarSolicitudCargoDocumento( $doctip, $perfil_id, $p_estado )
  {
    return $this->procedure("CALL RECLUTAMIENTO_GUARDAR_SOLICITUD_DOCUMENTO( :pDOCTIP_ID, :pRECSCP_ID, :pESTADO) ",
            array(
              ':pDOCTIP_ID' => $doctip,
              ':pRECSCP_ID' => $perfil_id,
              ':pESTADO' => $p_estado,
            ),
            false);
			//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
  }
	
	public function registrarSolicitudCargoImplemento( $imp, $perfil_id, $p_estado )
  {
    return $this->procedure("CALL RECLUTAMIENTO_GUARDAR_SOLICITUD_IMPLEMENTO( :pIMP_ID, :pRECSCP_ID, :pESTADO) ",
            array(
              ':pIMP_ID' => $imp,
              ':pRECSCP_ID' => $perfil_id,
              ':pESTADO' => $p_estado,
            ),
            false);
			//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
	}

	public function getSolicitudesReclutamientoUsuario( $usuario_id )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_LISTA_SOLICITUD_USER(:pUSER_ID)',
					array(':pUSER_ID' => $usuario_id )
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}
	
	public function getSolicitudesReclutamientoEstado( $estado_id )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_LISTA_SOLICITUD_XESTADO(:pESTADO)',
					array(':pESTADO' => $estado_id )
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}

	public function getCompetenciasPerfilSolReclutamiento( $id )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_COMPETENCIA_SOL_CARGO_PERFIL(:pCARGOPERF_ID)',
					array(':pCARGOPERF_ID' => $id )
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}
	
	public function getDocumentosPerfilSolReclutamiento( $id )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_DOCUMENTO_SOL_CARGO_PERFIL(:pCARGOPERF_ID)',
					array(':pCARGOPERF_ID' => $id )
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}

	public function getImplementosPerfilSolReclutamiento( $id )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_IMPLEMENTO_SOL_CARGO_PERFIL(:pCARGOPERF_ID)',
					array(':pCARGOPERF_ID' => $id )
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}
	
	public function getImplementosPerfilOferta( $id )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_IMPLEMENTO_OFERTA_CARGO_PERFIL( :pROCP_ID )',
					array(':pROCP_ID' => $id )
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}

	public function getPerfilesSolReclutamiento( $sol_id, $perf_id = null )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_CARGO_PERFIL_SOLICITUD( :pSOL_ID,:pPERF_ID)',
				array(':pSOL_ID' => $sol_id,
						  ':pPERF_ID' => $perf_id	
				)
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}
	
	public function getPlantillasEtapas()
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_LISTA_PLANTILLA_ETAPAS()',
				array()
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}

	#--------------------> GUARDAR OFERTA LABORAL
	
	public function registrarOfertaCargo( $pSOL_ID, $pTIPESTADO, $pTITULO, $pDESCRIPCION, $pFECHA_PUBLICACION, $pFECHA_CIERRE, $pESTADO, $pUSER )
	{
//var_dump( $pSOL_ID, $pTIPESTADO, $pTITULO, $pDESCRIPCION, $pFECHA_PUBLICACION, $pFECHA_CIERRE, $pESTADO, $pUSER );
			$r = $this->procedure('CALL RECLUTAMIENTO_GUARDAR_OFERTA(:pSOL_ID, :pTIPESTADO, :pTITULO, :pDESCRIPCION, :pFECHA_PUBLICACION, :pFECHA_CIERRE, :pESTADO, :pUSER)',
					$ar= array(
						':pSOL_ID' => $pSOL_ID, 
						':pTIPESTADO'=> $pTIPESTADO, 
						':pTITULO'=> $pTITULO, 
						':pDESCRIPCION' => $pDESCRIPCION, 
						':pFECHA_PUBLICACION' => $pFECHA_PUBLICACION, 
						':pFECHA_CIERRE'=> $pFECHA_CIERRE, 
						':pESTADO'=> $pESTADO, 
						':pUSER' => $pUSER
					)
			);
//var_dump($ar, $r);		
			return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
	
	}

  public function registrarOfertaCargoDetalle( $pOFE_ID, $pCPERF_ID, $pRECSCP_ID, $pDESCRIPCION, $pTURNO_ID, $pCANTIDAD, $pFA_ID, $pAREA_ID, $pESTADO, $pPLANTILLA_ID, $pSUELDO=0)
  {
    $r = $this->procedure("CALL RECLUTAMIENTO_GUARDAR_OFERTA_DETALLE(:pOFE_ID, :pCPERF_ID, :pRECSCP_ID,:pDESCRIPCION, :pTURNO_ID, :pCANTIDAD, :pFA_ID, :pAREA_ID ,:pESTADO, :pPLANTILLA_ID , :pSUELDO )",
            array(
								':pOFE_ID' => $pOFE_ID, 
								':pCPERF_ID'=> $pCPERF_ID, 
								':pRECSCP_ID' => $pRECSCP_ID,
								':pDESCRIPCION' => $pDESCRIPCION, 
								':pTURNO_ID' => $pTURNO_ID, 
								':pCANTIDAD' => $pCANTIDAD, 
								':pFA_ID' => $pFA_ID, 
								':pAREA_ID' => $pAREA_ID,
								':pESTADO' => $pESTADO,
								':pPLANTILLA_ID' => $pPLANTILLA_ID,
								':pSUELDO' => $pSUELDO
						) 
		);
	//	var_dump( $pOFE_ID, $pCPERF_ID, $pRECSCP_ID, $pDESCRIPCION, $pTURNO_ID, $pCANTIDAD, $pFA_ID, $pAREA_ID, $pESTADO, $pPLANTILLA_ID, $pSUELDO, $r);

		//var_dump($r);
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 

	}
	
	public function registrarOfertaCargoCompetencia( $perfil_id, $comp_id, $user_id, $ptje_min = 0 , $ptje_max = 0 )
  {
    return $this->procedure("CALL RECLUTAMIENTO_GUARDAR_OFERTA_COMPETENCIA(:pCPERF_ID, :pCOMP_ID, :pEVALUADOR_USER_ID, :pPUNTAJE_MAXIMO, :pPUNTAJE_MINIMO)",
            array(
								':pCPERF_ID' => $perfil_id, 
								':pCOMP_ID' =>$comp_id, 
								':pEVALUADOR_USER_ID' => $user_id, 
								':pPUNTAJE_MAXIMO' => $ptje_max, 
								':pPUNTAJE_MINIMO' => $ptje_min
            ),
            false);
			//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
  }
	
	public function registrarOfertaCargoDocumento( $perfil_id, $tipo_doc, $estado )
  {
    return $this->procedure("CALL RECLUTAMIENTO_GUARDAR_OFERTA_DOCUMENTO( :pDOCTIP_ID, :pCPERF_ID, :pESTADO ) ",
            array(
								':pDOCTIP_ID'=> $tipo_doc, 
								':pCPERF_ID'=> $perfil_id, 
								':pESTADO' => $estado
            ),
            false);
			//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
  }
	
	public function registrarOfertaCargoImplemento( $perfil_id, $imp_id, $estado = 1 )
  {
    return $this->procedure("CALL RECLUTAMIENTO_GUARDAR_OFERTA_IMPLEMENTO(:pCPERF_ID, :pIMP_ID, :pESTADO)",								
            array(
								':pCPERF_ID' => $perfil_id, 
								':pIMP_ID' => $imp_id, 
								':pESTADO' => $estado
            ),
            false);
			//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
  }
	
	public function registrarOfertaCargoExamen( $rocp_id, $rex_id )
  {
    return $this->procedure("CALL RECLUTAMIENTO_GUARDAR_OFERTA_EXAMEN( :pROCP_ID, :pREX_ID )",
            array(
								':pROCP_ID' => $rocp_id, 
								':pREX_ID' => $rex_id, 
            ),
            false);
			//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
  }
# -------------> FIN GUARDAR OFERTA LABORAL
	
	public function getOfertasPublicadas() 
  {
    $r = $this->procedure("CALL RECLUTAMIENTO_OFERTAS_PUBLICADAS()",
            array(
						) 
		);

		return ( is_array($r) && !empty( $r ) ) ? $r : false ;
	}
	
	public function getSolPendienteAprobRecluamiento( $user_id ) 
  {
    $r = $this->procedure("CALL RECLUTAMIENTO_LISTA_SOL_PENDIENTE_APROB( :pUSER_ID )",
				array(
						':pUSER_ID'=> $user_id
						) 
		);

		return ( is_array($r) && !empty( $r ) ) ? $r : false ;
	}
	
	public function cambiarEvaluadorCargoPerfilSolReclutamiento( $perfil_id, $user_id, $usuario_id ) 
  {
    return $this->procedure("CALL RECLUTAMIENTO_ACTUALIZAR_EVALUADOR_SOLDET( :pRECSCP_ID , :pUSR_ID, :pUSUARIO_ID )",
				array(
						':pRECSCP_ID' => $perfil_id,
						':pUSR_ID'=> $user_id,
						':pUSUARIO_ID' => $usuario_id
						)
		);
	}


	public function cambiarEstadoCargoPerfilSolReclutamiento( $perfil_id, $estado, $user_id )
  {
    return $this->procedure("CALL RECLUTAMIENTO_CAMBIAR_ESTADO_SOL_CARGO_PERFIL( :pRECSCP_ID , :pESTADO, :pUSER_ID )",								
            array(
								':pRECSCP_ID' => $perfil_id, 
								':pESTADO' => $estado,
								':pUSER_ID' => $user_id
            ),
            false);
  }
	
	public function cambiarEstadoSolicitudReclutamiento( $sol_id, $estado, $user_id )
  {
    return $this->procedure("CALL RECLUTAMIENTO_CAMBIAR_ESTADO_SOL( :pSOL_ID, :pESTADO_ID, :pUSER_ID )",								
            array(
								':pSOL_ID' => $sol_id, 
								':pESTADO_ID' => $estado,
								':pUSER_ID' => $user_id
            ),
            false);
  }

	#################### POSTULACION 
	public function postulanteLogin( $rut, $clave )
  {
    $r = $this->procedure("CALL POSTULANTE_LOGIN(:pRUT, :pPASS )",
            array(
								':pRUT' => $rut, 
								':pPASS' => $clave,
						) 
		);
		
		if( !is_array($r) || !isset( $r[0]['USER_ID'] ) )
				return false;
		else{
				
				$user_id = $r[0]['USER_ID'];
				
				$existe = $r[0]['EXISTE'];

				if( is_numeric( $user_id ) ){
						return $user_id;
				}
				else if( $existe == 1){
						return 0;
				}else{
						return false;
				}
		}

	}

	public function crearAccesoPostulante( $rut, $dv, $clave )
  {
    $r = $this->procedure("CALL POSTULANTE_CREAR_CLAVE(:pRUT, :pDV, :pPASS )",
            array(
								':pRUT' => $rut, 
								':pDV'=> $dv, 
								':pPASS' => $clave,
						) 
		);
		
		return ( is_array($r) && isset($r[0]['USER_ID']) ) ? $r[0]['USER_ID'] : false ; 

	}

	
	public function getDatosPostulante( $user_id, $rocp_id = null ) 
  {
    $r = $this->procedure("CALL POSTULANTE_DATOS_POSTULACION( :pUSER_ID, :pROCP_ID )",
				array(
						':pUSER_ID'=> $user_id,
						':pROCP_ID' => $rocp_id
						) 
		);

		return ( is_array($r) && !empty( $r ) ) ? $r : false ;
	}

	
	public function actualizarDatosPostulante( 
			$pPOST_ID, $pNOMBRE, $pAPATERNO, $pAMATERNO, $pEMAIL,
			$pFECHA, $pNACIONALIDAD, $pSEXO, $pESTADO_CIVIL,
			$pTEL_CELULAR, $pTEL_FIJO, $pTEL_CONTACTO, $pNIVEL_EDU,
			$pPROFESION, $pNOMBRE_CONTACTO, $pAFP, $pSALUD, $pSALUD_UF,
		  $pDIRECCION,$pCIUDAD, $pTALLA_PANTALON, 
			$pTALLA_CALZADO, $pTALLA_CAMISA, $pBE_ID,$pTCBE_ID, $pCUENTA
 	) 
  {
			$pSALUD_UF = str_replace(',', '.', $pSALUD_UF);

			$parametros = array(
					':pPOST_ID'=>	$pPOST_ID, 
					':pNOMBRE'=>	$pNOMBRE, 
					':pAPATERNO'=>	$pAPATERNO, 
					':pAMATERNO'=>	$pAMATERNO, 
					':pEMAIL'=>	$pEMAIL,
					':pFECHA'=>	$pFECHA,
					':pESTADO_CIVIL'=>	$pESTADO_CIVIL,
					':pNACIONALIDAD'=>	$pNACIONALIDAD,
					':pNIVEL_EDU'=>	$pNIVEL_EDU,
					':pSEXO'=>	$pSEXO,
					':pTEL_CELULAR'=>	$pTEL_CELULAR,
					':pTEL_FIJO'=>	$pTEL_FIJO,
					':pTEL_CONTACTO'=>	$pTEL_CONTACTO,
					':pNOMBRE_CONTACTO'=>	$pNOMBRE_CONTACTO,
					':pPROFESION'=>	$pPROFESION,
					':pAFP'=> $pAFP,
					':pSALUD' => $pSALUD,
					':pSALUD_UF' => $pSALUD_UF,
					':pDIRECCION' =>	$pDIRECCION,
					':pCIUDAD' =>	$pCIUDAD, 
					':pTALLA_PANTALON' =>	$pTALLA_PANTALON, 
					':pTALLA_CALZADO' =>	$pTALLA_CALZADO, 
					':pTALLA_CAMISA' =>	$pTALLA_CAMISA, 
					':pBE_ID' =>	$pBE_ID,
					':pTCBE_ID' => $pTCBE_ID,
					':pCUENTA' => $pCUENTA
			);
/*
			var_dump($parametros);
				var_dump("CALL POSTULANTE_ACTUALIZAR_DATOS_PERSONALES(
						:pPOST_ID, 
						:pNOMBRE, 
						:pAPATERNO, 
						:pAMATERNO, 
						:pEMAIL,
						:pFECHA,
						:pESTADO_CIVIL, 
						:pNACIONALIDAD,
						:pNIVEL_EDU , 
						:pSEXO,  
						:pTEL_CELULAR, 
						:pTEL_FIJO,	
						:pTEL_CONTACTO,	 
						:pNOMBRE_CONTACTO, 
						:pPROFESION,
						:pAFP,
						:pSALUD,
						:pSALUD_UF,
						:pDIRECCION,
						:pCIUDAD, 
						:pTALLA_PANTALON, 
						:pTALLA_CALZADO, 
						:pTALLA_CAMISA, 
						:pBE_ID, 
						:pTCBE_ID,
						:pCUENTA

 )");*/
			return $this->procedure("CALL POSTULANTE_ACTUALIZAR_DATOS_PERSONALES(
						:pPOST_ID, 
						:pNOMBRE, 
						:pAPATERNO, 
						:pAMATERNO, 
						:pEMAIL,
						:pFECHA,
						:pESTADO_CIVIL, 
						:pNACIONALIDAD,
						:pNIVEL_EDU , 
						:pSEXO,  
						:pTEL_CELULAR, 
						:pTEL_FIJO,	
						:pTEL_CONTACTO,	 
						:pNOMBRE_CONTACTO, 
						:pPROFESION,
						:pAFP,
						:pSALUD,
						:pSALUD_UF,
						:pDIRECCION,
						:pCIUDAD, 
						:pTALLA_PANTALON, 
						:pTALLA_CALZADO, 
						:pTALLA_CAMISA, 
						:pBE_ID, 
						:pTCBE_ID,
						:pCUENTA

 )",
				$parametros
		);
	}
	
	public function getIdPostulante( $rut )
  {
    $r = $this->procedure("CALL POSTULANTE_GET_ID( :pRUT )",
            array(
								':pRUT' => $rut, 
						) 
		);
		
		return ( is_array($r) && isset($r[0]['USER_ID']) ) ? $r[0]['USER_ID'] : false ; 

	}

	
	public function getNacionalidades()
  {
    return  $this->procedure("CALL POSTULANTE_LISTA_NACIONALIDAD()",
      array()
    );
  }

	public function getNivelesEducacional()
  {
    return  $this->procedure("CALL POSTULANTE_LISTA_NIVEL_EDUCACIONAL()",
      array()
    );
	}

	public function getEstadosCivil()
  {
    return  $this->procedure("CALL POSTULANTE_LISTA_ESTADO_CIVIL()",
      array()
    );
	}

	
	public function getReqCargoPerfilOferta( $pROCP_ID = null, $pPOST_ID = null)
  {
    return  $this->procedure("CALL RECLUTAMIENTO_LISTA_REQUERIMIENTO_OFERTA( :pROCP_ID, :pPOST_ID ) ",
				array(':pROCP_ID' => $pROCP_ID,
							':pPOST_ID' => $pPOST_ID
						)
				);
	}
	
	public function getListaAFP()
  {
    return  $this->procedure("CALL POSTULANTE_LISTA_AFP() ",
      array()
    );
	}
	
	public function getListaSalud()
  {
    return  $this->procedure("CALL POSTULANTE_LISTA_SALUD()",
      array()
    );
	}

	public function getListaBanco()
  {
    return  $this->procedure("CALL POSTULANTE_LISTA_BANCO()",
      array()
    );
	}

	public function registrarDocumentoPostulante($pRDOCOFE_ID , $pPOST_ID, $pOBSERVACION)
  {
    $r = $this->procedure("CALL POSTULANTE_GUARDAR_DOCUMENTO( :pRDOCOFE_ID , :pPOST_ID, :pOBSERVACION )",
            array(
								':pRDOCOFE_ID' => $pRDOCOFE_ID, 
								':pPOST_ID' => $pPOST_ID, 
								':pOBSERVACION' => $pOBSERVACION 
						) 
		);
		
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 

	}
	
	public function guardarPostulacion( $pPOST_ID,  $pROCP_ID )
  {
    $r = $this->procedure("CALL POSTULANTE_GUARDAR_POSTULACION(:pPOST_ID, :pROCP_ID)",
            array(
								':pPOST_ID' => $pPOST_ID, 
								':pROCP_ID'=> $pROCP_ID 
						) 
		);
		
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 

	}
	
	public function getListaPostulantes( $pROCP_ID = null, $pRETA_ID = null, $pUSR_ID=null )
	{

    $r=  $this->procedure("CALL POSTULANTE_LISTA_POSTULANTE( :pROCP_ID, :pRETA_ID, :pUSR_ID )",
			$ar=	array( 
						':pROCP_ID' =>$pROCP_ID,
						':pRETA_ID' => $pRETA_ID,
						':pUSR_ID' =>$pUSR_ID	)
		);
			
			//if( $pROCP_ID == 90 )
			//		var_dump( $r, $ar);


		return $r;
	}
	
	public function getListaEtapasOfertaCargoPerfil( $pROCP_ID , $pPOST_ID = null )
  {
    return  $this->procedure("CALL RECLUTAMIENTO_LISTA_ETAPA_OFERTA_PERFIL( :pROCP_ID, :pPOST_ID )",
				array( 
						':pROCP_ID' =>$pROCP_ID,
						':pPOST_ID' =>$pPOST_ID
				)
    );
	}

	public function getListaExamenOfertaCargoPerfil( $pROCP_ID , $pPOST_ID = null )
  {
    return  $this->procedure("CALL RECLUTAMIENTO_LISTA_EXAMEN_CARGO_PERFIL( :pROCP_ID, :pPOST_ID )",
				array( 
						':pROCP_ID' =>$pROCP_ID,
						':pPOST_ID' =>$pPOST_ID
				)
    );
	}

	public function registrarEtapaPostulacion( $pROCPETA_ID, $pPROCP_ID, $pPUNTAJE, $pDESCRIPCION, $pFECHA , $pDIRECCION, $pLINK, $pOBSERVACION, $pAPROBADO , $pUSER_ID )
  {
    $r = $this->procedure("CALL POSTULANTE_GUARDAR_ETAPA(:pROCPETA_ID, :pPROCP_ID, :pPUNTAJE, :pDESCRIPCION, :pFECHA, :pDIRECCION, :pLINK , :pOBSERVACION, :pAPROBADO, :pUSER_ID )",
            array(
								':pROCPETA_ID' => $pROCPETA_ID, 
								':pPROCP_ID' => $pPROCP_ID, 
								':pPUNTAJE' => $pPUNTAJE, 
								':pDESCRIPCION' => $pDESCRIPCION, 
								':pFECHA' => $pFECHA,
								':pDIRECCION' => $pDIRECCION,
								':pLINK' => $pLINK,
								':pOBSERVACION' =>$pOBSERVACION,
								':pAPROBADO' => $pAPROBADO,
								':pUSER_ID' => $pUSER_ID
						) 
		);
		
		//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0] : false ; 
	}
	
	public function registrarSubEtapaPostulacion( $pPOSTOFEETA_ID, $pRSETA_ID, $pAPROBADO ,$user_id )
  {
    return $this->procedure("CALL POSTULANTE_GUARDAR_SUBETAPA(:pPOSTOFEETA_ID,:pRSETA_ID,:pAPROBADO, :pUSER_ID )",
            array(
								':pPOSTOFEETA_ID' => $pPOSTOFEETA_ID, 
								':pRSETA_ID' => $pRSETA_ID, 
								':pAPROBADO' => $pAPROBADO,
								':pUSER_ID' => $user_id
						)
		);
	}


	public function registrarSolicitudEtapaExternaPostulacion( $pPOSTOFEETA_ID, $pRSETA_ID, $pMENSAJE= null )
  {
    $r = $this->procedure("CALL POSTULANTE_GUARDAR_ETAPA_CORREO( :pPOSTOFEETA_ID, :pRSETA_ID, :pMENSAJE )",
				array(
								':pPOSTOFEETA_ID' => $pPOSTOFEETA_ID, 
								':pRSETA_ID' => $pRSETA_ID, 
								':pMENSAJE' => $pMENSAJE, 
						) 
		);
		
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 

	}

	public function registrarSolicitudEtapaExternaPostulacion2(  $pPOST_ID, $pROCP_ID, $pETAPA_TIPO, $pMENSAJE= null )
  {
    return $this->procedure("CALL POSTULANTE_GUARDAR_ETAPA_CORREO_2( :pPOST_ID, :pROCP_ID, :pETAPA_TIPO, :pMENSAJE )",
				array(
								':pPOST_ID' => $pPOST_ID, 
								':pROCP_ID' => $pROCP_ID, 
								':pETAPA_TIPO' => $pETAPA_TIPO, 
								':pMENSAJE' => $pMENSAJE, 
						) 
		);
		
		//return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 

	}

	public function registrarEtapaExternaPostulacion(  $post_id, $rocp_id, $reta_id , $puntaje, $aprobado, $user_id)
  {
    $r = $this->procedure("CALL POSTULANTE_GUARDAR_ETAPA_EXTERNO( :pPOST_ID, :pROCP_ID, :pRETA_ID, :pPUNTAJE, :pAPROBADO, :pUSER_ID )",
				array(
								':pPOST_ID' => $post_id, 
								':pROCP_ID' => $rocp_id, 
								':pRETA_ID' => $reta_id, 
								//':pETAPA_TIPO' => $etapa_tipo, 
								':pPUNTAJE' => $puntaje, 
								':pAPROBADO' => $aprobado, 
								':pUSER_ID' => $user_id
						) 
		);
		
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 

	}
	public function getDetalleEtapasPostulacion($pPROCP_ID, $pROCPETA_ID=null  )
  {
    return  $this->procedure("CALL POSTULANTE_DETALLE_ETAPA(:pPROCP_ID, :pROCPETA_ID )",
				array( 
						':pPROCP_ID' =>$pPROCP_ID,
						':pROCPETA_ID' =>$pROCPETA_ID
				)
    );
	}


	public function getCompetenciasOfertaPerfil( $perfil_id, $post_id = null, $tipo=null){
			return $this->procedure("CALL RECLUTAMIENTO_COMPETENCIAS_OFE_CARGO_PERFIL( :pPERFIL_ID , :pPOST_ID , :pCOMPTIP_ID )",
					array( 
							':pPERFIL_ID' => $perfil_id,
						  ':pPOST_ID' => $post_id,
						  ':pCOMPTIP_ID' => $tipo )			
			);
	}
	
	public function registrarEvaluacionCompetenciaPostulacion( $pROFECOMP_ID , $pPROCP_ID , $pPUNTAJE , $pOBSERVACION , $pUSER_ID)
  {
    $r =  $this->procedure("CALL POSTULANTE_GUARDAR_COMPETENCIA_EVALUACION( :pROFECOMP_ID , :pPROCP_ID , :pPUNTAJE , :pOBSERVACION, :pUSER_ID )",
				$p = array( 
						':pROFECOMP_ID' => $pROFECOMP_ID , 
						':pPROCP_ID' => $pPROCP_ID, 
						':pPUNTAJE' => $pPUNTAJE, 
						':pOBSERVACION' => $pOBSERVACION,
						':pUSER_ID' => $pUSER_ID
				),false
		);

		return $r;
	}
	
	public function registrarEvaluacionExamenPostulacion( $pPROCP_ID, $pREX_ID , $pAPTO )
  {
    return  $this->procedure("CALL POSTULANTE_GUARDAR_EXAMEN_EVALUACION(  :pPROCP_ID, :pREX_ID , :pAPTO )",
				array( 
						':pPROCP_ID' => $pPROCP_ID, 
						':pREX_ID' => $pREX_ID, 
						':pAPTO' => $pAPTO
				),false
    );
	}
	
	public function getListaInduccionOfertaCargoPerfil( $pROCP_ID , $pPOST_ID = null , $pRTIND_ID )
  {
    return  $this->procedure("CALL RECLUTAMIENTO_LISTA_INDUCCION_CARGO_PERFIL( :pROCP_ID, :pPOST_ID, :pRTIND_ID )",
				array( 
						':pROCP_ID' =>$pROCP_ID,
						':pPOST_ID' =>$pPOST_ID,
						':pRTIND_ID' =>$pRTIND_ID
				)
    );
	}
	
	public function getListaInduccionesProgramadas( $pRTIND_ID )
  {
    return  $this->procedure("CALL RECLUTAMIENTO_LISTA_INDUCCIONES_PROGRAMADAS( :pRTIND_ID )",
				array( 
						':pRTIND_ID'=>$pRTIND_ID
				)
    );
	}
	
	public function getListaPostulantesInduccion( $pRIND_ID, $pUSER_ID, $pFECHA )
  {
    return  $this->procedure("CALL RECLUTAMIENTO_LISTA_POSTULANTES_INDUCCION( :pRIND_ID, :pUSER_ID, :pFECHA )",
				array( 
						':pRIND_ID' =>$pRIND_ID, 
						':pUSER_ID' =>$pUSER_ID, 
						':pFECHA' => $pFECHA
				)
    );
	}

	public function registrarInduccionPostulacion( $pROCPETA_ID,
																$pPOST_ID,
																$pRIND_ID,
																$pUSER_ID, 
																$pFECHA, 
																$pDIRECCION , 
																$pLINK	)
  {
    return  $this->procedure("CALL POSTULANTE_GUARDAR_INDUCCION( 
																:pROCPETA_ID,
																:pPOST_ID,
																:pRIND_ID,
																:pUSER_ID, 
																:pFECHA, 
																:pDIRECCION , 
																:pLINK  )",
				array( 
						
						':pROCPETA_ID' => $pROCPETA_ID,
						':pPOST_ID' => $pPOST_ID,
						':pRIND_ID' =>$pRIND_ID,
						':pUSER_ID'=>$pUSER_ID, 
						':pFECHA' =>$pFECHA, 
						':pDIRECCION'=>$pDIRECCION , 
						':pLINK'=>$pLINK 
				)
    );
	}
	
	public function registrarInduccionEvaluacion( $pPOSTOFEIND_ID , $pEVALUACION, $pROCPETA_ID, $pUSER_ID )
  {
    $r =  $this->procedure("CALL POSTULANTE_GUARDAR_INDUCCION_EVALUACION( :pPOSTOFEIND_ID , :pEVALUACION, :pROCPETA_ID , :pUSER_ID )",
				array( 
						
						':pPOSTOFEIND_ID' => $pPOSTOFEIND_ID,
						':pEVALUACION'=>$pEVALUACION ,
						':pROCPETA_ID'=>$pROCPETA_ID ,
						':pUSER_ID' => $pUSER_ID
				)
    );


		return ( is_array($r) && isset($r[0][0]['APROBADO']) ) ? $r[0][0]['APROBADO'] : false ; 
	
	}
	
	
	public function getListaCorreoProceso( $pPROCESO )
  {
    return  $this->procedure("CALL RECLUTAMIENTO_LISTA_CORREO( :pPROCESO )",
				array(
						':pPROCESO' =>$pPROCESO, 
				)
    );
	}

	public function registrarSolExamenCovid19($pPROCP_ID, $pUSER_ID){

			return $this->procedure("CALL RECLUTAMIENTO_REGISTRAR_EXAMEN_PENDIENTE( :pPROCP_ID, :pUSER_ID )",
					array(
							":pPROCP_ID"=>$pPROCP_ID,
							":pUSER_ID"=> $pUSER_ID
				)
			);
	}
	
	public function getCargoPerfilOfertaReclutamiento( $pROCP_ID ){

			return $this->procedure("CALL RECLUTAMIENTO_CARGO_PERFIL_OFERTA( :pROCP_ID )",
					array(
							":pROCP_ID"=>$pROCP_ID
				)
			);
	}
	
	public function getSubEtapaReclutamiento( $pROCP_ID, $pPOST_ID, $pRECETATIP_ID ){

			return $this->procedure("CALL RECLUTAMIENTO_LISTA_SUBETAPA( :pROCP_ID, :pPOST_ID, :pRECETATIP_ID )",
					array(
							":pROCP_ID"=>$pROCP_ID,
							":pPOST_ID"=>$pPOST_ID,
							":pRECETATIP_ID"=>$pRECETATIP_ID
				)
			);
	}
		
	public function getSubEtapaEtapaReclutamiento( $pROCP_ID, $pPOST_ID, $pROCPETA_ID){

			return $this->procedure("CALL RECLUTAMIENTO_LISTA_SUBETAPA_ETAPA( :pROCP_ID, :pPOST_ID, :pROCPETA_ID )",
					array(
							":pROCP_ID"=>$pROCP_ID,
							":pPOST_ID"=>$pPOST_ID,
							":pROCPETA_ID"=>$pROCPETA_ID
				)
			);
	}
	
	public function getPostulacionesReclutamiento( $pPOST_ID, $pROCP_ID = null ){

			return $this->procedure("CALL POSTULANTE_LISTA_POSTULACIONES( :pPOST_ID, :pROCP_ID )",
					array(
							":pPOST_ID"=>$pPOST_ID,
							":pROCP_ID"=>$pROCP_ID,
				)
			);
	}
	
	public function getUltimaPostulacionReclutamiento( $pPOST_ID ){

			return $this->procedure("CALL POSTULANTE_ULTIMA_POSTULACION( :pPOST_ID )",
					array(
							":pPOST_ID"=>$pPOST_ID,
				)
			);
	}
	
	public function getUltimoExamenCovidPostulante( $pPOST_ID ){

			return $this->procedure("CALL POSTULANTE_COVID( :pPOST_ID )",
					array(
							":pPOST_ID"=>$pPOST_ID,
				)
			);
	}

	public function getDocumentosPerfilOferta( $id, $pPOST_ID = null  )
	{
		$r = $this->procedure('CALL RECLUTAMIENTO_DOCUMENTO_OFE_CARGO_PERFIL( :pROCP_ID, :pPOST_ID )',
				array(
						':pROCP_ID' => $id,
						':pPOST_ID' => $pPOST_ID 
				)
		);
		
		return ( is_array($r) && !empty( $r ) ) ? $r : false ;

	}


	
	public function registrarProgramacionTurno( $pTURNO, $pFECHA, $pUSER_ID )
  {
    $r =  $this->procedure("CALL PROGRAMACION_TURNO_GUARDAR( :pTURNO , :pFECHA , :pUSER_ID )",
				$ar = array(
						':pTURNO' => $pTURNO,
						':pFECHA' => $pFECHA,
						':pUSER_ID' => $pUSER_ID 
				)
    );
		
		//var_dump($ar, $r);
		return ( is_array($r) && isset($r[0]['ID']) ) ? $r[0]['ID'] : false ; 
	
	}
 
	public function registrarProgramacionTurnoDetalle( 
			$pPT_ID, $pRUT, $pDV, $pNOMBRE, $pCARGO, $pTURNO, 
			$pTELEFONO, $pCOMUNA, $pESTADO_DES ,$pDESDE_PLANILLA, $user_id=null )
  {
    $r =  $this->procedure("CALL PROGRAMACION_TURNO_GUARDAR_DETALLE( :pPT_ID, :pRUT, :pDV, :pNOMBRE, :pCARGO, :pTURNO, :pTELEFONO, :pCOMUNA, :pESTADO_DES, :pPLANILLA ,:pUSER_ID )",
				$ar =array(
						':pPT_ID' => $pPT_ID,
						':pRUT' => $pRUT,
						':pDV' => $pDV, 
						':pNOMBRE' => $pNOMBRE, 
						':pCARGO' => $pCARGO, 
						':pTURNO' => $pTURNO, 
						':pTELEFONO' => $pTELEFONO, 
						':pCOMUNA' => $pCOMUNA, 
						':pESTADO_DES' => $pESTADO_DES, 
						':pPLANILLA' => $pDESDE_PLANILLA,
						':pUSER_ID' => $user_id, 
				)
		);
		//if($pRUT == '13317527')
		//		var_dump($ar);
		return $r;
	}
  
	public function getProgramacionTurnoSinValidar()
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_SIN_VALIDAR()",
				array()
		);
	}
  
	public function getProgramacionTurnoDetalle( $pPT_ID )
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_DETALLE( :pPT_ID )",
				array(':pPT_ID' => $pPT_ID )
		);
	}
 
	public function actualizarProgramacionTurnoDetalle($pPTD_ID , $pCONTRATO, $pEXAMEN, $pENCUESTA, $pFECHA_VENC_INDUCCION, $pFECHA_VENC_ALTURA_GEO, $pUSER_ID)
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_ACTUALIZAR_DETALLE(:pPTD_ID , :pCONTRATO, :pEXAMEN, :pENCUESTA, :pFECHA_VENC_INDUCCION, :pFECHA_VENC_ALTURA_GEO, :pUSER_ID)",
				array(
						':pPTD_ID' => $pPTD_ID , 
						':pCONTRATO' => $pCONTRATO, 
						':pEXAMEN' => $pEXAMEN, 
						':pENCUESTA' => $pENCUESTA, 
						':pFECHA_VENC_INDUCCION' => $pFECHA_VENC_INDUCCION, 
						':pFECHA_VENC_ALTURA_GEO' => $pFECHA_VENC_ALTURA_GEO, 
						':pUSER_ID' => $pUSER_ID
				)
		);
	}
   
	public function getProgramacionTurnoPorEstado( $por_vencer= null)
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_LISTA( :pPOR_VENCER )",
				array( ':pPOR_VENCER' => $por_vencer	)
		);
	}
  
	public function getProgramacionTurnoTransportes()
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_LISTA_PATENTE()",
				array()
		);
	}
   
	public function getProgramacionTurnoTransportesSinVencer( $fecha=null)
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_LISTA_PATENTE_SIN_VENCER(:pfecha)",
				array(':pfecha'=>$fecha)
		);
	}

	public function getProgramacionTurnoTransporteDetalle( $pPTT_ID , $pPATENTE )
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_PATENTE_PASAJEROS( :pPTT_ID, :pPATENTE )",
				array(
						':pPTT_ID' => $pPTT_ID,
						':pPATENTE' => $pPATENTE
				)
		);
	}
  
	public function getProgramacionTurnoEstados()
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_ESTADO()",
				array()
		);
	}
  
	public function actualizarProgramacionTurnoDetalleEstado( $pPTD_ID , $pPTE_ID , $user_id=null )
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_DETALLE_ACTUALIZAR_ESTADO( :pPTD_ID, :pPTE_ID, :pUSER_ID )",
				array(
						':pPTD_ID' => $pPTD_ID,
						':pPTE_ID' => $pPTE_ID,
						':pUSER_ID' => $user_id
				)
		);
	}
  
	public function getProgramacionTurnoDetalleNoAbordo( $pPT_ID )
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_LISTA_PERSONAS_NO_A_ABORDO( :pPT_ID)",
				array( ':pPT_ID' => $pPT_ID )
		);
	}
  

	public function actualizarProgramacionTurnoTransporteInformeEnviado( $pPTT_ID )
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_CORREO_INFORME_ENVIADO( :pPTT_ID )",
				array( ':pPTT_ID' => $pPTT_ID )
		);
	}


	public function actualizarReclutamientoOfertaDocumentoEstado(  $pRDOCOFE_ID, $pPOST_ID,  $pVALIDADO  )
  {
		return  $this->procedure("CALL POSTULANTE_DOCUMENTO_ACTUALIZAR_ESTADO(  :pRDOCOFE_ID, :pPOST_ID,  :pVALIDADO)",
				array( 
								':pRDOCOFE_ID' => $pRDOCOFE_ID,
								':pPOST_ID'=> $pPOST_ID,
								':pVALIDADO' => $pVALIDADO)
		);
	}

	public function eliminarProgramacionTurnoDetalle(  $pPTD_ID  )
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_ELIMINAR_DETALLE( :pPTD_ID )",
				array( 
						':pPTD_ID' => $pPTD_ID
				)
		);
	}


	public function getProgramacionTurnoRutFecha(  $pRUT, $pTURNO, $pFECHA  )
  {
		return  $this->procedure("CALL PROGRAMACION_TURNO_RUT_FECHA(  :pRUT, :pTURNO, :pFECHA )",
				array( 
						':pRUT' => $pRUT,
						':pTURNO' => $pTURNO,
						':pFECHA' => $pFECHA,
				)
		);
	}

	public function getPostulacionCorreosInteresados(  $pROCP_ID )
  {
		return  $this->procedure("CALL POSTULANTE_CORREOS_NUEVA_POSTULACION( :pROCP_ID )",
				array( 
						':pROCP_ID' => $pROCP_ID,
				)
		);
	}
/*
	public function getCantidadDocumentosInvalidos(  $pPOST_ID, $pROCP_ID  )
  {
		return  $this->procedure("CALL POSTULANTE_CANTIDAD_DOCUMENTOS_INVALIDOS( :pPOST_ID, :pROCP_ID )",
				array( 
						':pPOST_ID' => $pPOST_ID,
						':pROCP_ID' => $pROCP_ID
				)
		);
	}
 */


}


 ?>
