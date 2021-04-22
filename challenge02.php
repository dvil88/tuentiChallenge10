<?php
/* Challenge 2 - The Lucky One
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new pingPong();

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



class pingPong{
	protected $players = [];

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
			$matchesNum = trim(array_shift($input));


			$elo = new Elo();
			$elo->setFactor(16);
			$this->players = [];
			for( $match = 0; $match < $matchesNum; $match++ ){
				list($player1, $player2, $winner) = explode(' ', trim(array_shift($input)));
				if( !isset($this->players[$player1]) ){ $this->players[$player1] = 1000000; }
				if( !isset($this->players[$player2]) ){ $this->players[$player2] = 1000000; }

				$elo->new_rating(
					$this->players[$player1], $this->players[$player2], 
					$winner, (int)!$winner
				);
				list($this->players[$player1], $this->players[$player2]) = $elo->new_rating_get();
			}

			$result = $this->predictWinner();
			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	protected function predictWinner(){
		return array_keys($this->players, max($this->players))[0];
	}
}

class Elo{
	const KFACTOR = 16;

	protected $kfactor;
	protected $rating_a;
	protected $rating_b;
	protected $score_a;
	protected $score_b;
	protected $expected_a;
	protected $expected_b;
	protected $new_rating_a;
	protected $new_rating_b;

	public function new_rating($rating_a, $rating_b, $score_a, $score_b){
		$this->rating_a = $rating_a;
		$this->rating_b = $rating_b;

		$this->score_a = $score_a;
		$this->score_b = $score_b;

		list($this->expected_a, $this->expected_b) = $this->_expected_scores_get();
		list($this->new_rating_a, $this->new_rating_b) = $this->_new_ratings_get();
	}

	public function setFactor($factor){
		$this->kfactor = $factor;
	}

	public function new_rating_get(){
		return array (
			$this->new_rating_a,
			$this->new_rating_b
		);
	}

	protected function _expected_scores_get(){
		$expected_score_a = 1 / (1 + (pow(10, ($this->rating_b - $this->rating_a) / 400)));
		$expected_score_b = 1 / (1 + (pow(10, ($this->rating_a - $this->rating_b) / 400)));

		return array (
			$expected_score_a,
			$expected_score_b
		);
	}

	protected function _new_ratings_get(){
		$new_rating_a = $this->rating_a + ($this->kfactor * ($this->score_a - $this->expected_a));
		$new_rating_b = $this->rating_b + ($this->kfactor * ($this->score_b - $this->expected_b));

		return array (
			$new_rating_a,
			$new_rating_b
		);
	}
}