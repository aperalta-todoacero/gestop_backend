<?php 

/**

 *

 */



require_once 'Login.php';

require_once 'Usuario.php';

require_once 'Examen.php';

require_once 'CasoParticular.php';



use \Firebase\JWT\JWT;



class SSOMA extends Usuario implements iLogin

{

  private $examenes;

  private $casos_particulares;

  private $error;


  protected function __construct()

  {

    

    parent::__construct();



    $this->examenes = array();

    $this->casos_particulares = array();



  }



  public static function LoginPass($usr, $pass)

  {



    $instancia = new Self();

    

    if( $rut = $instancia->datos->validaLogin( $usr, $pass ) ){

      $instancia->setRut($rut);

      $instancia->setUsuario( $usr );

      return $instancia;

    }else{

      $instancia->error ="Usuario y password incorrectos";

      return false;

    }

  }



  public static function LoginToken($token)

  {

    $key = date('dmY');

    try{

      $decoded = JWT::decode( $token, $key, array('HS256'));

      //validar con BD

      if( isset( $decoded->rut ) && isset( $decoded->exp ) && $decoded->exp > time() ){

        $instancia = new Self();



        if( !$instancia->usuario_id = $instancia->datos->validaToken( $decoded->sub, $token ) )

          return false;



        $instancia->setRut( $decoded->rut );

        $instancia->setUsuario( $decoded->sub );

        $instancia->setExpiracion( $decoded->exp );

        return $instancia;        

      }else{

        return false;

      }

    }catch(Exception $e){

      return false;

    }

  }



  

  public function getError()  { return $this->error;  }



  public function agregarExamen( $examen )

  {

    if ($examen instanceof Examen) {

      array_push( $this->examenes, $examen);

      return true;

    }else {

      return false;

    }

  }



  public function getUltimosExamenesCovid()

  {

    

    return array_map(

      function($f){

        return array(

				  'id' => $f['PERS_ID'],

          'rut' => $f['PERS_RUT'],

          'nombre' => $f['PERS_NOMBRE'],

          'email' => $f['PERS_EMAIL'],

          'celular' => $f['PERS_CELULAR'],

          'faena' => $f['FA_ID'],

          'turno' => $f['EXAM_TURNO'],

          'examen' => $f['EXAM_ID'],

          'tipo' => $f['TIPEXAM_ID'],

          'lugar' => $f['EXAM_LUGAR'],

          'laboratorio' => $f['EXAM_LABORATORIO'],

          'fecha' => $f['EXAM_FECHA'],

          'hora' => $f['EXAM_HORA'],

          'dias' => $f['DIFERENCIA_DIAS'],

          'estado'=>$f['EXAMRESULT_ID'],

          'observacion'=>$f['EXAM_OBSERVACION']

        );

      }

      ,$this->datos->getUltimosExamenesCovid()

    );



  }



  public function getExamenesCovid($id=null) //se cambia el rut por el pers_id

  {


    if (empty($id)) {

      return array_map(

        function($f){

          return array(

            'pers_id' => $f['PERS_ID'],

            'rut' => $f['PERS_RUT'],

            'nombre' => $f['PERS_NOMBRE'],

            'email' => $f['PERS_EMAIL'],

            'celular' => $f['PERS_CELULAR'],

            'faena' => $f['FA_ID'],

            'faena_nombre' => $f['FA_NOMBRE'],

            'examen' => $f['EXAM_ID'],

            'tipo' => $f['TIPEXAM_ID'],

            'tipo_descripcion' => $f['TIPEXAM_TITULO'],

            'fecha' => $f['EXAM_FECHA'],

            'hora' => $f['EXAM_HORA'],

            'lugar' => $f['EXAM_LUGAR'],

            'turno' => $f['EXAM_TURNO'],

            'laboratorio' => $f['EXAM_LABORATORIO'],

            'observacion'=>$f['EXAM_OBSERVACION'],

            'resultado'=>$f['RESULTADO_DESCRIPCION'],

          );

        }

        ,$this->datos->getExamenesCovid( null )

      );

    }else{



      return array_map(

        function($f){

          return array(

            'pers_id' => $f['PERS_ID'],

            'rut' => $f['PERS_RUT'],

            'nombre' => $f['PERS_NOMBRE'],

            'email' => $f['PERS_EMAIL'],

            'celular' => $f['PERS_CELULAR'],

            'faena' => $f['FA_ID'],

            'turno' => $f['EXAM_TURNO'],

            'examen' => $f['EXAM_ID'],

            'tipo' => $f['TIPEXAM_ID'],

            'lugar' => $f['EXAM_LUGAR'],

            'laboratorio' => $f['EXAM_LABORATORIO'],

            'fecha' => $f['EXAM_FECHA'],

            'hora' => $f['EXAM_HORA'],

            //'dias' => $f['DIFERENCIA_DIAS'],

            'estado'=>$f['EXAMRESULT_ID'],

            'observacion'=>$f['EXAM_OBSERVACION']

          );

        }

        ,$this->datos->getExamenesCovid($id)

      );

    }



  }



