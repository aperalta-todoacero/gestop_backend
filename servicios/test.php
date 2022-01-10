<?php
/*
require_once '../clases/Rec_Perfil_Evaluado.php';
$perfil = new Rec_Perfil_Evaluado();
$perfil->setId(57);
$perfil->setCompetencias();
$perfil->setExamenes();
$pdf = $perfil->getPDF();
$pdf->Output('test.pdf', 'D');
 */

$fp = fopen('data.txt', 'a');//opens file in append mode  
fwrite($fp, date('Y-m-d H:i'));  
fclose($fp);  
?>

