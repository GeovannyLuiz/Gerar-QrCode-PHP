<?php
error_reporting(0);
if($_POST['texto'] != ""){
$chars = array("(",")",",","-","","/",".",":");
$data = str_replace($chars,'',$_POST['texto']);
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
?>