  public function registrarExamenes()

  {

    $e = array();

    $this->datos->begin();


    foreach ($this->examenes as $examen) {

      if( !$this->datos->registrarExamen(

            $examen->getRut(),

            $examen->getNombre(),

            $examen->getEmail(),

            $examen->getCelular(),

            $examen->getTipo(),

            $examen->getFaena(),

            $examen->getTurno(),

            $examen->getLaboratorio(),

            $examen->getLugar(),

            //$examen->getFecha(true),
            $examen->getFecha(),

            $examen->getHora(),

            $examen->getResultado(),

						$examen->getObservacion(),

						$this->usuario_id

      )){

        $this->datos->rollback();

        return false;

			}

    }

    $this->datos->commit();

    return true;

  }



  public function getSeguimientosActivos()

  {



    return array_map(

      function($f){

        return array(

          'id' => $f['PERS_ID'],

          'rut' => $f['PERS_RUT'],

          'nombre' => $f['PERS_NOMBRE'],

          'email' => $f['PERS_EMAIL'],

          'celular' => $f['PERS_CELULAR'],

          'seguimiento' => $f['SEG_ID'],

          'estado_contagio' => $f['ESTCONT_ID'],

          'estado_contagio_desc' => $f['ESTCONT_DESCRIPCION'],

          'seguido_hoy' => $f['SEGUIDO_HOY'],

					'origen' => $f['ORIGEN'],

					'fecha' => $f['FECHA'],

        );

      }

      ,$this->datos->getSeguimientosActivos()

    );



  }



  public function getSeguimientos($id)//antes era rut

  {



    return array_map(

      function($f){

        return array(

          'id' => $f['SEG_ID'],

          'fecha' => $f['SEG_FECHA'],

          'cerrado' => $f['SEG_CERRADO'],

          'estado_descripcion' => $f['ESTCONT_DESCRIPCION'],

        );

      }

      ,$this->datos->getSeguimientos($id)

    );



  }

    public function getEstadosContagio()

  {



    return array_map(

      function($f){

        return array(

          'id' => $f['ESTCONT_ID'],

          'nombre' => $f['ESTCONT_DESCRIPCION'],

        );

      }

      ,$this->datos->getEstadosContagio()

    );



  }



  public function getSeguimientoTipoDetalle()

  {



    return array_map(

      function($f){

        return array(

          'id' => $f['ID'],

          'descripcion' => $f['DESCRIPCION']

        );

      }

      ,$this->datos->getSeguimientoTipoDetalle()

    );



  }



  public function getSeguimientoDetalle($id)

  {



    return array_map(

      function($f){

        return array(

          'id' => $f['SEGDET_ID'],

          'tipo' => $f['SEGDETTIP_ID'],

          'observacion' => $f['SEGDET_DESCRIPCION'],

          'fecha' => $f['SEGDET_FECHA'],

          'fecha_inicio' => $f['SEGDET_FECHA_INICIO'],

          'fecha_termino' => $f['SEGDET_FECHA_TERMINO'],

          'tipo_descripcion' => $f['SEGDETTIP_DESCRIPCION'],

          'sistema' => $f['SISTEMA'],

        );

      }

      ,$this->datos->getSeguimientoDetalle($id)

    );



  }





  public function getCasosCovid()

  {



    return array_map(

      function($f){

        return array(

          'pers_id'            => $f['PERS_ID'],

          'rut'                => $f['PERS_RUT'],

          'nombre'             => $f['PERS_NOMBRE'],

          'email'              => $f['PERS_EMAIL'],

          'celular'            => $f['PERS_CELULAR'],

          'estado_contagio'             => $f['ESTCONT_ID'],

          'estado_contagio_descripcion' => $f['ESTCONT_DESCRIPCION'],

          'seguimiento'        => $f['SEG_ID'],

          'seguimiento_fecha'  => $f['SEG_FECHA'],

          'seguimiento_estado' => $f['SEG_CERRADO'],

          'fecha'              => $f['FECHA_EVENTO'],

          'cuarentena_minsal'  => $f['CUARENTENA_MINSAL'],
          
          'licencia'           => $f['LICENCIA'],

          'cuarentena_cmdic'   => $f['CUARENTENA_CMDIC']

        );

      }

      ,$this->datos->getCasosCovid()

    );



  }


  public function getDatosCovid( $encuesta_id, $fecha_ini, $fecha_fin, $faena_id =null , $estado_id=null )

