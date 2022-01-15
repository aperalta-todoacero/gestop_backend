<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

//require_once('../clases/Usuario.php');

//use \Firebase\JWT\JWT;
/*
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

$inputFile = 'FORMATO CARGA MASIVA EXAMENES.xlsx';

$objPHPExcel = PHPExcel_IOFactory::load($inputFile);

//  Get worksheet dimensions
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();


$json = array();

for ($row = 2; $row <= $highestRow; $row++){
  $fecha= $sheet->getCell('D'.$row)->getValue();
  if(!empty($fecha)){
    $fecha = PHPExcel_Shared_Date::ExcelToPHP( $fecha );
    $fecha = date('d-m-Y', $fecha);
  }else{
    $fecha = '';
  }

    $fila = array(
      'rut' => $sheet->getCell('A'.$row)->getValue(),
      'nombre' => $sheet->getCell('B'.$row)->getValue(),
      'tipo' => $sheet->getCell('C'.$row)->getValue(),
      'fecha' => $fecha,
      'faena' => $sheet->getCell('E'.$row)->getValue(),
      'observacion' => $sheet->getCell('F'.$row)->getValue(),
      'cuarentena' => $sheet->getCell('G'.$row)->getValue()
    );
    array_push( $json, $fila );
}

echo json_encode($json);
*/



//$token="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJnZXN0b3B3ZWIiLCJzdWIiOiJqZ3Vhc2NoIiwicnV0IjoiMTU5MjQ2OTMiLCJpYXQiOjE2MTQ3MTY3NDQsIm5iZiI6MTYxNDcxNjc0NCwiZXhwIjoxNjE0NzI3NTQ0fQ.xUZAP9Wt0iKmq9DdsVso8pmm-AnTmuElCk1FWIKmj7w";

//$Usuario = SSOMA::LoginToken($token);
//var_dump( $Usuario );
//$rut = 15924693;


//$SSOMA = SSOMA::LoginInvitado( $rut );
/*if( $SSOMA = SSOMA::LoginPass( 'jguasch', 15924693 ) ){
  echo "if:";
  print_r($SSOMA->getCasosCovid() );
}else{
  echo "else:";
  var_dump( $SSOMA );
}*/
/*
$key = date('dmY');//debe invalidarse a final del dia
  
  $time = time();

  $exp = $time + 60*60*3;


  $payload = array(
      "iss" => "gestopweb",
      "sub" => 'holi',
      "iat" => $time, //emitido
      "nbf" => $time, //no usar antes de
      "exp" => $exp //no usar antes de
  );


  $jwt = JWT::encode($payload, $key);

  $decoded = JWT::decode( $jwt, $key, array('HS256'));

echo "holanda";
  var_dump($decoded->iss);
 */

/*
$path = __DIR__ . '/php-graph-sdk-5.x/src/Facebook';
echo 'este es el tiempo: '.time().'<br>';
echo  $path.'<br>';

require_once $path.'/autoload.php';
require_once $path.'/Facebook.php';
require_once $path.'/FacebookApp.php';
require_once $path.'/FacebookBatchRequest.php';
require_once $path.'/FacebookBatchResponse.php';
require_once $path.'/FacebookClient.php';
require_once $path.'/FacebookRequest.php';
require_once $path.'/FacebookResponse.php';

$fb = new \Facebook\Facebook([
  'app_id' => '407483357783501',
  'app_secret' => 'fda38cf0f103b6af735cec796871cd0c',
  'default_graph_version' => 'v2.10',
  //'default_access_token' => '{access-token}', // optional
]);

// Use one of the helper classes to get a Facebook\Authentication\AccessToken entity.
//   $helper = $fb->getRedirectLoginHelper();
//   $helper = $fb->getJavaScriptHelper();
//   $helper = $fb->getCanvasHelper();
//   $helper = $fb->getPageTabHelper();

  $response = $fb->get('/me', '8f90dcbfcb48c1c81dd33837cc344f41');
try {
  // Get the \Facebook\GraphNodes\GraphUser object for the current user.
  // If you provided a 'default_access_token', the '{access-token}' is optional.
  $response = $fb->get('/me', '8f90dcbfcb48c1c81dd33837cc344f41');
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$me = $response->getGraphUser();
echo 'Logged in as ' . $me->getName();
 */
