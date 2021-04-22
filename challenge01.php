<?php
/* Challenge 
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new lizardSpock();

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



class lizardSpock{
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
			$options = explode(' ', trim(array_shift($input)));

			$result = $this->play($options);

			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	protected function play($options){
		$options = array_unique($options);

		if( count($options) == 1 ){ return '-'; }

		// Scissors beat Paper
		if( in_array('S', $options) && in_array('P', $options) ){ return 'S'; }

		// Rock beat Scissors
		if( in_array('R', $options) && in_array('S', $options) ){ return 'R'; }

		// Paper beat Rock
		if( in_array('P', $options) && in_array('R', $options) ){ return 'P'; }
	}
}