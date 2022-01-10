<?php

header('Access-Control-Allow-Origin: *');

header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

header('content-type: application/json; charset=utf-8');

ini_set('display_errors', 'On');

error_reporting(E_ALL);



use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;



require 'Mailer/src/Exception.php';

require 'Mailer/src/PHPMailer.php';

require 'Mailer/src/SMTP.php';



require_once('../clases/Usuario.php');

require_once('../clases/Encuesta.php');

require_once('../clases/Empresa.php');

require_once('../clases/PasaporteCovid19.php');



$resp = array('error'=>false, 'preguntas'=> array() );


try{


  $post = '';

  $op = '';


  if( isset($_POST['opcion']) ){

    $op = $_POST['opcion'];

    $post = $_POST;

  }else{

    $JSONpost = file_get_contents("php://input");


    $post = json_decode($JSONpost);


    if( isset( $post->params ) ) {

        $op = (isset($post->params->opcion)) ? $post->params->opcion : '';

        $post = $post->params;          

    }else {

      throw new Exception('opción no identificada');

    }

  }



  $encta_id = 1;




  switch($op){

    

    case 'ini':



      if(!isset($encta_id))

        throw new Exception("Id encuesta no recibida", 1);



      $resp['preguntas'] = array();



      $Empresa = new Empresa();



      $resp['faenas'] = array_map(function($f){ return array('id'=>$f->getId(), 'nombre'=>$f->getNombre() );}, $Empresa->getFaenas() );



      $Encuesta = new Encuesta( $encta_id );



      $resp['preguntas'] = array_map(function($pregunta){

        return array(

          'id'=>$pregunta->getId(),

          'tipo'=>$pregunta->getTipo(),

          'descripcion'=> ($pregunta->getDescripcion()),

          'alternativas' => $pregunta->getAlternativas()

        );

      },  $Encuesta->getPreguntas() );
$resp['encta_id'] = $encta_id; 

    break;



    case 'graficos_covid':



      //$rut = 15924693;

      require_once('../clases/SSOMA.php');

      require_once('../clases/Empresa.php');

      

      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);

        

      



      $Empresa = new Empresa();


      $resp = array(
            'error' => false,
            'faenas' => array(),
            'fechas' => array(),
            'seguimientos' => array(
                'estados' => array('Sin seguimiento', 'Abierto', 'Cerrado'),
                'data' =>array( )
            ),
            'examenes_total' => array(
                'estados' => array('Negativo', 'Espera', 'Indeterminado', 'Positivo'),        
                'data' =>array()
            ),
            
            'casos_total' => array(
                'estados' => array('Sospecha', 'Contacto estrecho', 'Positivo COVID 19'),        
                'data' =>array()
            ),
            
            'examenes_tipo' => array(
                'estados' =>array(),
                'data' =>array()
            ),
            
            'cuarentenas' => array( 
                'tipos' =>array(),
                'data' =>array()

            ),
            
            'resumen' => array(
                array(
                  'estado'=>'HABILITADO',
                  'data' => array()
                ),
                array(
                  'estado' =>'SOSPECHA',
                  'data' => array()
                ),
                array(
                  'estado' =>'CONTACTO ESTRECHO',
                  'data' => array()
                ),
                array(
        
                  'estado' =>'POSITIVO COVID 19',
                  'data' => array()
                ),    
            ),
            
            'examenes_fechas' => array(),
            
            'examenes' => array(
                array(
                  'estado'=>'HABILITADO',
                  'data' => array()
                ),
                array(
                  'estado' =>'SOSPECHA',
                  'data' => array()
                ),
                array(
                  'estado' =>'CONTACTO ESTRECHO',
                  'data' => array()
                ),
                array(
                  'estado' =>'POSITIVO COVID 19',
                  'data' => array()
                ),
            ),
            
            'pasaportes_faenas' => array(),
            
            'pasaportes' => array(
                array(
                    'estado'=>'HABILITADO',
                    'data' => array()
                ),
                array(
                  'estado' =>'SOSPECHA',
                  'data' => array()
                ),
            ),
            
            'casos_fechas' => array(),
            
            'casos' => array(
                array(
                  'estado'=>'HABILITADO',
                  'data' => array()
                ),
                array(
                  'estado' =>'SOSPECHA',
                  'data' => array()
                ),
        
                array(
                  'estado' =>'CONTACTO ESTRECHO',
                  'data' => array()
                ),
                array(
                  'estado' =>'POSITIVO COVID 19',
                  'data' => array()
                ),
            ),
            'resumen_faenas' => array(),
            'registros' => array(),
            'estados' => array()
        );
          
          
      $resp['faenas'] = array_map(function($f){ return array('id'=>$f->getId(), 'nombre'=>$f->getNombre() );}, $Empresa->getFaenas() );

          

      list($ano,$mes,$dia) = explode('-', $post->fecha_ini );

      list($ano2,$mes2,$dia2) = explode('-', $post->fecha_fin);



      if( mktime(0, 0, 0, $mes, $dia, $ano) < mktime(0, 0, 0, $mes2, $dia2, $ano2) ) {

        $pFECHA_INI = $post->fecha_ini;

        $pFECHA_FIN = $post->fecha_fin;

      }else{

        $pFECHA_INI = $post->fecha_fin;

        $pFECHA_FIN = $post->fecha_ini;

      }



      $pFAENA = (isset($post->faena) && count($post->faena)>0) ? implode(',', $post->faena) : null ;

      $pESTADO = (isset($post->estado) && count($post->estado)>0) ? implode(',', $post->estado) : null ;

      $datos = $SSOMA->getDatosCovid( $pENCTA_ID=1, $pFECHA_INI, $pFECHA_FIN, $pFAENA, $pESTADO );



      if( is_array($datos['resumen']) && !empty( $datos['resumen'] ) )
        $resp['fechas'] = array_column( $datos['resumen'], 'fecha');      


    if( is_array($datos['seguimientos_total']) && !empty( $datos['seguimientos_total'] ) )
      $resp['seguimientos'] = array(

        'estados' => array('Sin seguimiento', 'Abierto', 'Cerrado'),        

        'data' =>array( 

          $datos['seguimientos_total']['no_seguido'], 

          $datos['seguimientos_total']['abierto'], 

          $datos['seguimientos_total']['cerrado']

        )

      ); 


    if( is_array($datos['examenes_total']) && !empty( $datos['examenes_total'] ) )
      $resp['examenes_total'] = array(

        'estados' => array('Negativo', 'Espera', 'Indeterminado', 'Positivo'),        

        'data' =>array( 

          $datos['examenes_total']['negativo'], 

          $datos['examenes_total']['espera'], 

          $datos['examenes_total']['indeterminado'], 

          $datos['examenes_total']['positivo'] 

        )

      ); 


if( is_array($datos['casos_total']) && !empty( $datos['casos_total'] ) )
      $resp['casos_total'] = array(

        'estados' => array('Sospecha', 'Contacto estrecho', 'Positivo COVID 19'),        

        'data' =>array( 

          $datos['casos_total']['sospecha'], 

          $datos['casos_total']['contacto_estrecho'], 

          $datos['casos_total']['positivo_covid_19'] )

        ); 



      //$resp['examenes_tipo'] = $datos['examenes_tipo'];


if( is_array($datos['examenes_tipo']) && !empty( $datos['examenes_tipo'] ) )
      $resp['examenes_tipo'] = array(

        'estados' =>array_column($datos['examenes_tipo'], 'titulo'),

        'data' =>array_column($datos['examenes_tipo'], 'cantidad')

        );


if( is_array($datos['cuarentenas']) && !empty( $datos['cuarentenas'] ) )
      $resp['cuarentenas'] = array(

        'tipos' =>array_column($datos['cuarentenas'], 'tipo'),

        'data' =>array_column($datos['cuarentenas'], 'cantidad')

        );



      

      //$resp['resumen'] = $datos['resumen'];
if( is_array($datos['resumen']) && !empty( $datos['resumen'] ) )
      $resp['resumen'] = array(

        array(

          'estado'=>'HABILITADO',

          'data' => array_column($datos['resumen'], 'HABILITADO')

        ),

        array(

          'estado' =>'SOSPECHA',

          'data' => array_column($datos['resumen'], 'SOSPECHA')

        ),

        array(

          'estado' =>'CONTACTO ESTRECHO',

          'data' => array_column($datos['resumen'], 'CONTACTO_ESTRECHO')

        ),

        array(

          'estado' =>'POSITIVO COVID 19',

          'data' => array_column($datos['resumen'], 'POSITIVO_COVID_19')

        ),

      );


if( is_array($datos['examenes']) && !empty( $datos['examenes'] ) )
      $resp['examenes_fechas'] = array_column($datos['examenes'], 'fecha');


if( is_array($datos['examenes']) && !empty( $datos['examenes'] ) )
      $resp['examenes'] = array(

        array(

          'estado'=>'HABILITADO',

          'data' => array_column($datos['examenes'], 'HABILITADO')

        ),

        array(

          'estado' =>'SOSPECHA',

          'data' => array_column($datos['examenes'], 'SOSPECHA')

        ),

        array(

          'estado' =>'CONTACTO ESTRECHO',

          'data' => array_column($datos['examenes'], 'CONTACTO_ESTRECHO')

        ),

        array(

          'estado' =>'POSITIVO COVID 19',

          'data' => array_column($datos['examenes'], 'POSITIVO_COVID_19')

        ),

      );



      //$resp['pasaportes_fechas'] = array_column($datos['pasaportes'], 'fecha');
if( is_array($datos['pasaportes']) && !empty( $datos['pasaportes'] ) )
      $resp['pasaportes_faenas'] = array_column($datos['pasaportes'], 'faena');


if( is_array($datos['pasaportes']) && !empty( $datos['pasaportes'] ) )
      $resp['pasaportes'] = array(

        array(

          'estado'=>'HABILITADO',

          'data' => array_column($datos['pasaportes'], 'HABILITADO')

        ),

        array(

          'estado' =>'SOSPECHA',

          'data' => array_column($datos['pasaportes'], 'SOSPECHA')

        ),

        /*array(

          'estado' =>'CONTACTO ESTRECHO',

          'data' => array_column($datos['pasaportes'], 'CONTACTO_ESTRECHO')

        ),

        array(

          'estado' =>'POSITIVO COVID 19',

          'data' => array_column($datos['pasaportes'], 'POSITIVO_COVID_19')

        ),*/

      );


if( is_array($datos['casos_particulares']) && !empty( $datos['casos_particulares'] ) )
      $resp['casos_fechas'] = array_column($datos['casos_particulares'], 'fecha');


if( is_array($datos['casos_particulares']) && !empty( $datos['casos_particulares'] ) )
      $resp['casos'] = array(

        array(

          'estado'=>'HABILITADO',

          'data' => array_column($datos['casos_particulares'], 'HABILITADO')

        ),

        array(

          'estado' =>'SOSPECHA',

          'data' => array_column($datos['casos_particulares'], 'SOSPECHA')

        ),

        array(

          'estado' =>'CONTACTO ESTRECHO',

          'data' => array_column($datos['casos_particulares'], 'CONTACTO_ESTRECHO')

        ),

        array(

          'estado' =>'POSITIVO COVID 19',

          'data' => array_column($datos['casos_particulares'], 'POSITIVO_COVID_19')

        ),

      );



//if( is_array($datos['registros']) && !empty( $datos['regitros'] ) )
      $resp['registros'] = $datos['registros'];


if( is_array($datos['resumen_faenas']) && !empty( $datos['resumen_faenas'] ) )
      $resp['resumen_faenas'] = $datos['resumen_faenas'];



      $resp['estados'] = $SSOMA->getEstadosContagio();

      

    break;

    

    case 'ini_dinamico':



      $rut = 15924693;

      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      $Empresa = new Empresa();

      

      //$SSOMA = new SSOMA($rut);





      if(isset($post->faenas) && $post->faenas === true)

        $resp['faenas'] = array_map(function($f){ return array('id'=>$f->getId(), 'nombre'=>$f->getNombre() );}, $Empresa->getFaenas() );



      if(isset($post->tipos_examen) && $post->tipos_examen === true)

        $resp['tipos_examen'] = $SSOMA->getTiposExamen();



      if(isset($post->tipos_seguimiento) && $post->tipos_seguimiento === true)

        $resp['tipos_seguimiento'] = $SSOMA->getSeguimientoTipoDetalle();



      if(isset($post->estados_contagio) && $post->estados_contagio === true)

        $resp['estados_contagio'] = $SSOMA->getEstadosContagio();



      if(isset($post->casos_particulares) && $post->casos_particulares === true)

        $resp['casos_particulares'] = $SSOMA->getCasosParticulares();



      if(isset($post->fecha_actual) && $post->fecha_actual === true)

        $resp['fecha_actual'] = date('Y-m-d');



      if(isset($post->examenes_registrados) && $post->examenes_registrados === true)

        $resp['examenes_registrados'] = $SSOMA->getExamenesCovid();//null trae todo



    break;



    case 'ini_ssoma':



      require_once('../clases/Examen.php');

      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);


      $Empresa = new Empresa();




      $resp['faenas'] = array_map(function($f){ return array('id'=>$f->getId(), 'nombre'=>$f->getNombre() );}, $Empresa->getFaenas() );

      $resp['tipos_examen'] = $Empresa->getTiposExamen();

      $resp['ultimos_examenes'] = $SSOMA->getUltimosExamenesCovid();



    break;



    case 'ini_seguimiento':

      //require_once('../clases/Examen.php');

      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      //$rut = 15924693;

      //$SSOMA = new SSOMA($rut);



      $resp['seguimientos_activos'] = $SSOMA->getSeguimientosActivos();

      $resp['tipos_detalle'] = $SSOMA->getSeguimientoTipoDetalle();



    break;



    case 'ini_casos_covid':

      //require_once('../clases/Examen.php');

      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      $resp['casos'] = $SSOMA->getCasosCovid();



    break;



    case 'seguimiento_rut':

      require_once('../clases/SSOMA.php');

      if( !isset($post->token) || empty( $post->token ) )
          throw new Exception("Token de acceso no encontrado", 1);


      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )
        throw new Exception("Su tiempo de acceso ha expirado", 2);



      if(!isset($post->pers_id) || !is_numeric($post->pers_id))

        throw new Exception("Identificador inválido", 1);//se cambia el rut por pers_id


      $resp['seguimientos'] = $SSOMA->getSeguimientos($post->pers_id);



      if(isset($post->seguimiento) && is_numeric($post->seguimiento))

        $resp['detalle'] = $SSOMA->getSeguimientoDetalle($post->seguimiento);

      else

        $resp['detalle'] = array();

      

    break;



    case 'seguimiento_id':



      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      if(!isset($post->id) || !is_numeric($post->id))

        throw new Exception("Id seguimiento inválido", 1);



      $resp['detalle'] = $SSOMA->getSeguimientoDetalle($post->id);



    break;

    case 'eliminar_examen':



      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);


		//pers_id
      if(!isset($post->pers_id) || !is_numeric($post->pers_id))

        throw new Exception("Identificador no válido", 1);
			
			
			if(!isset($post->id) || !is_numeric($post->id))
					
        throw new Exception("Id examen inválido", 1);


				
			if( ! $SSOMA->eliminarExamen($post->id) )
			
					throw new Exception( $SSOMA->getError() , 1 );


      $resp['examenes'] = $SSOMA->getExamenesCovid( $post->pers_id );

      $resp['ultimos_examenes'] = $SSOMA->getUltimosExamenesCovid();

			$resp['mensaje'] = "El examen ha sido eliminado";

    break;



    case 'seguimiento_detalle':



      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

        throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      if(!isset($post->id) || !is_numeric($post->id))

        throw new Exception("Id inválido", 1);



      //$rut = 15924693;

      //$SSOMA = new SSOMA($rut);



      $resp['detalle'] = $SSOMA->getSeguimientoDetalle($post->id);



    break;



    case 'examenes_rut':

      require_once('../clases/Examen.php');

      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);


      if(!isset($post->id) || !is_numeric($post->id))

        throw new Exception("Identificador inválido", 1);


      $resp['examenes'] = $SSOMA->getExamenesCovid($post->id);



    break;



    case 'cerrar_seguimiento':



      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      if(!isset($post->id) || !is_numeric($post->id))

        throw new Exception("Identificador de seguimiento no válido", 1);



      //$rut = 15924693;

      //$SSOMA = new SSOMA($rut);



      if( $SSOMA->cerrarSeguimiento($post->id) )

        $resp['msg'] = "Seguimiento cerrado con éxito";

      else

        throw new Exception("No fue posible cerrar el seguimiento", 1);



      $resp['seguimientos_activos'] = $SSOMA->getSeguimientosActivos();

      $resp['seguimientos'] = $SSOMA->getSeguimientos($post->pers_id);



    break;



    case 'guardar_seguimiento_observacion':



      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      //$rut = 15924693;



      if(!isset( $post->pers_id ) || !is_numeric($post->pers_id) )

        throw new Exception("No se ha recibido el identificador de la persona", 1);



      if(!isset( $post->tipo ) || !is_numeric($post->tipo) )

        throw new Exception("Debe escoger un tipo de observacion", 1);



      if(!isset( $post->estado_contagio ) || !is_numeric($post->estado_contagio) )

        throw new Exception("No se encontró el estado de contagio", 1);



      if(!isset( $post->fecha ) )

        throw new Exception("Debe ingresar la fecha de la observacion", 1);



      if( $post->tipo!=1 && ( !isset( $post->fecha1 ) || !isset($post->fecha2) ) )

        throw new Exception("Debe ingresar un periodo para este tipo de observación", 1);



      if(!isset( $post->observacion ) || strlen($post->observacion) < 5 )

        throw new Exception("Debe ingresar una observacion", 1);



      if($post->tipo !=1){

        list($ano,$mes,$dia) = explode('-', $post->fecha1);

        list($ano2,$mes2,$dia2) = explode('-', $post->fecha2);



        if( mktime(0, 0, 0, $mes, $dia, $ano) < mktime(0, 0, 0, $mes2, $dia2, $ano2) ) {

          $fecha1 = $post->fecha1;

          $fecha2 = $post->fecha2;

        }else{

          $fecha1 = $post->fecha2;

          $fecha2 = $post->fecha1;

        }

      }else{

        $fecha1 = null;

        $fecha2 = null;

      }





      //$SSOMA = new SSOMA( $rut );

      $pers_id =$post->pers_id;

      $id = (empty($post->seg_id ) || !is_numeric($post->seg_id)) ? null : $post->seg_id;

      $estado_contagio = $post->estado_contagio;

      $cerrar = (!isset($post->cerrar) || empty($post->cerrar) || !isnumeric($post->cerrar) ) ? 0 : $post->cerrar ;

      $tipo_obs =$post->tipo;

      $fecha_obs = $post->fecha;

      $descripcion_obs = $post->observacion;

      $fecha_inicial_obs = $fecha1;

      $fecha_final_obs = $fecha2;



      if( $nid =$SSOMA->registrarSeguimientoObservacion($pers_id, $id, $estado_contagio, $cerrar, $tipo_obs, $fecha_obs, $descripcion_obs , $fecha_inicial_obs, $fecha_final_obs ) )

        $resp['msg'] = "observacion registrada";

      else

        throw new Exception("Error al tratar de registrar el seguimiento", 1);



      $resp['seg_id'] = $nid;

      $resp['detalle'] = $SSOMA->getSeguimientoDetalle($nid);

      $resp['seguimientos'] = $SSOMA->getSeguimientos($pers_id);

      $resp['seguimientos_activos'] = $SSOMA->getSeguimientosActivos();



    break;



    case 'guardar_caso':



      require_once('../clases/SSOMA.php');

      require_once('../clases/CasoParticular.php');

      require_once('../clases/ContactoEstrecho.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      //$rut = 15924693;

      //$SSOMA = new SSOMA( $rut );



      if(!isset( $post->rut ) || !is_numeric($post->rut) )

        throw new Exception("Rut vacío o inválido,", 1);



      if(!isset( $post->nombre ) || !is_string($post->nombre) )

        throw new Exception("Debe ingresar el nombre del afectado", 1);



      if(!isset( $post->estado ) || !is_numeric($post->estado) )

        throw new Exception("No se encontró el estado de contagio", 1);



      if(!isset( $post->fecha_sintoma ) || $post->fecha_sintoma=='' || empty($post->fecha_sintoma) )

        throw new Exception("Debe ingresar la fecha del primer sintoma", 1);



      if(!isset( $post->fecha_conocimiento ) || empty($post->fecha_conocimiento) || $post->fecha_conocimiento=='')

        throw new Exception("Debe ingresar la fecha en que tomó conocimiento del caso", 1);


      if(!isset( $post->faena ) || !is_numeric($post->faena) )

        throw new Exception("Debe ingresar la faena", 1);


      if(!isset( $post->observacion ) || strlen($post->observacion) < 5 )

        throw new Exception("Debe ingresar una observacion válida", 1);





      $rut = $post->rut;

      $nombre = $post->nombre;

      $faena = $post->faena;

      $estado_contagio = $post->estado;

      $fecha_sintoma = $post->fecha_sintoma;

      $fecha_conocimiento = $post->fecha_conocimiento;

      $observacion = $post->observacion;



      $contactos_estrecho = $post->contactos_estrecho;



      //$SSOMA = new SSOMA( $rut );



      $Caso = new CasoParticular();



      $Caso->setRut($rut);

      $Caso->setNombre($nombre);

      $Caso->setFaenaId($faena);

      $Caso->setEstadoContagio($estado_contagio);

      $Caso->setFechaSintoma($fecha_sintoma);

      $Caso->setFechaConocimiento($fecha_conocimiento);

      $Caso->setObservacion($observacion);



      if(is_array($contactos_estrecho) && count( $contactos_estrecho )){

        foreach ($contactos_estrecho as $contacto) {

          if( is_numeric($contacto->rut) && is_string( $contacto->nombre )){

            $cce = new ContactoEstrecho();

            $cce->setRut( $contacto->rut );

            $cce->setNombre( $contacto->nombre );

            $Caso->agregarContactoestrecho($cce);

          }

        }

      }



      $SSOMA->agregarCasoParticular( $Caso );



      if( !$SSOMA->registrarCasosParticulares())

        throw new Exception( $SSOMA->getError() , 1);



      $resp['casos_particulares'] = $SSOMA->getCasosParticulares();



      $resp['msg'] = 'Caso registrado con éxito';

        

      

    break;



    case 'buscar_datos_personales':



      if(!isset($post->rut) || !is_numeric($post->rut))

        throw new Exception("Rut inválido", 1);



      $Usuario = Usuario::LoginInvitado($post->rut);

      $Usuario->setDatosDesdeEncuestas();



      $resp['nombre']= $Usuario->getNombre();

      $resp['email']= $Usuario->getEmail();

      $resp['celular']= $Usuario->getCelular();

			$resp['vacuna_covid19'] = $Usuario->getEstadoVacunaCovid19();


    break;



    case 'pasaporte':


      if(!isset($encta_id))

        throw new Exception("Id encuesta no recibida", 1);

      if(!isset($post->usuario->rut) && !isset($post->usuario->nombre))

        throw new Exception("Rut obligatorio", 1);

        //$rut = 
        
        $rut=preg_replace('/[^\-\dkK]/', '', $post->usuario->rut );
        $rut = (preg_match('/^(\d+)/', $rut, $array)) ? $array[0] : $rut;
        
        
      $Usuario = Usuario::LoginInvitado( $rut );

      $Usuario->setNombre($post->usuario->nombre);

      $Usuario->setEmail($post->usuario->email);

      $Usuario->setCelular($post->usuario->celular);

      $Usuario->setFaena($post->usuario->faena, $post->usuario->faena_nombre);



      //$tipoContactoEstrecho = $post->tipoContactoEstrecho;

     // $listaContactoEstrecho = $post->listaContactoEstrecho;



      $respuestas = array_map(function($r){

        return array(

          'preg_id' => $r->id,

          'alt_id' => $r->valorCerrado,

          'valor' => $r->valorAbierto

        );

      }, $post->respuestas );



      $Encuesta = new Encuesta($encta_id);

      foreach ($post->respuestas as $r ) {

    /*    if($r->id == 8)

          $Encuesta->responderPregunta($r->id, $r->valorCerrado, $r->valorAbierto, $tipoContactoEstrecho, $listaContactoEstrecho);

        else
		*/
          $Encuesta->responderPregunta($r->id, $r->valorCerrado, $r->valorAbierto);

      }




			//$r = $Usuario->registrarEncuesta( $Encuesta );
		  
			//if( $r=='EXISTE' )
				
		
			if(  $Usuario->registrarEncuesta( $Encuesta )){



        $fecha= date('d-m-Y');

        $pdf = new PasaporteCovid19($Usuario->getRut(), $Usuario->getNombre() ,$Usuario->getFaena()->getNombre(),  $fecha ,$Usuario->getEstadoPasaporte());

        $pdfdoc = $pdf->Output('pasaportecovid19.pdf', 'S');



        $subject ="Pasaporte Covid-19";

        $body ="Este pasaporte debe ser presentado en garita al momento de ingresar a la faena correspondiente.";

        $mail = new PHPMailer(TRUE);

        $mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');

        // $mail->addAddress("jguasch@todoacero.cl", 'Julio Guasch');

        $mail->addAddress($post->usuario->email, $post->usuario->nombre);

        $mail->isHTML(true);



        $mail->Subject = $subject;

        $mail->Body = utf8_decode($body);

        //$mail->addBCC("jguasch@todoacero.cl");

        //$mail->addBCC("aperalta@todoacero.cl");

        //$mail->addBCC("rmelgarejo@todoacero.cl");

        $mail->AddStringAttachment($pdfdoc, 'pasaportecovid.pdf', 'base64', 'application/pdf');

        $mail->send();



        $resp['datos']['rut']=$Usuario->getRut();

        $resp['datos']['nombre']=$Usuario->getNombre();

        $resp['datos']['fecha']=$fecha;

        $resp['datos']['faena']=$Usuario->getFaena()->getNombre();

        $resp['datos']['estado']=$Usuario->getEstadoPasaporte();

      }else{

        throw new Exception("Error al registrar encuesta:".$Usuario->getError() , 1);

      }



    break;

case 'existe_pasaporte_hoy':


      if(!isset($encta_id))

        throw new Exception("Id encuesta no recibida", 1);

      if( !isset($post->rut) )

        throw new Exception("Rut obligatorio", 1);

        
        $rut=preg_replace('/[^\-\dkK]/', '', $post->rut );
        $rut = (preg_match('/^(\d+)/', $rut, $array)) ? $array[0] : $rut;
        
        
				$Usuario = Usuario::LoginInvitado( $rut );

				$fecha_hoy = date('Y-m-d');

				$pasaporte = $Usuario->getDatosPasaporte( $fecha_hoy );

				if( !$pasaporte )
						throw new Exception('No existe pasaporte para el dia de hoy');

				$resp['datos']= $pasaporte;


				break;

    case 'guardar_examenes':



      require_once('../clases/Examen.php');

      require_once('../clases/SSOMA.php');



      if( !isset($post->token) || empty( $post->token ) )

          throw new Exception("Token de acceso no encontrado", 1);



      if( ! $SSOMA = SSOMA::LoginToken( $post->token ) )

        throw new Exception("Su tiempo de acceso ha expirado", 2);



      if(!isset( $post->examenes ) || !is_array($post->examenes) )

        throw new Exception("No se encontraron examenes", 1);


      $examenes = $post->examenes;


      $tipos = (array)$SSOMA->getTiposExamen();

      $error = 0;


      foreach ($examenes as $ex) {

        $tipo_id = $ex->tipo;
        
        if( empty( $ex->faena) )
            throw new Exception('Debe espeficiar la faena del trabajador',1);

        $r = array_filter($tipos, function($v,$k) use($tipo_id ) {

          return $v['id'] == $tipo_id;

        }, ARRAY_FILTER_USE_BOTH);



        $r = array_pop ($r);

        $tipo_nombre = (isset($r['nombre'])) ? $r['nombre'] : '';



        $examen = new Examen( $ex->rut);

        $email = (isset($ex->email)) ? $ex->email : null;

        $celular = (isset($ex->celular)) ? $ex->celular : null;
        
        $celular = ( empty($celular) && isset($ex->telefono)) ? $ex->telefono : $celular;
        

				$estado = (isset($ex->estado)) ? $ex->estado : null;

        $examen->setInfoPersonal($ex->nombre, $email, $celular);

        $examen->setFaena( $ex->faena );

        $examen->setTurno( $ex->turno );

        $examen->setTipo($ex->tipo, $tipo_nombre );

        $examen->setFecha( $ex->fecha );

        $examen->setHora( $ex->hora );

        $examen->setResultado( $estado );

        $examen->setObservacion( $ex->observacion );

        $examen->setLaboratorio( $ex->laboratorio );

        $examen->setLugar( $ex->lugar );


        if(!$SSOMA->agregarExamen(  $examen ) )

          $error++;


      }

	//		if( $error )
	//				throw new Exception( "Se produjo error en ".$error." de los registros", 1 );


      if( $SSOMA->registrarExamenes() === true ){

        $resp['ultimos_examenes'] = $SSOMA->getUltimosExamenesCovid();

        $resp['msg'] = "Los examenes han sido registrados con exito";

      }
      else{

        throw new Exception("Error al registrar la lista de examenes " , 1);

      }



    break;



    case 'excel_examenes':

      require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

      $inputFile = $_FILES['archivo']['tmp_name'];

      $archivo = 'temp/file'.time().'.xlsx';

      if(!move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo) )

        throw new Exception("Error al guardar archivo", 1);



      $inputFileType = PHPExcel_IOFactory::identify($archivo);

      $objReader = PHPExcel_IOFactory::createReader($inputFileType);

      $objReader->setLoadAllSheets();

      $objPHPExcel = $objReader->load($archivo);

      $sheet = $objPHPExcel->getSheet(1);

      $highestRow = $sheet->getHighestRow();

      $examenes = array();

      $faenas = array();

      $cuarentenas = array();

      $resultados = array();



      for ($row = 2; $row <= $highestRow; $row++){

        $val = $sheet->getCell('A'.$row)->getValue();

        if(!empty($val)){

          $examen = array(

            'id' => $sheet->getCell('A'.$row)->getValue(),

            'descripcion' => strtolower( $sheet->getCell('B'.$row)->getValue() ),

          );

          array_push($examenes, $examen);

        }



        $val2 = $sheet->getCell('D'.$row)->getValue();

        if(!empty($val2)){

          $faena = array(

            'id' => $sheet->getCell('D'.$row)->getValue(),

            'descripcion' => $sheet->getCell('E'.$row)->getValue(),

          );

          array_push($faenas, $faena);

        }


/*
        $val3 = $sheet->getCell('G'.$row)->getValue();

        if(!empty($val3)){

          $cuarentena = array(

            'id' => $sheet->getCell('G'.$row)->getValue(),

            'descripcion' => $sheet->getCell('H'.$row)->getValue(),

          );

          array_push($cuarentenas, $cuarentena);

        }

*/

//antes j-k

        $val4 = $sheet->getCell('G'.$row)->getValue();

        if(!empty($val4)){

          $resultado = array(

            'id' => $sheet->getCell('G'.$row)->getValue(),

            'descripcion' => strtolower( $sheet->getCell('H'.$row)->getValue() ),

          );

          array_push($resultados, $resultado);

        }
        

      }

      $resp['resultados'] = $resultados;

      $sheet = $objPHPExcel->getSheet(0);

      $highestRow = $sheet->getHighestRow();

      $highestColumn = $sheet->getHighestColumn();




