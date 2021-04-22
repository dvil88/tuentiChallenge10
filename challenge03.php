<?php
/* Challenge 3 - Fortunata and Jacinta
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new galdos();

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



class galdos{
	protected $frequency = [];
	protected $frequencyValues = [];

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
		$this->processBook();

		$testCases = trim(array_shift($input));
		$results = array();
		for( $case = 1; $case <= $testCases; $case++ ){
			$word  = trim(array_shift($input));

			$result = $this->searchWord($word);

			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	protected function processBook(){
		$bookText = file_get_contents('pg17013.txt');
		$bookText = str_replace("\n", ' ', $bookText);
		$bookText = mb_strtolower($bookText);

		$bookTextClean = preg_replace('/[^abcdefghijklmnñopqrstuvwxyzáéíóúü]/msiu', ' ', $bookText);
		$bookTextClean = preg_replace('/\s{2,}/msiu', ' ', $bookTextClean);


		$bookTextArray = explode(' ', $bookTextClean);
		$bookTextArray = array_diff($bookTextArray, ['']);

		$bookTextArrayClean = array_map(function($a){ return (mb_strlen($a) > 2 ? $a : '' ); } , $bookTextArray);
		$bookTextArrayClean = array_diff($bookTextArrayClean, ['']);


		$this->frequency = array_count_values($bookTextArrayClean);

		$freq = $this->frequency;
		uksort($this->frequency, function($a, $b) use ($freq){
			if( $freq[$a] == $freq[$b] ){
				// Same frequency, order alphabetically
				return $a > $b;
			} else {
				return $freq[$a] < $freq[$b];
			}
		});

		$this->frequencyValues = array_values(array_keys($this->frequency));
	}

	protected function searchWord($word){
		if( is_numeric($word) ){
			// Search number
			$word = $this->frequencyValues[$word - 1];
			$score = $this->frequency[$word];

			return $word.' '.$score;
		}

		// Search text
		$score = $this->frequency[$word];

		// search position in array
		$ranking = array_keys($this->frequencyValues, $word)[0] + 1;

		return $score.' #'.$ranking;
	}
}