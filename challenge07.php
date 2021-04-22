<?php
/* Challenge 7 - Encrypted lines
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new dvorak();

	switch($argv[1]){
		case 'sample':
			$t->sample();
			break;
		case 'test':
			$t->test();
			break;
		case 'submit':
			$t->submit();
			break;
		default:
			$t->sample();
			$t->test();
			$t->submit();
			break;
	}
} else {
	echo 'ERROR'.PHP_EOL;
}



class dvorak{
	public function __construct(){
	}

	public function sample(){
		$input = file('sampleInput');
		$results = $this->getResults($input);
		echo implode("\n", $results).PHP_EOL;
		file_put_contents('sampleResult.txt',implode(PHP_EOL,$results));
	}

	public function test(){
		$input = file('testInput');
		$results = $this->getResults($input);
		echo implode("\n", $results).PHP_EOL;
		file_put_contents('testResult.txt',implode(PHP_EOL,$results));
	}

	public function submit(){
		$input = file('submitInput');
		$results = $this->getResults($input);
		echo implode("\n", $results).PHP_EOL;
		file_put_contents('submitResult.txt',implode(PHP_EOL,$results));
	}

	protected function getResults($input){
		$testCases = trim(array_shift($input));
		$results = array();
		for( $case = 1; $case <= $testCases; $case++ ){
			$text = array_shift($input);
			$text = str_replace("\n", '', $text);

			$result = $this->toQwerty($text);

			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	function toQwerty($encrypted ){
		$qwerty = "-=qwertyuiop[]asdfghjkl;'zxcvbnm,./_+QWERTYUIOP{}ASDFGHJKL:\"ZXCVBNM<>?";
		$dvorak = "[]',.pyfgcrl/=aoeuidhtns-;qjkxbmwvz{}\"<>PYFGCRL?+AOEUIDHTNS_:QJKXBMWVZ";

		$text = '';
		for( $i=0; $i< strlen($encrypted); $i++ ){
			if( strpos($dvorak, $encrypted[$i]) !== false ){
				$text .= $qwerty[strpos($dvorak, $encrypted[$i])];
			} else {
				$text .= $encrypted[$i];
			}
		}

		return $text;
	}
}

