<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Mailer/src/Exception.php';
require 'Mailer/src/PHPMailer.php';
require 'Mailer/src/SMTP.php';

include('../clases/PasaporteCovid19.php');
include('../datos/temp_conexion.php');

$resp = array('error'=>false, 'datos'=> array() );
try{

  $JSONpost = file_get_contents("php://input");
  $post = json_decode($JSONpost)->params;

/*$post->opcion ='dashboard';
$post->encta_id = 1;*/

  if( !isset( $post->opcion ) )
     throw new Exception('no viene la opcion');

  $op = $post->opcion;

  //$op='pasaporte';

  switch($op){
    case 'pasaporte':

      if(!isset($post->encta_id))
        throw new Exception("Id encuesta no recibida", 1);

      $dbh = Conexion2::Conectar();
      $dbh->beginTransaction();
      $dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

      $stmt = $dbh->prepare("CALL RESPONDER_ENCUESTA(:pRUT , :pNOMBRE, :pEMAIL, :pCELULAR, :pENC_ID, :pFA_ID)");
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute( array(
        ':pRUT' => $post->usuario->rut,
        ':pNOMBRE' => $post->usuario->nombre,
        ':pEMAIL' => $post->usuario->email,
        ':pCELULAR' => $post->usuario->celular,
        ':pENC_ID' => $post->encta_id,
        ':pFA_ID' => $post->usuario->faena
      ) );

      $resp_id = $stmt->fetchColumn();
      $stmt->closeCursor();

      $resp['datos']['resp_id']=$resp_id;

      if(!is_numeric($resp_id))
        throw new Exception("Error al registrar la respuesta (encabezado)", 1);

      $stmt = $dbh->prepare("CALL REGISTRAR_PASAPORTE(:pPAS_ID , :pESTCONT_ID, :pRESP_ID, :pPAS_TEMPERATURA)");
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute( array(
        ':pPAS_ID'=> null,
        ':pESTCONT_ID'=> 1, //cambiar
        ':pRESP_ID'=>$resp_id,
        ':pPAS_TEMPERATURA'=> null
      ) );

      $resp['datos']['respuestas'] = array();
      $test1= 0;
      $test2= 0;

      foreach ($post->respuestas as $r ) {
          $stmt2 = $dbh->prepare("CALL RESPONDER_PREGUNTA(:pENCTA_ID, :pRESP_ID, :pPREG_ID, :pALT_ID, :pRESP_ABIERTA )");
          $stmt2->execute( array(
            ':pENCTA_ID' => $post->encta_id,
            ':pRESP_ID' => $resp_id,
            ':pPREG_ID' => $r->id,
            ':pALT_ID' => $r->valorCerrado,
            ':pRESP_ABIERTA' => $r->valorAbierto
          ) );
          //$test2 = $stmt2->fetchColumn();
      }
      $dbh->commit();

////////////////////////////
/*
      $parametros = array();
      $querys = array();
      foreach ($post->respuestas as $key => $r ) {
        $parametros = array_merge( $parametros, array(
          ':pENCTA_ID'.$key => $post->encta_id,
          ':pRESP_ID'.$key => $resp_id,
          ':pPREG_ID'.$key => $r->id,
          ':pALT_ID'.$key => $r->valorCerrado,
          ':pRESP_ABIERTA'.$key => $r->valorAbierto
        ));
        $querys = array_push($querys,"CALL RESPONDER_PREGUNTA(:pENCTA_ID, :pRESP_ID, :pPREG_ID, :pALT_ID, :pRESP_ABIERTA )");

          //$stmt->execute(  );
      }

      $stmt = $dbh->prepare("CALL RESPONDER_PREGUNTA(:pENCTA_ID, :pRESP_ID, :pPREG_ID, :pALT_ID, :pRESP_ABIERTA )");
      $test2 = $stmt->fetchColumn();
      $dbh->commit();
*/
      $fecha= date('d-m-Y');


      $pdf = new PasaporteCovid19($post->usuario->rut, $post->usuario->nombre ,$post->usuario->faena,  $fecha );
      $pdfdoc = $pdf->Output('pasaportecovid19.pdf', 'S');

      $subject ="Probando formulario covid";
      $body ="hola, esta es solo una prueba";
      $mail = new PHPMailer(TRUE);
      $mail->setFrom("gestop@todoacero.cl", 'Gestop ERP');
      // $mail->addAddress("jguasch@todoacero.cl", 'Julio Guasch');
      $mail->addAddress($post->usuario->email, $post->usuario->nombre);
      $mail->isHTML(true);

      $mail->Subject = $subject;
      $mail->Body = utf8_decode($body);
      $mail->addBCC("jguasch@todoacero.cl");
      $mail->addBCC("aperalta@todoacero.cl");
      $mail->addBCC("rmelgarejo@todoacero.cl");
      $mail->AddStringAttachment($pdfdoc, 'pasaportecovid.pdf', 'base64', 'application/pdf');
      $mail->send();

      $resp['datos']['rut']=$post->usuario->rut;
      $resp['datos']['nombre']=$post->usuario->nombre;
      $resp['datos']['fecha']=$post->usuario->fecha;

    break;

    case 'dashboard':
      if(!isset($post->encta_id))
        throw new Exception("Id encuesta no recibida", 1);

      $pENCTA_ID = $post->encta_id;
      //$pFECHA_INI = '2021-01-01';
      //$pFECHA_FIN = '2021-01-30';
      list($ano,$mes,$dia) = explode('-', $post->fecha_ini);
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

      $dbh = Conexion2::Conectar();

      ////////////estados
      $estados = array();
      $faenas = array();
      $respuestas = array();
      $resumen = array();

      $stmt = $dbh->prepare("CALL LISTAR_ESTADO_CONTAGIO()");
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
      while ( $row = $stmt->fetch() ){
        if(in_array($row['ESTCONT_ID'], (array)$post->estado ) || empty($post->estado) )
          array_push($estados,  array(
            'id'=>$row['ESTCONT_ID'],
            'descripcion'=>$row['ESTCONT_DESCRIPCION']
          ));
      }

      //////////faenas
      $stmt = $dbh->prepare("CALL FAENA_LISTAR_FAENA()");
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
      while ( $row = $stmt->fetch() ){
        if(in_array($row['FA_ID'], (array)$post->faena ) || empty($post->faena) )
          array_push($faenas,  array(
            'id'=>$row['FA_ID'],
            'descripcion'=>$row['FA_NOMBRE']
          ));
      }


      ///listar encuesta
      $stmt = $dbh->prepare("CALL ENCUESTA_LISTAR_ESTADO_CONTAGIO( :pENCTA_ID, :pFECHA_INI, :pFECHA_FIN , :pFAENA, :pESTADO )");
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute(
        array(
          ':pENCTA_ID'=>$pENCTA_ID,
          ':pFECHA_INI'=>$pFECHA_INI,
          ':pFECHA_FIN'=>$pFECHA_FIN,
          ':pFAENA'=>$pFAENA,
          ':pESTADO'=>$pESTADO,
        )
      );

      $fechas = array();
      while ( $row = $stmt->fetch() ){
        array_push($fechas,  $row['FECHA']);
      }

      $stmt->nextRowset();

      //$spline= array();
      $datos = $stmt->fetchAll();
      /*$spline = array(
        'habilitados' => array_column($datos, 'HABILITADO'),
        'sospecha' => array_column($datos, 'SOSPECHA'),
        'contacto_estrecho' => array_column($datos, 'CONTACTO_ESTRECHO'),
        'positivo_covid_19' => array_column($datos, 'POSITIVO_COVID_19')
      );*/
      $spline = array(
        array(
          'estado'=>'HABILITADO',
          'data' => array_column($datos, 'HABILITADO')
        ),
        array(
          'estado' =>'SOSPECHA',
          'data' => array_column($datos, 'SOSPECHA')
        ),
        array(
          'estado' =>'CONTACTO ESTRECHO',
          'data' => array_column($datos, 'CONTACTO_ESTRECHO')
        ),
        array(
          'estado' =>'POSITIVO COVID 19',
          'data' => array_column($datos, 'POSITIVO_COVID_19')
        ),
      );

      $stmt->nextRowset();

      while ( $row = $stmt->fetch() ){
        array_push($respuestas,  array(
          'fa_id'=>$row['FA_ID'],
          'fecha'=>$row['FECHA'],
          'rut'=>$row['PERS_RUT'],
          'nombre'=>$row['PERS_NOMBRE'],
          'celular'=>$row['PERS_CELULAR'],
          'email'=>$row['PERS_EMAIL'],
          'fa_nombre'=>$row['FA_NOMBRE'],
          'estado'=>$row['ESTCONT_DESCRIPCION']
        ));
      }

      ///listar resumen
      $stmt = $dbh->prepare("CALL ENCUESTA_LISTAR_RESUMEN_FAENA( :pENCTA_ID, :pFECHA_INI, :pFECHA_FIN , :pFAENA, :pESTADO )");
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute(
        array(
          ':pENCTA_ID'=>$pENCTA_ID,
          ':pFECHA_INI'=>$pFECHA_INI,
          ':pFECHA_FIN'=>$pFECHA_FIN,
          ':pFAENA'=>$pFAENA,
          ':pESTADO'=>$pESTADO,
        )
      );
      //$sumatoria =  0;
      while ( $row = $stmt->fetch() ){
        array_push($resumen,  array(
          'fa_id'=>$row['FA_ID'],
          'fa_nombre'=>$row['FA_NOMBRE'],
          'estado'=>$row['ESTCONT_DESCRIPCION'],
          'total'=>$row['TOTAL'],
          'porcentaje'=>$row['PORCENTAJE_FAENA']
        ));
        //$sumatoria+=$row['TOTAL'];
      }
      // foreach ($resumen as &$row) {
      //   $row['porcentaje'] = $row['total']/$sumatoria*100;
      // }
      unset($row);

      //llenado de respuesta

      $resp['datos']['estados']=$estados;
      $resp['datos']['faenas']=$faenas;
      $resp['datos']['fechas']=$fechas;
      $resp['datos']['spline']=$spline;
      $resp['datos']['respuestas']=$respuestas;
      $resp['datos']['resumen']=$resumen;
    break;
    default:
      throw new Exception('CASE default');
  }
}catch(Exception $e){
  $resp['error'] = $e->getMessage();
}
//vomito
echo json_encode($resp);
?>
