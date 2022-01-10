<?php 

require_once dirname(__DIR__).'/datos/Datos.php';




//require_once 'Rec_Postulante.php';

class Transporte
{
		
		private $patente;

		private $id;

		public function __construct( $patente ){

				$this->patente = $patente;

				$this->datos = new Datos();
		}

		/******** GETTERS  ****************/

		public function getId(){
				return $this->id;
		}
		public function getPatente(){

				return $this->patente;
		}

		/******** SETTERS  ************/
		
		public function setId( $id ){
				$this->id= $id;
		}

		public function setPatente( $patente ){
				$this->patente = $patente;
		}
	
	/*	
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
*/
		public function getPDF(){
		
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