  {
      

    $data = $this->datos->getDatosCovid($encuesta_id, $fecha_ini, $fecha_fin, $faena_id, $estado_id);


    $datos = array(

      'resumen'=>array(), 

      'pasaportes'=>array(),

      'examenes'=>array(),

      'casos_particulares'=>array(),

      'registros'=>array(),

      'resumen_faenas'=>array(),

      'seguimientos'=>array(),

    );



    if(isset($data[0]) && is_array($data[0]))

      $datos['resumen'] = array_map(function($f){ return array('fecha'=> $f['FECHA_STR'], 'HABILITADO'=> $f['HABILITADO'], 'SOSPECHA'=>$f['SOSPECHA'], 'CONTACTO_ESTRECHO'=>$f['CONTACTO_ESTRECHO'],'POSITIVO_COVID_19'=>$f['POSITIVO_COVID_19']  ); } , $data[0]);





    if(isset($data[1]) && is_array($data[1]))

      $datos['pasaportes'] = array_map(function($f){ return array('faena'=> $f['FA_NOMBRE'], 'HABILITADO'=> $f['HABILITADO'], 'SOSPECHA'=>$f['SOSPECHA'] ); } , $data[1]);



    if(isset($data[2]) && is_array($data[2]))

      $datos['examenes'] = array_map(function($f){ return array('fecha'=> $f['FECHA_STR'], 'HABILITADO'=> $f['HABILITADO'], 'SOSPECHA'=>$f['SOSPECHA'], 'CONTACTO_ESTRECHO'=>$f['CONTACTO_ESTRECHO'],'POSITIVO_COVID_19'=>$f['POSITIVO_COVID_19']  ); } , $data[2]);



    if(isset($data[3]) && is_array($data[3]))

      $datos['casos_particulares'] = array_map(function($f){ return array('fecha'=> $f['FECHA_STR'], 'HABILITADO'=> $f['HABILITADO'], 'SOSPECHA'=>$f['SOSPECHA'], 'CONTACTO_ESTRECHO'=>$f['CONTACTO_ESTRECHO'],'POSITIVO_COVID_19'=>$f['POSITIVO_COVID_19']  ); } , $data[3]);



    if(isset($data[4]) && is_array($data[4]))

      $datos['resumen_faenas'] = array_map(function($f){ return array('fa_id'=> $f['FA_ID'], 'fa_nombre'=> $f['FA_NOMBRE'], 'estado'=>$f['ESTCONT_DESCRIPCION'], 'total'=>$f['TOTAL'],'porcentaje'=>$f['PORCENTAJE_FAENA']  ); } , $data[4]);



    if(isset($data[6]) && is_array($data[6]))

      $datos['seguimientos_total'] = array('no_seguido'=> $data[6][0]['NO_SEGUIDO'], 'abierto'=> $data[6][0]['ABIERTO'], 'cerrado'=>$data[6][0]['CERRADO']  ) ;



    if(isset($data[7]) && is_array($data[7]))

      $datos['examenes_total'] = array(

        'espera'=> $data[7][0]['ESPERA'], 

        'negativo'=> $data[7][0]['NEGATIVO'], 

        'positivo'=>$data[7][0]['POSITIVO'],

        'indeterminado'=>$data[7][0]['INDETERMINADO'],

      ) ;



    if(isset($data[8]) && is_array($data[8]))

      $datos['casos_total'] = array(

        'habilitado'=> $data[8][0]['HABILITADO'],

        'sospecha'=> $data[8][0]['SOSPECHA'], 

        'contacto_estrecho'=>$data[8][0]['CONTACTO_ESTRECHO'],

        'positivo_covid_19'=>$data[8][0]['POSITIVO_COVID_19'],

      ) ;

//var_dump($data[9]);

    if(isset($data[9]) && is_array($data[9]))

      $datos['examenes_tipo'] = array_map(function( $f ){

        return array(

                'cantidad'=> $f['CANTIDAD'],

                'titulo'=> $f['TIPEXAM_TITULO'],

              ) ;

      }, $data[9]);



    if(isset($data[10]) && is_array($data[10]))

      $datos['cuarentenas'] = array_map(function( $f ){

        return array(

                'cantidad'=> $f['CANTIDAD'],

                'tipo'=> $f['TIPO'],

              ) ;

      }, $data[10]);




    if(isset($data[5]) && is_array($data[5]))

      $datos['registros'] = array_map(function($f){ 

        return array(

          'pers_id'=> $f['PERS_ID'], 

          'rut'=> $f['PERS_RUT'], 

          'nombre'=>$f['PERS_NOMBRE'], 

          'celular'=>$f['PERS_CELULAR'],

          'email'=>$f['PERS_EMAIL'], 

          'fecha'=>$f['FECHA_EVENTO'], 

          'faena_id'=>$f['FA_ID'], 

          'faena_nombre'=>$f['FA_NOMBRE'], 

          'estado'=>$f['ESTCONT_ID'],

          'estado_descripcion'=>$f['ESTCONT_DESCRIPCION'],

          'origen'=>$f['ORIGEN'],

          //'fecha_evento'=>$f['FECHA_EVENTO'],

           ); } 

        , $data[5]);



    return $datos;

  }



