<?php 

require_once dirname(__DIR__).'/datos/Datos.php';

//require_once('../clases/Rec_Competencia_Evaluada.php');
require_once('Rec_Competencia_Evaluada.php');

require_once 'Rec_Examen_Evaluado.php';

require_once 'Rec_Perfil.php';

require_once 'Rec_Postulante.php';

class Rec_Perfil_Evaluado extends Rec_Perfil
{
		
		private $tipo_id;

		private $turno_id;

		private $fecha;

		private $faena_id;
		
		private $area_id;
		
		private $faena_nombre;

		private $area_nombre;
	
		private $fecha_requerida;

		private $plantilla_id; //plantilla de las etapas de postulacion

		private $competencias;

		private $examenes;

		private $puntaje;

		private $solicitante_nombre;

		private $oferta_id;

		private $nro_contrato;

		public function __construct(){

				$this->competencias = array();
				$this->examenes = array();

				$this->datos = new Datos();
		}

		/******** GETTERS  ****************/

		public function getTipoId(){
				return $this->tipo_id;
		}
		public function getTurnoId(){

				return $this->turno_id;
		}

		public function getFaena(){

				return $this->faena_id;
		}

		public function getArea(){
				return $this->area_id;
		}
		
		public function getFaenaNombre(){

				return $this->faena_nombre;
		}

		public function getAreaNombre(){
				return $this->area_nombre;
		}
		public function getFechaReq(){
				return $this->fecha_requerida;
		}

		public function getPlantillaId(){
				return $this->plantilla_id;
		}

		public function getPuntaje(){
				return $this->puntaje;
		}
		
		public function getCompetencias(){
				return $this->competencias;
		}
		
		public function getExamenes(){
				return $this->examenes;
		}
		public function getSolicitanteNombre(){
				return $this->solicitante_nombre;
		}

		public function getOfertaId(){
				return $this->oferta_id;
		}

		public function getNroContrato(){
				return $this->nro_contrato;
		}

		public function getCalculoPuntaje(){
				
				if( empty( $this->competencias ) ){

						return 0;
				}
				else{
						
						
						$total = 0;
						$suma = 0;
						/////se debe calcular el puntaje de las competencias en general

						foreach( $this->competencias as $comp ){
								//codigo
								$suma+=$comp->GetPuntajeObtenido();
						}
						
						$total = $suma;

						return $total;

				}
		}

		/******** SETTERS  ************/
		
		public function setTipoId( $tipo_id ){
				$this->tipo_id= $tipo_id;
		}

		public function setTurnoId($turno_id){
				$this->turno_id = $turno_id;
		}

		public function setFaena( $id ){
				$this->faena_id = $id;
		}

		public function setArea( $id ){
				$this->area_id = $id;
		}
		
		public function setFaenaNombre( $nombre ){
				$this->faena_nombre = $nombre;
		}

		public function setAreaNombre( $nombre ){
				$this->area_nombre = $nombre;
		}
		public function setFechaReq( $fecha ){
				$this->fecha_requerida = $fecha;
		}

		public function setPlantillaId( $plantilla_id ){
				$this->plantilla_id = $plantilla_id;
		}

		public function setPuntaje( $puntaje ){
				$this->puntaje = $puntaje;
		}

		public function setSolicitanteNombre( $nombre ){
				$this->solicitante_nombre = $nombre;
		}

		public function setOfertaId( $id ){
				$this->oferta_id = $id;
		}
	
		public function setNroContrato( $nro_contrato ){
				$this->nro_contrato = $nro_contrato;
		}	

		public function setCompetencias( ){

			if( !empty( $this->getId() ) ){
				
					$lista = $this->datos->getCompetenciasOfertaPerfil( $this->getId() );

					foreach( (array)$lista as $t){
								
							$competencia = new Rec_Competencia_Evaluada();
							$competencia->setId($t['ROFECOMP_ID']);
							$competencia->setTitulo($t['COMP_TITULO']);
							$competencia->setDescripcion( $t['COMP_DESCRIPCION'] );
							$competencia->setTipoId($t['COMP_ID']);
							//$competencia->setTipoDescripcion($t['TIPO_DESCRIPCION']);
							
							$this->addCompetencia( $competencia );

					}

			}else{
			
					return false;
			
			}
	
	}
		
		public function setExamenes(){

				if( !empty( $this->getId() ) ){

						$lista = $this->datos->getListaExamenOfertaCargoPerfil( $this->getId() );

						foreach((array)$lista as $t){

								$examen = new Rec_Examen_Evaluado();
								$examen->setId( $t['REX_ID']);
								$examen->setDescripcion($t['REX_DESCRIPCION']);
								$examen->setTitulo( $t['REX_TITULO']);

								$this->addExamen( $examen);
						}
				}
		}
		
		
		public function setImplementos(){

				if( !empty( $this->getId() ) ){

						$lista = $this->datos->getImplementosPerfilOferta( $this->getId() );

						foreach( (array)$lista as $t){

								$implemento = new Implemento();
								$implemento->setId( $t['ID']);
								$implemento->setDescripcion($t['DESCRIPCION']);
								$implemento->setTipoId( $t['TIPO_ID']);
								$implemento->setTipoDescripcion( $t['TIPO_DESCRIPCION']);
								$implemento->setIcono( $t['ICONO'] );

								$this->addImplemento( $implemento );
						}
				}
		}

