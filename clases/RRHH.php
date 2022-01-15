<?php
require_once 'Login.php';

require_once 'Usuario.php';




use \Firebase\JWT\JWT;



class RRHH extends Usuario implements iLogin

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



  public function getResumenRespuestasEncuesta( $id )
  {
    
    $datos = $this->datos->getRespuestasResumen( $id );
    //var_dump($datos);
    
    if( count($datos) )
    return array_map(

      function($f){

        return array(

		    'id' => $f['RESP_ID'],
					
			'pers_id' => $f['PERS_ID'],

            'rut' => $f['PERS_RUT'],

            'nombre' => $f['PERS_NOMBRE'],

            'email' => $f['PERS_EMAIL'],

            'telefono' => $f['PERS_CELULAR'],

            'fecha'=>$f['FECHA'],

			'cantidad_respuestas'=>$f['CANT_RESPUESTAS'],

			'porcentaje_respuestas' => $f['PORC_RESPUESTAS'],

			'patron' =>  isset($f['PATRON'])?$f['PATRON']:'',

        );

      }

      , $datos

		);
	else 
	    return array();
	    
	}

  public function getRespuestasEncuesta( $id )// resp_id
  {
    

    return array_map(

      function($f){

        return array(

				  'id' => $f['PREG_ID'],
					
					'texto' => $f['PREG_TEXTO'],

          'grupo' => $f['PREG_GRUPO'],
					
					'valor_cerrado' => $f['ALT_ID'],

          'valor_cerrado_texto' => ucfirst(strtolower($f['ALT_TEXTO'])),

          'valor_abierto' => $f['RESPDET_ABIERTA'],
        );

      }

      ,$this->datos->getRespuestasEncuestaDisc( $id )

		);
	}

	public function getPerfilDiscPostulacion( $procp_id )
	{
			$r = $this->datos->getPerfilEncuestaDiscPostulacion( $procp_id );

			if( is_array($r) && count($r) && isset($r[0]) )
			{
					return array(
							'patron' => $r[0]['DPATRON_PATRON'],
							'emocion' => $r[0]['DPATRON_EMOCION'],
							'meta' => $r[0]['DPATRON_META'],
							'juzga' => $r[0]['DPATRON_JUZGA'],
							'influye' => $r[0]['DPATRON_INFLUYE'],
							'su_valor' => $r[0]['DPATRON_SU_VALOR'],
							'abusa' => $r[0]['DPATRON_ABUSA'],
							'bajo_presion' => $r[0]['DPATRON_BAJO_PRESION'],
							'teme' => $r[0]['DPATRON_TEME'],
							'seria_eficaz' => $r[0]['DPATRON_SERIA_EFICAZ'],
							'observacion1' => $r[0]['DPATRON_OBSERVACION1'],
							'observacion2' => $r[0]['DPATRON_OBSERVACION2'],
							'observacion3' => $r[0]['DPATRON_OBSERVACION3'],
					);
			}else{
					return false;
			}
	}


	public function eliminarRespuestaEncuesta( $id )
	{
			if( is_numeric( $id ) )
					return $this->datos->eliminarRespuestaEncuesta( $id );
			else
					return false;		

	}
	
	public function getSolicitudesOfertaLaboralEstado( $estado_id = 1 ){
			
			$lista = $this->datos->getSolicitudesReclutamientoEstado( $estado_id );

			if( ! $lista )
					return false;

			$this->solicitudes_oferta_laboral = array_map( function( $fila ){

					$sol = new Rec_Solicitud();
					$sol->setId( $fila['ID'] );
					$sol->setUsuarioNombre($fila['NOMBRE']);
					$sol->setDescripcion($fila['DESCRIPCION']);
					$sol->setFechaString( $fila['FECHA']);
					$sol->setEstadoId($fila['ESTADO_ID']);
					$sol->setEstadoDescripcion( $fila['ESTADO_DESC']);
					$sol->setUrgente( $fila['RSOL_URGENTE']==1 || $fila['RSOL_URGENTE']==true );

					return $sol;
			},
					$lista[0] );

			foreach( $this->solicitudes_oferta_laboral as &$sol){

					$sol_id = $sol->getId();

					$perfiles = array_filter( $lista[1],
							function( $p , $k ) use ( $sol_id ){
								return $p['SOL_ID'] == $sol_id;
							}, ARRAY_FILTER_USE_BOTH
					);

					foreach( $perfiles as $p ){

							$perfil = new Rec_Perfil_Solicitud();
							$perfil->setId( $p['ID'] );// id_solicitud_perfil
							$perfil->setDescripcion( $p['PERFIL_DESC'] );//nombre del perfil
							$perfil->setCantidad( $p['CANTIDAD'] );
							$perfil->setObservacion( $p['OBSERVACION'] );

							$sol->addPerfil( $perfil);

					}

			}
			
			return $this->solicitudes_oferta_laboral;
	}

	
	public function registrarOfertaLaboral( $oferta ){

			if( $oferta instanceof Rec_Oferta ){
						
					$this->datos->begin();

					$id_oferta = $this->datos->registrarOfertaCargo( 
						  $oferta->getSolicitudId(), 
							$oferta->getEstadoId(), 
							$oferta->getTitulo(),// puede ser vacio 
							$oferta->getDescripcion(),
							$oferta->getFechaPublicacion(),
							$oferta->getFechaCierre(),
							$estado =1,
							$this->usuario_id
				 	);

					if( ! is_numeric( $id_oferta ) ){
							$this->error = "No fue posible registrar la oferta de cargo";
							return false;
					}

					$oferta->setId($id_oferta);
		
					foreach ((array)$oferta->getPerfiles() as $perfil) {

						
							$id_cargo = $this->datos->registrarOfertaCargoDetalle(
									$oferta->getId(),
									$perfil->getTipoId(),// ?
									$perfil->getId(),
									$perfil->getDescripcion(),
									$perfil->getTurnoId(),
									$perfil->getCantidad(),
									$perfil->getFaena(),
									$perfil->getArea(),
									$estado = 1,
									$perfil->getPlantillaId(),
									$perfil->getSueldo()
							);

							if( ! $id_cargo ){
									
									$this->error ="Ocurrio un problema al intentar registrar uno de los cargos solicitados";
									
									$this->datos->rollback();
									
									return false;							
							}
							else{
								
									foreach((array) $perfil->getCompetencias() as $competencia ){

											$ptje_min = 0;
											$ptje_max = 0;

											if( $competencia->getEsEvaluada() ){

													$ptje_min = $competencia->getPuntajeMinimo();

													$ptje_max = $competencia->getPuntajeMaximo();
											}

											$comp_id = $this->datos->registrarOfertaCargoCompetencia( $id_cargo, $competencia->getId(), $competencia->getEvaluadorId() , $ptje_min, $ptje_max );

											if( ! $comp_id ){
													
													$this->error ="Ocurrio un problema al intentar registrar una de las competencias para los cargos requeridos";
													
													$this->datos->rollback();
													
													return false;												
											}
									
									}
								
									foreach( (array) $perfil->getDocumentos() as $documento ){

											$doc_id = $this->datos->registrarOfertaCargoDocumento( $id_cargo, $documento->getTipo(), $estado = 1 );
											
											if( ! $doc_id ){
													
													$this->error ="Ocurrio un problema al intentar registrar uno de los documentos para los cargos requeridos";
													
													$this->datos->rollback();
													
													return false;												
											}
									
									}
		
									foreach( (array) $perfil->getImplementos() as $implemento ){

											$ok = $this->datos->registrarOfertaCargoImplemento( $id_cargo, $implemento->getId(), $estado = 1 );
											
											if( ! $ok ){
													
													$this->error ="Ocurrio un problema al intentar registrar uno de los implementos para los cargos requeridos";
													
													$this->datos->rollback();
													
													return false;												
											}
									
									}


									
									foreach( (array) $perfil->getExamenes() as $examen ){

											$ok = $this->datos->registrarOfertaCargoExamen( $id_cargo, $examen->getId() );
											
											if( ! $ok ){
													
													$this->error ="Ocurrio un problema al intentar registrar uno de los examenes para los cargos requeridos";
													
													$this->datos->rollback();
													
													return false;												
											}
									
									}
								
							}
						
					}

					$this->datos->commit();


					return $id_oferta;
			}
			else{

					$this->error = "El parametro no corresponde a una solicitud de oferta laboral";

					return false;
			}
	}


	public function rechazarSolicitudOfertaLaboralAprobada( $sol_id ){
			return $this->datos->cambiarEstadoSolicitudReclutamiento( $sol_id, 2, $this->usuario_id );
	}


	public function registrarProgramacionTurno( $turno, $fecha, $empleados = array() ){

			$this->error = "";

			if( empty( $empleados) ){
					
					$this->error = "El listado esta vacio";

					return false;

			}

			if( ! $id_turno = $this->datos->registrarProgramacionTurno( $turno, $fecha, $this->usuario_id ) ){
	
					$this->error = "No fue posible registrar el turno";

					return false;
			}


			$this->datos->begin();
			
			$insertados = 0;

			foreach( (array)$empleados as $e ){
					
					//echo $e->getEstadoDescripcion();
					//throw new Exception( 'finnn');

					if( $e instanceof Empleado ){
							
							$this->datos->registrarProgramacionTurnoDetalle(
								$id_turno, 
								$e->getRut(),
								$e->getDV(),
								$e->getNombre(),
								$e->getCargo(),
								$e->getTurno(),
								$e->getTelefono(),
								$e->getComuna(),
								$e->getEstadoDescripcion(),
								$e->getDesdePlanilla(),
								$this->usuario_id
							);

							$insertados++;
					}
					else{
							
							$this->datos->rollback();
							
							$this->error = "No fue posible registrar el empleado";

							return false;
					
					}
				}
			
			$this->datos->commit();
		
			return $insertados;
	}


	public function actualizarProgramacionTurnoDetalle($ptd_id , $contrato, $examen, $encuesta, $fecha_venc_induccion, $fecha_venc_altura_geo){
	
			
			return $this->datos->actualizarProgramacionTurnoDetalle(
					$ptd_id, 
					$contrato, 
					$examen, 
					$encuesta, 
					$fecha_venc_induccion, 
					$fecha_venc_altura_geo, 
					$this->usuario_id);

	}

	public function actualizarProgramacionTurnoDetalleEstado($ptd_id , $pte_id){
	
			
			return $this->datos->actualizarProgramacionTurnoDetalleEstado(
					$ptd_id, 
					$pte_id, 
					$this->usuario_id);

	}

	public function validarDocumentoPostulante(  $pRDOCOFE_ID, $pPOST_ID,  $pVALIDADO  ){

			return $this->datos->actualizarReclutamientoOfertaDocumentoEstado(  $pRDOCOFE_ID, $pPOST_ID,  $pVALIDADO  );

	}

	public function eliminarProgramacionTurnoDetalle(  $pPTD_ID ){

			return $this->datos->eliminarProgramacionTurnoDetalle(  $pPTD_ID  );

	}

	public function crearImagenOfertaAgrupada( $descripcion='', $cargos = array() ){
			
			
			$dir = __DIR__."/../servicios/imagenes";
			
			$imagen_dir = __DIR__."/../servicios/imagenes/publicacion_tipo_1.png";
			
			$fuente_dir_bold = __DIR__."/../servicios/imagenes/LiberationSans-Bold.ttf";
			
			$fuente_dir_regular = __DIR__."/../servicios/imagenes/LiberationSans-Regular.ttf";
			
			$imagen = imagecreatefrompng ($imagen_dir);
			
			$imagen_tamano = getimagesize($imagen_dir);
			
			$w_imagen = imagesx($imagen);

			
			$color_blanco = imagecolorallocate ($imagen, 255, 255, 255);
			
			$color_gris = imagecolorallocate ($imagen, 177, 177, 177);
			
			$color = imagecolorallocate ($imagen, 0, 0, 0);
			
			$tamano =20;
			
			$h_texto = 20;
			
			$angulo = 0;

			
			$x = 100;
			
			$y = 210;


		
			#descripcion o texto principal


			$y+=50;

			
			$lineas = $this->getLineas( $descripcion, $h_texto, $fuente_dir_regular , $w_imagen, $ml = 100, $mi = 100,  $angulo = 0);

			
			$i=0;

			
			foreach($lineas as $linea){
		
					
					if( $i > 0 )
							
							$y+=30;
		
					
					$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_regular,$linea );
						
					
					$x = ($w_imagen / 2.0 ) - ( $coordenadas[4]/2.0 );

					
					imagettftext($imagen, $h_texto, $angulo, $x , $y , $color_blanco , $fuente_dir_regular , $linea );

					
					$i++;
			
			}

			
			$y+=50;



		
	
			foreach($cargos as $cargo){
				
		
					$txt = $cargo;

					$lineas = $this->getLineas( $txt, $h_texto, $fuente_dir_bold , $w_imagen, $ml = 100, $mi = 100,  $angulo = 0);
				
					$i=0;
					
					foreach($lineas as $linea){
	
							$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_bold, $linea );
						
							$x = ($w_imagen / 2.0 ) - ( $coordenadas[4]/2.0 );
						
							$y += abs($coordenadas[7] - $coordenadas[1]);

							$y+= ( $i > 0 ) ? 10 : 30;

							imagettftext($imagen, $h_texto, $angulo, $x , $y , $color_blanco ,$fuente_dir_bold , $linea );
						
							$i++;
					
					}
					
					unset($linea);
			
			}

			
			unset($cargo);


			#footer
			
			imagettftext($imagen, $tamano, $angulo, $x=320, $y=1160, $color_gris,$fuente_dir_regular,$texto="Interesados, postular en nuestra plataforma");

			
			imagettftext($imagen, $tamano, $angulo, $x=520, $y+=50, $color_blanco,$fuente_dir_regular,$texto="reclutamiento.todoacero.cl");

			
			//header ("Content-type: image/png");
			$nombre_img = 'img_oferta_'.rand().''.time().'.png';
			
			imagepng ($imagen, $dir.'/'.$nombre_img );
			
			imagedestroy($imagen);

			return $nombre_img;
	}
	

	public function crearImagenOfertaIndividual( $descripcion='', $cargo ='', $competencias = array() ){

			
			$dir = __DIR__."/../servicios/imagenes";

			$imagen_dir = __DIR__."/../servicios/imagenes/publicacion_tipo_1.png";
			
			$fuente_dir_bold = __DIR__."/../servicios/imagenes/LiberationSans-Bold.ttf";
			
			$fuente_dir_regular = __DIR__."/../servicios/imagenes/LiberationSans-Regular.ttf";


			
			$imagen = imagecreatefrompng ($imagen_dir);
			
			$imagen_tamano = getimagesize($imagen_dir);
			
			$w_imagen = imagesx($imagen);

			
			$color_blanco = imagecolorallocate ($imagen, 255, 255, 255);
			
			$color_gris = imagecolorallocate ($imagen, 177, 177, 177);
			
			$color = imagecolorallocate ($imagen, 0, 0, 0);
			
			$tamano =20;
			
			$h_texto = 20;
			
			$angulo = 0;

			
			$x = 100;
			
			$y = 210;

			#descripcion o texto principal
			
			$y+=50;
			
			$lineas = $this->getLineas( $descripcion, $h_texto, $fuente_dir_regular , $w_imagen, $ml = 100, $mi = 100,  $angulo = 0);
			
			$i=0;

			
			foreach($lineas as $linea){
		
					if( $i > 0 )
							
							$y+=30;
		
					
					$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_regular,$linea );
						
					
					$x = ($w_imagen / 2.0 ) - ( $coordenadas[4]/2.0 );

					
					imagettftext($imagen, $h_texto, $angulo, $x , $y , $color_blanco , $fuente_dir_regular , $linea );

					
					$i++;
			
			}

			
			$y+=50;


			$lineas = $this->getLineas( $cargo, $h_texto = 50, $fuente_dir_bold , $w_imagen, $ml = 100, $mi = 100,  $angulo = 0);

		
			$i=0;

			
			foreach($lineas as $linea){
						
					$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_bold, $linea );
						
					$x = ($w_imagen / 2.0 ) - ( $coordenadas[4]/2.0 );
						
					$y += abs($coordenadas[7] - $coordenadas[1]);

					$y+= ( $i > 0 ) ? 10 : 30;

					imagettftext($imagen, $h_texto, $angulo, $x , $y , $color_blanco ,$fuente_dir_bold , $linea );
				
					$i++;

			}

			
			unset($linea);

			if( count($competencias) > 1 && is_array($competencias) ){
			
					
					$ultimo = array_pop($competencias);
			
					
					$comp = implode(", ", $competencias).' y '.$ultimo;
					
					$txt = $comp;

			}else{

					$txt = $competencias[0];

			}

			
			$lineas = $this->getLineas( $txt, $h_texto = 20, $fuente_dir_regular , $w_imagen, $ml = 100, $mi = 100,  $angulo = 0);

			
			$i=0;

			
			foreach($lineas as $linea){
						
					$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_bold, $linea );
						
					$x = ($w_imagen / 2.0 ) - ( $coordenadas[4]/2.0 );
						
					$y += abs($coordenadas[7] - $coordenadas[1]);

					$y+= ( $i > 0 ) ? 10 : 30;

					imagettftext($imagen, $h_texto, $angulo, $x , $y , $color_blanco ,$fuente_dir_bold , $linea );
				
					$i++;
	
			}
			
			unset($linea);

			unset($comp);
			
			#footer
			
			imagettftext($imagen, $tamano, $angulo, $x=320, $y=1160, $color_gris,$fuente_dir_regular,$texto="Interesados, postular en nuestra plataforma");
			
			imagettftext($imagen, $tamano, $angulo, $x=520, $y+=50, $color_blanco,$fuente_dir_regular,$texto="reclutamiento.todoacero.cl");

		
			$nombre_img = 'img_oferta_'.rand().''.time().'.png';
			
			imagepng ($imagen, $dir.'/'.$nombre_img );
			
			imagedestroy($imagen);

			return $nombre_img;
		}


	public function crearImagenOfertaIndividual2( $faena='', $turno='', $cargo ='', $competencias = array() ){
				
			$dir = __DIR__."/../servicios/imagenes";

			$imagen_dir = __DIR__."/../servicios/imagenes/publicacion_tipo_3.png";
			
			$fuente_dir_bold = __DIR__."/../servicios/imagenes/LiberationSans-Bold.ttf";
			
			$fuente_dir_regular = __DIR__."/../servicios/imagenes/LiberationSans-Regular.ttf";

				
			$imagen = imagecreatefrompng ($imagen_dir);
			
			$imagen_tamano = getimagesize($imagen_dir);
			
			$w_imagen = imagesx($imagen);

			
			$color_blanco = imagecolorallocate ($imagen, 255, 255, 255);
			
			$color_gris = imagecolorallocate ($imagen, 177, 177, 177);
			
			$color = imagecolorallocate ($imagen, 0, 0, 0);
			
			$h_texto = 30;
			
			$angulo = 0;
	
			$lineas = $this->getLineas( strtoupper($cargo), $h_texto, $fuente_dir_bold , $w_imagen, $mi = 20, $md = 60,  $angulo = 0);

		
			$x = 20;

			
			if(  count($lineas)==1 ){
				
					$h_texto = 30;
					
					$y = 590;
		
			}else{
		
					$y = 575;
				
					$h_texto = 17;

			}

			
			$lineas = $this->getLineas( strtoupper($cargo), $h_texto, $fuente_dir_bold , $w_imagen, $mi, $md, $angulo = 0);

			$i=0;

	
			foreach($lineas as $linea){
						
					$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_bold, $linea );
						
					$y += abs($coordenadas[7] - $coordenadas[1]);

					if($i>0)

							$y+= 10;
						
					imagettftext($imagen, $h_texto, $angulo, $x+$mi , $y , $color_blanco ,$fuente_dir_bold , $linea );
				
					$i++;
	
			}

			unset($linea);
	
			imagettftext($imagen, $h_texto=17, $angulo, $x = 280 , $y = 674 , $color ,$fuente_dir_bold , $faena.', '.$turno );

		
			$compt_tmp = $competencias;

			
			//$ultimo = array_pop($compt_tmp);
			
			$comp = implode(" - ", $compt_tmp);

			
			$txt = $comp;

			
			$lineas = $this->getLineas( $txt, $h_texto = 17, $fuente_dir_regular , $w_imagen, $mi = 15, $md = 45,  $angulo = 0);

			$lineas = ( count($lineas)>5)? array_slice($lineas,0,5) : $lineas ;
			/*
			if( count($lineas) > 5 ){

					

					$ultimo = array_pop($compt_tmp);
					
					$comp = implode(", ", $compt_tmp).' y '.$ultimo;
					
					$txt = $comp;
				
					$lineas = $this->getLineas( $txt, $h_texto = 17, $fuente_dir_regular , $w_imagen, $mi = 15, $md = 45,  $angulo = 0);
		
			}*/

			$y = 700;

			$x = 10 + $mi;

			$i=0;

			$contador = count($lineas) -1;
	
			foreach($lineas as $linea){

					if($contador===$i)
							$l = rtrim($linea, ' - ');
					else
							$l=$linea;

					$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_regular, $l );

					$y += abs($coordenadas[7] - $coordenadas[1]);

					$y+= 7;

					imagettftext($imagen, $h_texto, $angulo, $x , $y , $color_blanco ,$fuente_dir_regular , $l );

					$i++;
	
			}
		
			unset($linea);

			unset($comp);
			
			#footer
			
			imagettftext($imagen, $tamano = 16, $angulo, $x=100, $y=890, $color_gris,$fuente_dir_regular,$texto="Interesados, postular en nuestra plataforma");
			
			imagettftext($imagen, $tamano=17, $angulo, $x=100, $y+=25, $color,$fuente_dir_bold,$texto="reclutamiento.todoacero.cl");
			
			$nombre_img = 'img_oferta_'.rand().''.time().'.png';
			
			imagepng ($imagen, $dir.'/'.$nombre_img );
			
			imagedestroy($imagen);

			return $nombre_img;

	}


	private function getLineas( $texto, $h_texto, $fuente , $w_imagen, $ml = 0, $mi = 0,  $angulo = 0){

		$w = $w_imagen - $ml - $mi;

		$coordenadas = imagettfbbox($h_texto, $angulo, $fuente, $texto );
				
		$w_linea = abs($coordenadas[2] - $coordenadas[0]);

		if( $w_linea < $w )

				return array($texto);

		else{

				$palabras = explode(" ", $texto);

				$lineas = array(0=>'');

				$linea = 0;

				foreach($palabras as $palabra){

						$linea_txt = $lineas[$linea].' '.$palabra;

						$coordenadas = imagettfbbox($h_texto, $angulo, $fuente, $linea_txt );
				
						$w_linea = abs($coordenadas[2] - $coordenadas[0]);


						if( $w_linea > $w ){
						
								$linea++;
								
								$lineas[$linea]= $palabra;

						}else{

								$lineas[$linea]= $linea_txt;

						}
	
				}

				unset($palabra);

				return $lineas;
		}
	
	}


}
?>