/*
$path = __DIR__ . '/vendor/autoload.php';
require_once $path;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;

$app_id = "407483357783501";
$app_secret = "fda38cf0f103b6af735cec796871cd0c";
$access_token = "EAAFympzK5c0BAKY8CcXrZCn3CvZB1CN929ItMCZCw8FeVe6OwKo1ZAgDEyruMK394kcWMhLJB3VbsS6TNeuAoZCkFCuXoBW9HsxkctEYgwfeCXZBPtT9IxRlKJ9wjZAzqxQbC5veoK0kJDzqrF6pafbFho3fmaIcZAyqChligobI9g4bfrBwpQ7fqKIoibLKQ1v9NYpZBi14MoKYwHg4RZAWGZB";
$account_id = "act_100076352662692";

Api::init($app_id, $app_secret, $access_token);

$account = new AdAccount($account_id);
$cursor = $account->getCampaigns();

// Loop over objects
foreach ($cursor as $campaign) {
  echo $campaign->{CampaignFields::NAME}.PHP_EOL;
}
*/
/*
 https://www.daniloaz.com/es/como-obtener-un-token-o-identificador-de-acceso-permanente-a-una-pagina-de-facebook/#:~:text=3.1%20Ir%20al%20Explorador%20de,duraci%C3%B3n%20(short_lived_token)%3A%202%20horas.
sera necesario buscar un token permanente
https://graph.facebook.com/v12.0/oauth/access_token?grant_type=fb_exchange_token&client_id=407483357783501&client_secret={app_secret}&fb_exchange_token=EAAFympzK5c0BAFzlfV3yb2UVG2SuzXPBVI9E3FPrIUSU4G4JqCrVMmvnZASkVqv7wGkg52Wm9vRgFZCAUcyVbp097I8IGeXs4FC4MUZCyBD4hYiuzusMspe6eUZCzpwtKa0lvZBIJg4qfWa0bw4nQwe2dcDR6RijrfWO6JoqZCytjGfAbZAq6RIQ8vXPuTZB0Ae7ZAMZCvHwc4YeBml0H99qYe

token larga vida:
EAAFympzK5c0BAPK2QaHdMIftuFxIvEc56qXqDHFaxTsfnZB1tNSznquWXZBQX0vJ0GMj5i3ZCotpnP6geZBxkNZCedIMOaQ3oDWAxpoasiTHu4Ya5DnJsYEIEZCUv20q1Xm4mpD89dOejhg40Bgsl4eEkWBmUZC7PphoZAp2noanKAZDZD

token permanente:
https://graph.facebook.com/{page_id}?fields=access_token&access_token={long_lived_token}

https://graph.facebook.com/442410452618970?fields=access_token&access_token=EAAFympzK5c0BAPK2QaHdMIftuFxIvEc56qXqDHFaxTsfnZB1tNSznquWXZBQX0vJ0GMj5i3ZCotpnP6geZBxkNZCedIMOaQ3oDWAxpoasiTHu4Ya5DnJsYEIEZCUv20q1Xm4mpD89dOejhg40Bgsl4eEkWBmUZC7PphoZAp2noanKAZDZD

{
   "access_token": "EAAFympzK5c0BANqEUba7cXBAqANiPJkK4Ruf4nne7nBecUhzlgp5MPS5aKHQVFEbdM32DtDshEetLNjVlTjZArXTLR6VZBuzOcnB9Eh86h2v3b01zVNyv3AfQOXZBxtNp1HX9eDGhzdOGNhN5uCJBCcIcbSrNSeSCJRpjKARM39l05pIiOV",
   "id": "442410452618970"
}
 */
/*
 curl -i -X POST \
 "https://graph.facebook.com/v12.0/442410452618970/feed?message=posteando%20desde%20fuera&access_token=EAAFympzK5c0BALQdGVUYz8278VCyJ0zgzWpHsb9MP5Y1WaiEJVk9yh7jntBOhtG2tP0edrc99GtsWwkvr2xZBppwtR46TaQAq5ZCmFqCHaO9ZBfj4VVAkQLyqyFdA7POsL32Wku9CaBq9sRI2XaSKg39VQZBgIKa6AcH3oleHDe0QFliNuBByyrjY9yhGA8L5F7NukEizwZDZD&posteo=esto%20esd%20una%20prueba"
 */
//$token_acceso="EAAFympzK5c0BALQdGVUYz8278VCyJ0zgzWpHsb9MP5Y1WaiEJVk9yh7jntBOhtG2tP0edrc99GtsWwkvr2xZBppwtR46TaQAq5ZCmFqCHaO9ZBfj4VVAkQLyqyFdA7POsL32Wku9CaBq9sRI2XaSKg39VQZBgIKa6AcH3oleHDe0QFliNuBByyrjY9yhGA8L5F7NukEizwZDZD";


#posteo
$token_acceso="EAAFympzK5c0BANqEUba7cXBAqANiPJkK4Ruf4nne7nBecUhzlgp5MPS5aKHQVFEbdM32DtDshEetLNjVlTjZArXTLR6VZBuzOcnB9Eh86h2v3b01zVNyv3AfQOXZBxtNp1HX9eDGhzdOGNhN5uCJBCcIcbSrNSeSCJRpjKARM39l05pIiOV";
$mensaje="titulo del mensaje 2
		cuerpo del mensaje extenso para prpbar como se veria y si es que considera los saltos de linea o los ajusta solo.