  public function getCasosParticulares( $rut = null )

  {


    return array_map(

      function($f){

        return array(

          'id'            => $f['CP_ID'],

          'pers_id'            => $f['PERS_ID'],

          'rut'                => $f['PERS_RUT'],

          'nombre'             => $f['PERS_NOMBRE'],

          'email'              => $f['PERS_EMAIL'],

          'celular'            => $f['PERS_CELULAR'],

          'estado_contagio'    => $f['ESTCONT_ID'],

          'estado_contagio_descripcion' => $f['ESTCONT_DESCRIPCION'],

          'fecha'       => $f['FECHA'],

          'observacion' => $f['CP_OBSERVACION'],

          'faena' => $f['FA_ID'],

          'faena_descripcion' => $f['FA_NOMBRE'],

        );

      }

      ,$this->datos->getCasosParticulares($rut)

    );



  }



  public function registrarSeguimientoObservacion($pers_id, $id = null, $estado_contagio, $cerrar=0, $tipo_obs, $fecha_obs, $descripcion_obs ='', $fecha_inicial_obs =null, $fecha_final_obs = null    )

  {

    

    $id = empty($id) ? null : $id ;



    if( !is_numeric($pers_id) || empty($pers_id) || !is_numeric($estado_contagio) )

      return false;



    if($seg_id = $this->datos->registrarSeguimiento( $id, $estado_contagio, $pers_id, $cerrar , $this->usuario_id ) ){

      if( !$this->datos->registrarSeguimientoDetalle( $seg_id, $tipo_obs, $fecha_obs, $descripcion_obs, $fecha_inicial_obs, $fecha_final_obs , $this->usuario_id ) )

        return false;

    }else{

      return false;

    }



    return is_numeric($id) ? $id : $seg_id  ;

  }



  public function cerrarSeguimiento($id = null)

  {

    



    if( !is_numeric($id) || empty($id) || !isset($id) )

      return false;



    $seg_id = $this->datos->registrarSeguimiento( $id, null, null, $cerrar=1, $this->usuario_id );



    return (is_numeric($seg_id) && $seg_id!=0 ) ? true : false  ;

  }



  public function agregarCasoParticular( $Caso )

  {

    if($Caso instanceof CasoParticular){

      array_push( $this->casos_particulares, $Caso );

      return true;

    }else{

      return false;

    }



  }



  public function registrarCasosParticulares( )

  {    

    $this->datos->begin();

    foreach ($this->casos_particulares as &$Caso) {



      $id = null;

      

      if( $id = $this->datos->registrarCasoCovid( 

          $Caso->getRut(), 

          $Caso->getNombre(), 

          $Caso->getFaenaId(), 

          $Caso->getEstadoContagio(), 

          $Caso->getFechaSintoma(), 

          $Caso->getFechaConocimiento() , 

					$Caso->getObservacion() ,

					$this->usuario_id
			) ){



        $Caso->setId( $id );



        //$this->datos->rollback();

        //return $Caso;

        

        $contactos_estrechos = $Caso->getContactosEstrechos();



        if( count($contactos_estrechos) ){

          foreach ($contactos_estrechos as $cce) {

            if( !$this->datos->registrarCasoContactoEstrecho( 

              $cce->getRut(), 

              $cce->getNombre(),

              $cce->getFecha(), 

              $pas_id=null, 

              $exam_id=null, 

              $Caso->getId() )){



              $this->error = "Error al registrar el contacto estrecho";

              $this->datos->rollback();

              return false;

            }

          }



        }



      }else{

        $this->error = "Error al registrar el caso";

        $this->datos->rollback();

        return false;

      }

      $this->datos->commit();



      return true;

    }



  }




  public function registrarCCE_CasoParticular($cp_id = null)

  {

    

    foreach ($this->casos_particulares as &$Caso) {

      if( !empty($cp_id) && is_numeric($cp_id) ){

        return true;

      }else{

      }

    }


    return true;

  }

	public function eliminarExamen($exam_id){
		
			if(! $this->usuario_id){
					$this->error = "identificador de usuario no válido";
					return false;
			}

			if( $this->datos->eliminarExamen( $exam_id , $this->usuario_id ) ){

					$this->error = "";

					return true;
						
			}else{

					$this->error = "El examen no pudo ser eliminado";

					return false;
							
			}

	}


}

 ?>

