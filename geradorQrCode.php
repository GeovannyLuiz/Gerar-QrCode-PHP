<?php
error_reporting(0);
if($_POST['texto'] != ""){
$chars = array("(",")",",","-","","/",".",":");
$data = str_replace($chars,'',$_POST['texto']);
	if(isset($_POST['mod11'])){
		if(is_numeric($data)){
			$data = $data.modulo_11($data);
		}else{
			echo json_encode(array('status' => false , 'msg' => 'Não foi possível gerar o QrCode pois os dados digitados não são numéricos, tente novamente ou desative o Módulo 11'));
			exit();
		}
	}
	if(file_exists(__DIR__.'/qrcodegerados/QrCode_'.$data.'.png')){
		header('Content-Type: application/json');
		echo json_encode(array('status' => false , 'msg' => 'QrCode já Gerado!!! Para gerar um NOVO, atualize sua página apertando (F5)'));
		exit();
	}

$size = '500x500';
$logo = "logotipo.jpg";
header('Content-type: image/png');
// Get QR Code image from Google Chart API
// http://code.google.com/apis/chart/infographics/docs/qr_codes.html
$QR = imagecreatefrompng('https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs='.$size.'&chl='.urlencode($data));
if($logo !== FALSE){
	$logo = imagecreatefromstring(file_get_contents($logo));

	$QR_width = imagesx($QR);
	$QR_height = imagesy($QR);
	
	$logo_width = imagesx($logo);
	$logo_height = imagesy($logo);
	
	// Scale logo to fit in the QR Code
	$logo_qr_width = $QR_width/3;
	$scale = $logo_width/$logo_qr_width;
	$logo_qr_height = $logo_height/$scale;
	
	imagecopyresampled($QR, $logo, $QR_width/3, $QR_height/3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
}

$targetPath = __DIR__."/qrcodegerados/";
array_map('unlink', glob($targetPath."*.png"));
if (! is_dir($targetPath)) {
mkdir($targetPath, 0777, true);
}
$targetPath = $targetPath."QrCode_".$data.".png";


imagepng($QR,$targetPath);
imagedestroy($QR,$targetPath);

header('Content-Type: application/json');
echo json_encode(array('status' => true , 'cod' => $data));
}else{
	header('Content-Type: application/json');
	echo json_encode(array('status' => false , 'msg' => 'Dados inválidos tente novamente....'));
}


function modulo_11($num, $base=9, $r=0)  {
    /**
     *   Autor:
     *           Pablo Costa <pablo@users.sourceforge.net>
     *
     *   Função:
     *    Calculo do Modulo 11 para geracao do digito verificador 
     *    de boletos bancarios conforme documentos obtidos 
     *    da Febraban - www.febraban.org.br 
     *
     *   Entrada:
     *     $num: string numérica para a qual se deseja calcularo digito verificador;
     *     $base: valor maximo de multiplicacao [2-$base]
     *     $r: quando especificado um devolve somente o resto
     *
     *   Saída:
     *     Retorna o Digito verificador.
     *
     *   Observações:
     *     - Script desenvolvido sem nenhum reaproveitamento de código pré existente.
     *     - Assume-se que a verificação do formato das variáveis de entrada é feita antes da execução deste script.
     */                                        

    $soma = 0;
    $fator = 2;

    /* Separacao dos numeros */
    for ($i = strlen($num); $i > 0; $i--) {
        // pega cada numero isoladamente
        $numeros[$i] = substr($num,$i-1,1);
        // Efetua multiplicacao do numero pelo falor
        $parcial[$i] = $numeros[$i] * $fator;
        // Soma dos digitos
        $soma += $parcial[$i];
        if ($fator == $base) {
            // restaura fator de multiplicacao para 2 
            $fator = 1;
        }
        $fator++;
    }

    /* Calculo do modulo 11 */
    if ($r == 0) {
        $soma *= 10;
        $digito = $soma % 11;
        if ($digito == 10) {
            $digito = 0;
        }
        return $digito;
    } elseif ($r == 1){
        $resto = $soma % 11;
        return $resto;
    }
}
?>