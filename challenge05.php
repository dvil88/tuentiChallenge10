<?php
/* Challenge 5 - Tuentistic Numbers
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new tuentistic();

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



class tuentistic{
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
			$number = trim(array_shift($input));

			$result = $this->calculate($number);

			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	protected function calculate($number){
		$maxSlots = gmp_div($number, 20, GMP_ROUND_MINUSINF);

		// Spare numbers
		$spareNumbers = gmp_mod($number, 20);

		$freeSlots = gmp_mul(9, $maxSlots);

		if( $freeSlots >= $spareNumbers ){ 
			return $maxSlots;
		}

		return 'IMPOSSIBLE';
	}
}