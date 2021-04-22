<?php

$encrypted = '3633363A33353B393038383C363236333635313A353336';
$decrypted = '514;248;980;347;145;332';

$messageToDecrypt = '3A3A333A333137393D39313C3C3634333431353A37363D';

$key = decryptKey($encrypted, $decrypted);
echo 'Decrypted key: '.$key.PHP_EOL;


// decrypt($key, $encrypted);
$coordinates = decrypt($key, $messageToDecrypt);
echo 'Coordinates: '.$coordinates.PHP_EOL;


file_put_contents('testResult.txt', $coordinates);
file_put_contents('submitResult.txt', $coordinates);

function encrypt($key, $msg){
	$crpt_msg = "";

	# for ((i=0; i<1 i++)); do
	for( $i=0; $i < strlen($msg); $i++ ){
		# echo $i
		$c = $msg[$i];
		echo 'c = '.$c.PHP_EOL;

		$asc_chr = ord($c);
		echo 'asc_chr = '.$asc_chr.PHP_EOL;
		
		$key_pos = strlen($key) - 1 - $i;
		echo 'key_pos = '.$key_pos.PHP_EOL;
		
		$key_char = $key[$key_pos];
		echo 'key_char = '.$key_char.PHP_EOL;

		$crpt_chr = $asc_chr ^ $key_char;
		echo 'crpt_chr = '.$crpt_chr.PHP_EOL;

		$hx_crpt_chr = dechex($crpt_chr);
		echo 'hx_crpt_chr = '.$hx_crpt_chr.PHP_EOL;
		
		$crpt_msg .= $hx_crpt_chr;
		// echo crpt_msg = $crpt_msg
		
		printf("\n");
	}

	echo $crpt_msg.PHP_EOL;
}

function decrypt($key, $msg){
	$key = strrev($key);
	$message = '';

	# for ((i=0; i<1 i++)); do
	for( $i=0,$j=0; $i < strlen($msg); $i+=2,$j++ ){
		$hx_crpt_chr = substr($msg, $i, 2);

		$crpt_chr = hexdec($hx_crpt_chr);

		$key_char = $key[$j];

		$asc_chr = $key_char ^ $crpt_chr;

		$c = chr($asc_chr);
		
		$message .= $c;
	}

	return $message;
}

function decryptKey($enc, $dec){
	$decryptedKey = '';
	for( $i=0; $i < strlen($dec); $i++ ){
		$c = $dec[$i];

		$asc_chr = ord($c);
		$hx_crpt_chr = substr($enc, $i*2, 2);
		$crpt_chr = hexdec($hx_crpt_chr);
		$key_char = $asc_chr ^ $crpt_chr;
		$decryptedKey .= $key_char;
	}

	return strrev($decryptedKey);
}