		public function setDocumentos( /*$postulante_id = null*/ ){

			if( !empty( $this->getId() ) ){
				
					$lista = $this->datos->getDocumentosPerfilOferta( $this->getId()/*, $postulante_id*/ );

					foreach( (array)$lista as $t){
								
							$documento= new Rec_Documento();
							$documento->setId($t['ID']);
							$documento->setDescripcion($t['DESCRIPCION']);
							$documento->setTipo($t['TIPO_ID']);
							/*
							if( isset($t['PDO_VALIDADO']) )
									$documento->setValidado( !$t['PDO_VALIDADO'] ? false : true );
							*/
							$this->addDocumento( $documento );

					}

			}
	
		}
		
		public function addCompetencia( $competencia ){

				if( $competencia instanceof Rec_Competencia_Evaluada ){

						if( empty( $this->competencias ) )
								$this->competencias = array();

						array_push( $this->competencias, $competencia );
						
						return true;

				}
				else{
						return false;
				}
		}
		
		public function addExamen( $examen ){
				
				if( $examen instanceof Rec_Examen_Evaluado ){

						if( empty( $this->examenes ) )
								$this->examenes = array();

						array_push( $this->examenes, $examen );

						return true;
				}
				else{
						return false;
				}

		}

		public function getPDF( $post_id = null ){
		
				require_once('../servicios/TCPDF/tcpdf.php');

				$nombre_completo ="";

				$telefono = "";

				$email = "";

				$competencias = array();


				if( !empty($post_id) ){
				
						$postulante = new Rec_Postulante();
						$postulante->setId($post_id);
						$r = $postulante->setDatos();

						$nombre_completo = $postulante->getNombre().' '.$postulante->getApaterno().' '.$postulante->getAmaterno();
						$telefono= $postulante->getTelCelular();
						$email= $postulante->getEmail();
						
						$competencias = $this->datos->getCompetenciasOfertaPerfil( $this->getId(), $post_id );

				}else{
						
						$competencias = $this->datos->getCompetenciasOfertaPerfil( $this->getId() );
				
				}
				
				$examenes = $this->datos->getListaExamenOfertaCargoPerfil( $this->getId() );

				$oferta = $this->datos->getCargoPerfilOfertaReclutamiento( $this->getId() );

				$oferta_id = $oferta[0]['ROFE_ID'];

				$oferta_cargo = $oferta[0]['CPERF_DESCRIPCION'];

				$pdf = new TCPDF('p', 'mm', array( 216, 330 ) , true, 'UTF-8', false);
				
				$pdf->SetPrintHeader(false);
				
				$pdf->SetPrintFooter(false);

				
				$pdf->SetMargins(20, 20,20, 20);
				
				$pdf->SetAutoPageBreak(TRUE, 5);

				$pdf->AddPage();
		
				$txt="PERFIL SOLICITADO EN LA OFERTA FOLIO ".$oferta_id;
				
				$pdf->SetFont('helvetica', 'B', 10);


				//$pdf->MultiCell(0, 0, $txt, 0, 'C', 1, 0, '', '', true, 0, false, true, $htitulo=0, 'M',true);
				
				$pdf->MultiCell(0, 5, $txt, 0, 'R', 0, 0, '', '', true );
				
				$pdf->Ln();
				$pdf->Ln();
				$pdf->SetTextColor( 93, 109, 126 );
				$pdf->SetFont('helvetica', 'B', 17);
				$pdf->Cell(0, 0, $oferta_cargo , 0, 0, $align = 'L', $fill = false);

				$pdf->Ln();

				$c = count( $this->competencias );
				$b = count( $this->examenes );
				
				$pdf->SetFont('helvetica', 'N', 12);

				$pdf->SetTextColor( 0 , 0 , 0 );
			
				//$pdf->Cell(0, 0, $this->getTitulo() , 0, 0, $align = 'L', $fill = false);

		
				$pdf->Ln();
				$pdf->Cell(60, 0, "Nombre del postulante" , 0, 0, $align = 'L', $fill = false);
				$pdf->Cell(0, 0, ": ".$nombre_completo , 0, 0, $align = 'L', $fill = false);
				
				$pdf->Ln();
				$pdf->Cell(60, 0, "Telefono" , 0, 0, $align = 'L', $fill = false);
				$pdf->Cell(0, 0, ": ".$telefono , 0, 0, $align = 'L', $fill = false);
				
				$pdf->Ln();
				$pdf->Cell(60, 0, "Correo electronico" , 0, 0, $align = 'L', $fill = false);
				$pdf->Cell(0, 0, ": ".$email , 0, 0, $align = 'L', $fill = false);

				$pdf->Ln();
				$pdf->Ln();
			/*	
				$pdf->SetFont('helvetica', 'B', 12);
				$pdf->Cell(0, 0, "Competencias" , 0, 0, $align = 'L', $fill = false);
*/
				$pdf->Ln();
				$pdf->SetFont('helvetica', 'N', 12);
				

				$comp_todoacero = array_filter($competencias, function($v,$k){
						return $v['COMPTIP_ID']==1;
				}, ARRAY_FILTER_USE_BOTH);
	
				$comp_blandas= array_filter($competencias, function($v,$k){
						return $v['COMPTIP_ID']==3;
				}, ARRAY_FILTER_USE_BOTH);

				$comp_tecnicas= array_filter($competencias, function($v,$k){
						return !in_array($v['COMPTIP_ID'], array(1,3) );
				}, ARRAY_FILTER_USE_BOTH);

				//$t = array_shift($comp_tecnicas)['COMPTIP_DESCRIPCION'];
				$pdf->SetFont('helvetica', 'B', 12);
				$pdf->Cell(0, 0, 'Competencias tecnicas' , 0, 0, $align = 'L', $fill = false);
				$pdf->Ln();

	
				foreach ( $comp_tecnicas as $c ){
						
						$pdf->Ln();

						$pdf->Cell(160, 0, $c['COMP_TITULO'] ,1 , 0, $align = 'L', $fill = false);
						$puntaje= empty( $c['POEV_PUNTAJE'] ) ? ' ' : $c['POEV_PUNTAJE'] ;
						$pdf->Cell(0, 0, $puntaje ,1 , 0, $align = 'L', $fill = false);

				}

				$pdf->Ln();
				$pdf->Ln();
				$pdf->SetFont('helvetica', 'B', 12);
				$pdf->Cell(0, 0,  'Valores Todo Acero' , 0, 0, $align = 'L', $fill = false);
				$pdf->Ln();
							
				foreach ( $comp_todoacero as $c ){
						
						$pdf->Ln();

						$pdf->Cell(160, 0, $c['COMP_TITULO'] ,1 , 0, $align = 'L', $fill = false);
						$puntaje= empty( $c['POEV_PUNTAJE'] ) ? ' ' : $c['POEV_PUNTAJE'] ;
						$pdf->Cell(0, 0, $puntaje ,1 , 0, $align = 'L', $fill = false);

				}
	
				$pdf->Ln();
				$pdf->Ln();
				$pdf->SetFont('helvetica', 'B', 12);
				$pdf->Cell(0, 0,  'Competencias laborales' , 0, 0, $align = 'L', $fill = false);
				$pdf->Ln();
						
				foreach ( $comp_blandas as $c ){
						
						$pdf->Ln();

						$pdf->Cell(160, 0, $c['COMP_TITULO'] ,1 , 0, $align = 'L', $fill = false);
						$puntaje= empty( $c['POEV_PUNTAJE'] ) ? ' ' : $c['POEV_PUNTAJE'] ;
						$pdf->Cell(0, 0, $puntaje ,1 , 0, $align = 'L', $fill = false);

				}
				
/*
				foreach ( $competencias as $c ){
						
						$pdf->Ln();

						$pdf->Cell(160, 0, $c['COMP_TITULO'] ,1 , 0, $align = 'L', $fill = false);
						$puntaje= empty( $c['POEV_PUNTAJE'] ) ? ' ' : $c['POEV_PUNTAJE'] ;
						$pdf->Cell(0, 0, $puntaje ,1 , 0, $align = 'L', $fill = false);

				}
 */
				
				$pdf->Ln();
				$pdf->Ln();

				$pdf->SetFont('helvetica', 'B', 12);
				$pdf->Cell(0, 0, "Examenes" , 0, 0, $align = 'L', $fill = false);
				$pdf->Ln();
				
				$pdf->SetFont('helvetica', 'N', 12);
				foreach ( (array)$examenes as $e ){
						
						$pdf->Ln();
						
						$pdf->Cell(160, 0, $e['REX_TITULO'] , 1, 0, $align = 'L', $fill = false);
						$pdf->Cell(0, 0, " " , 1, 0, $align = 'L', $fill = false);
				
				}
/*
				$html ='<ul style="">';

				foreach ( (array)$this->competencias as $c ){
						$html.='<li>'. $c->getTitulo() .'</li>';
				}        

				$html.='<ul>';

				$pdf->writeHTMLCell($w = 80, $h=0, $x=30, $y=80, $html, $border=1, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true);
				
				$html ='<ul style="">';

				foreach ( (array)$this->examenes as $e ){
						$html.='<li>'. $e->getTitulo() .'</li>';
				}        

				$html.='<ul>';
				
				$pdf->writeHTMLCell($w = 80, $h=0, $x=120, $y=80, $html, $border=1, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true);
	*/			
				//$pdf->writeHTML($html, true, false, true, false, '');


      // output the HTML content



				return $pdf;
		}


		
}
?>