//			throw new Exception('filas: '.$highestRow,1);

      $resp['datos'] = array();



      for ($row = 3; $row <= $highestRow; $row++){

					$rut = $sheet->getCell('B'.$row)->getValue();
					//$rut = str_replace(',', '', str_replace('.', '', $rut) );
					$rut = preg_replace('/[^\-\dkK]/', '', $rut);

					if( !empty($rut) ) {

          $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4);



          $e = strtolower( $sheet->getCell('K'.$row)->getValue() );

          $keyE = array_search($e, array_column($examenes, 'descripcion'));

          $examen = ($keyE!==false) ? $examenes[$keyE]['id'] : null;



          $f = $sheet->getCell('I'.$row)->getValue();

          $keyF = array_search($f, array_column($faenas, 'descripcion'));

          $faena = ($keyF!==false) ? $faenas[$keyF]['id'] : null;



          $r = strtolower( $sheet->getCell('M'.$row)->getValue() );

          $keyR = array_search($r, array_column($resultados, 'descripcion'));

          $resultado = ($keyR!==false) ? $resultados[$keyR]['id'] : null;




          $rut = (preg_match('/^(\d+)/', $rut, $array)) ? $array[0] : $rut;



          $telefono = $sheet->getCell('E'.$row)->getValue();

          $telefono = preg_replace('/[^\d]/', '', $telefono);



          $turno = trim( $sheet->getCell('D'.$row)->getValue() );

          $laboratorio = $sheet->getCell('F'.$row)->getValue();



          $lugar = $sheet->getCell('L'.$row)->getValue();

          $lugar = (empty($lugar) || $lugar==null) ? '' : $lugar ;

          $cuar = in_array($resultado, array(2,4) );



          $fecha= $sheet->getCell('H'.$row)->getValue();

          if(!empty($fecha)){

            $fecha = PHPExcel_Shared_Date::ExcelToPHP( $fecha );

            $fecha2 = $fecha;

            $fecha = date('d-m-Y', $fecha);

          }else{

            $fecha = '';

          }



          $hora = $sheet->getCell('J'.$row)->getValue();



          if(!empty($hora) && $hora!=''){



            $hora= $sheet->getCell('J'.$row)->getCalculatedValue();

            if(!empty($hora) && $hora!='' && strlen($hora)>3 ){

              $hora = PHPExcel_Shared_Date::ExcelToPHP( $hora );

              $hora = date('H:i', $hora);

              //$hora = (preg_match('/^(\d{1,2}:\d{2})/', $hora, $array)) ? $array[0] : $hora;

            }else{

              $hora = '';

            }

          }else{

            $hora = '';

          }



          $observacion= $sheet->getCell('N'.$row)->getValue();


          $fila = array(

            'rut' => $rut,

            //'nombre' => $sheet->getCell('C'.$row)->getValue(),
            'nombre' => $sheet->getCell('C'.$row)->getCalculatedValue(),
            
            'telefono' => $telefono,

            'turno' => $turno,

            'tipo' => $examen,

            'fecha' => $fecha,

            'hora' => $hora,

            'laboratorio' => $laboratorio,

            'lugar' => $lugar,

            'faena' => $faena,

            'turno' => $turno,

            'observacion' => $observacion,

            'estado' => $resultado,

          );



          if(is_numeric($rut))

            array_push( $resp['datos'], $fila );


					}
      }

      if(file_exists($archivo))

        unlink($archivo);




    break;

    

    default:

      throw new Exception('CASE default');

  }

  

}catch(Exception $e){



  sleep(3);

  $resp['error'] = $e->getMessage();

  // $resp['post'] = $_POST;

}



//vomito

echo json_encode($resp);

// echo "fin";

//var_dump($resp);

?>

