<?php



//require_once('../vendor/autoload.php');

require_once('../servicios/TCPDF/tcpdf.php');



class PasaporteCovid19 extends TCPDF

{

    private $rut;

    private $nombre;

    private $fecha;

    private $estado;

    private $faena;



    function __construct($rut=0,$nombre="", $faena ="sin faena", $fecha=null,$estado='')

    {



      $this->rut = $rut;

      $this->nombre= $nombre;

      $this->faena= $faena;

      $this->estado= $estado;



      $this->fecha = (empty($fecha)) ? date('d-m-Y') : $fecha ;



      parent::__construct('P', 'mm', 'A6', true, 'UTF-8', false);

      $this->generar();

    }



    private function generar()

    {

      $this->SetCreator(PDF_CREATOR);

      $this->SetAuthor('Nicola Asuni');

      $this->SetTitle('TCPDF Example 050');

      $this->SetSubject('TCPDF Tutorial');

      $this->SetKeywords('TCPDF, PDF, example, test, guide');



      $this->SetPrintHeader(false);

      $this->SetPrintFooter(false);



      $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);



      $this->SetMargins(5, 5,5, 5);



      $this->SetAutoPageBreak(TRUE, 5);



      $this->setImageScale(PDF_IMAGE_SCALE_RATIO);



      $this->AddPage();



      $htitulo=25;

      $txt="PASAPORTE COVID-19\n".$this->estado;

      $this->SetFont('helvetica', 'B', 15);

      $this->SetTextColor(255,255,255);

      if($this->estado == "HABILITADO")
      {
        $this->SetFillColor(76, 175, 80);
      }else{
        $this->SetFillColor(224, 25, 41);
      }

      $this->MultiCell(0, 0, $txt, 0, 'C', 1, 0, '', '', true, 0, false, true, $htitulo, 'M',true);

      $this->Ln();

      $this->SetFillColor(255, 255, 255);

      $style = array(

          'border' => true,

          'padding' => 0,

          'fgcolor' => array(0,0,0),

          'bgcolor' => false

      );

      $widthqr = 42;

      $xqrini = $this->getPageWidth()/2 - $widthqr/2;

      $yqrini = $this->getY()+5;

      $qrtext =$this->rut.'||'.$this->nombre.'||'.$this->fecha.'||'.$this->faena.'||'.$this->estado;

      $this->write2DBarcode($qrtext, 'QRCODE,H', $xqrini, $yqrini, $widthqr, $widthqr, $style, 'N');



      $this->setY( $this->getY() + 5);



      $this->SetFont('helvetica', 'N', 12);

      $this->SetTextColor(102,102,102);

      $this->Cell(0, 0, $this->fecha , 0, 0, $align = 'C', $fill = true);

      $this->Ln();



      $this->SetFont('helvetica', 'B', 15);

      $this->SetTextColor(0,0,0);

      $this->Cell(0, 0, $this->nombre , 0, 0, $align = 'C', $fill = true);

      $this->Ln();



      $this->SetFont('helvetica', 'N', 15);

      $this->SetTextColor(0,0,0);

      $this->Cell(0, 0, $this->rut , 0, 0, $align = 'C', $fill = true);

      $this->Ln();



      $this->SetFont('helvetica', 'N', 12);

      $this->SetTextColor(102,102,102);

      $this->Cell(0, 0, $this->faena, 0, 0, $align = 'C', $fill = true);

      $this->Ln();

      $this->Ln();



      ///lista

      $this->SetFont('helvetica', 'N', 10);

      $this->SetTextColor(102,102,102);



      $html =

      '<ul style="">

          <li>Utiliza adecuadamente tu mascarilla</li>

          <li>Mantén distancia física</li>

          <li>Lava y desinfecta tus manos</li>

          <li>Cuídate y cuida a tu familia</li>

      <ul>';



      // output the HTML content

      $this->writeHTML($html, true, false, true, false, '');



      /*$this->setY( $this->getY() + 5);

      $txt="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.";

      $this->MultiCell(0, 0, $txt, 0, 'L', 1, 0, '', '', true, 0, false, true, 0, 'T',true);*/

    }

}



?>

