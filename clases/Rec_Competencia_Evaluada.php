<?php
require_once 'Rec_Competencia.php';

class Rec_Competencia_Evaluada extends Rec_Competencia
{

		private $evaluador_user_id;

		private $es_evaluada;

		private $puntaje_maximo;

		private $puntaje_minimo;

		private $puntaje_obtenido;

		public function __construct() {
		
		}

		/**** GETTERS  ***********/


		public function getEvaluadorId(){
				return $this->evaluador_user_id;
		}

		public function getPuntajeMaximo() { 
				return $this->puntaje_maximo; 
		}

		public function getPuntajeMinimo(){
				return $this->puntaje_minimo;
		}

		public function getPuntajeObetenido(){
				return $this->puntaje_obtenido;
		}

		public function getEsEvaluada(){
				return $this->es_evaluada === true ;
		}

		public function getPorcentajeObtenido(){

				$maximo = $this->puntaje_maximo;
				$obtenido = $this->puntaje_obtenido;

				try{
						
						$porcentaje = $obtenido*100/$maximo;
						return round( $porcentaje );
				}
				catch( Exception $e){
						return false;
				}
		}

		public function getCalcularAprobacion(){
				$minimo = $this->puntaje_minimo;
				$maximo = $this->puntaje_maximo;
				$obtenido = $this->puntaje_obtenido;

				return $minimo>=$obtenido && $obtenido<=$maximo;
		}
		/********* SETTERS   *************/

		public function setEvaluadorId( $user_id ){
				$this->evaluador_user_id = $user_id;
		}
		public function setEsEvaluada( $es_evaluada ){
				$this->es_evaluada = $es_evaluada == true;
		}

		public function setPuntajeMaximo( $puntos ){
				$this->puntaje_maximo = $puntos;
		}

		public function setPuntajeMinimo( $puntos ){
				$this->puntaje_minimo = $puntos;
		}

		public function setPuntajeObtenido( $puntos ){
				$this->puntaje_obtenido = $puntos;
		}

		
}
?>
