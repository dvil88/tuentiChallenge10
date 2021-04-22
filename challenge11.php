<?php
/* Challenge 11 - All the Possibilities
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new sumatum();

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



class sumatum{
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
		// file_put_contents('submitResult.txt',implode(PHP_EOL,$results));
	}

	protected function getResults($input){
		$testCases = trim(array_shift($input));
		$results = array();
		for( $case = 1; $case <= $testCases; $case++ ){
			$numbers = explode(' ', trim(array_shift($input)));

			$target = array_shift($numbers);
			$forbiddenNumbers = $numbers;
if( $case != 1 ){continue;}
			$result = $this->calculateSums($target, $forbiddenNumbers);

			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	protected function calculateSums($target, $forbiddenNumbers){
		$blocks  = array_fill_keys($forbiddenNumbers, true);
		$sums = 0;
		$this->startTime = microtime(1);
		$this->findCombinationsUtil($blocks, $sums, [], 0, $target, $target);

		return $sums;

	}

	protected function findCombinationsUtil($forbidden, &$sum, $arr, $index, $num, $reducedNum){ 
		if( $reducedNum == 0 ){ 
			$sum++;
			if( $sum % 1000000 == 0){
				echo $sum.' - '.(microtime(1) - $this->startTime).PHP_EOL;
			}

			return; 
		} 
	  
		$prev = ($index == 0) ? 1 : $arr[$index - 1]; 
	  
	  	for( $k = $num-1; $k >= $prev; $k-- ){
			if( isset($forbidden[$k]) ){ continue; }

			$arr[$index] = $k; 
	  		if( $reducedNum - $k < 0 ){ continue; }
		  
			$this->findCombinationsUtil($forbidden, $sum, $arr, $index + 1, $num, $reducedNum - $k); 
		}
	}
}