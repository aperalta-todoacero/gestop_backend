<?

class Empleado extends Persona
{
		protected $turno;
		protected $cargo;
		protected $estado_descripcion;
		protected $observacion;
		protected $desde_planilla;

		public function __construct( $rut, $nombre, $desde_planilla=0){

				parent::__construct( $rut, $nombre);

				$this->desde_planilla = $desde_planilla;

		}

		public function setTurno( $turno ){
				$this->turno = $turno;
		}

		public function setCargo( $cargo ){

				$this->cargo = $cargo;

		}

		public function setEstadoDescripcion( $estado_descripcion ){

				$this->estado_descripcion = $estado_descripcion;
		}


		public function setObservacion( $observacion ){

				$this->observacion = $observacion;
		}

		public function getTurno(){
				return $this->turno;
		}

		public function getCargo(){
				return $this->cargo;
		}

		public function getObservacion(){
				return $this->observacion;
		}

		public function getEstadoDescripcion(){
				return $this->estado_descripcion;
		}

		public function getDesdePlanilla(){
				return $this->desde_planilla;
		}
}
?>