Competencias requeridas:
  - Coffee
  - Tea
  - Milk
";

//pagina_id y token de acceso a pagina

//$graph_url ="https://graph.facebook.com/v12.0/442410452618970/feed";
/*$post_data ="message=".$mensaje
						."&link=https://reclutamiento.todoacero.cl"
						."&url=https://todoacero.cl/images/logo.jpg"
						."&access_token=".$token_acceso;
*/
#imagen
/*
$graph_url ="https://graph.facebook.com/v12.0/442410452618970/photos";
$post_data ="url=https://todoacero.cl/images/logo.jpg"
						."&message=este es una prueba https://reclutamiento.todoacero.cl"
						."&access_token=".$token_acceso;

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $graph_url);

curl_setopt($ch, CURLOPT_HEADER, 0);

curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$output = curl_exec($ch);

curl_close($ch);

var_dump($output);
 */

$cargos = array('ingeniero comercial','maestro segunda calderero','ingeniero en gestion de prevencion de riesgos','profesor de educaion basica');
$competencias = array('competencia 1','competencia 2','competencia 3','competencia 4','competencia 5','competencia 6','competencia 7','competencia 8','competencia 9','competencia 10','competencia 11','competencia 12','competencia 13','competencia 14','competencia 15');

$descripcion = "Esta es la descripcion principal para testear la separacion de las lineas si un texto es muy extenso.";
$descripcion = ucfirst(strtolower($descripcion));

$faena= "maestranza";
$turno= "4x4";

$imagen_dir = __DIR__."/imagenes/publicacion_tipo_3.png";
$fuente_dir_bold = __DIR__."/imagenes/LiberationSans-Bold.ttf";
$fuente_dir_regular = __DIR__."/imagenes/LiberationSans-Regular.ttf";

$simple = false;

$imagen = imagecreatefrompng ($imagen_dir);
$imagen_tamano = getimagesize($imagen_dir);
$w_imagen = imagesx($imagen);

$color_blanco = imagecolorallocate ($imagen, 255, 255, 255);
$color_gris = imagecolorallocate ($imagen, 177, 177, 177);
$color = imagecolorallocate ($imagen, 0, 0, 0);
$h_texto = 30;
$angulo = 0;


#descripcion o texto principal


		$lineas = getLineas( strtoupper($cargos[2]), $h_texto, $fuente_dir_bold , $w_imagen, $mi = 20, $md = 60,  $angulo = 0);

		$x = 20;

		if(  count($lineas)==1 ){
	
				$h_texto = 30;

				$y = 590;
		
			}else{
		
				$y = 575;
				
				$h_texto = 17;

			}

		//$h_texto = ($multiline===true)? 17 : $h_texto;

		$lineas = getLineas( strtoupper($cargos[2]), $h_texto, $fuente_dir_bold , $w_imagen, $mi, $md, $angulo = 0);

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

		$ultimo = array_pop($compt_tmp);
		$comp = implode(", ", $compt_tmp).' y '.$ultimo;

		$txt = $comp;

		$lineas = getLineas( $txt, $h_texto = 17, $fuente_dir_regular , $w_imagen, $mi = 15, $md = 45,  $angulo = 0);
		
		if( count($lineas) > 5 ){
	
				$ultimo = array_pop($compt_tmp);
			
				$comp = implode(", ", $compt_tmp).' y '.$ultimo;

				
				$txt = $comp;

				
				$lineas = getLineas( $txt, $h_texto = 17, $fuente_dir_regular , $w_imagen, $mi = 15, $md = 45,  $angulo = 0);
		
		}

		$y = 700;

		$x = 10 + $mi;

		$i=0;

		foreach($lineas as $linea){
						
						$coordenadas = imagettfbbox($h_texto, $angulo,$fuente_dir_regular, $linea );
						
						$y += abs($coordenadas[7] - $coordenadas[1]);

						$y+= 7;

						imagettftext($imagen, $h_texto, $angulo, $x , $y , $color_blanco ,$fuente_dir_regular , $linea );
				
						$i++;

		}

		unset($linea);

		unset($comp);


#footer
imagettftext($imagen, $tamano = 16, $angulo, $x=100, $y=890, $color_gris,$fuente_dir_regular,$texto="Interesados, postular en nuestra plataforma");

imagettftext($imagen, $tamano=20, $angulo, $x=100, $y+=25, $color,$fuente_dir_bold,$texto="reclutamiento.todoacero.cl");

header ("Content-type: image/png");
imagepng ($imagen);
imagedestroy($imagen);



function getLineas( $texto, $h_texto, $fuente , $w_imagen, $ml = 0, $mi = 0,  $angulo = 0){

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

?>
