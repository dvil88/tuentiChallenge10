<?php
/* Challenge 13 - The Great Toilet Paper Fortress
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new mercaFortress();

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



class mercaFortress{
	public function __construct(){
		$this->layerCache = [];
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
			$paperPacks = (int)trim(array_shift($input));

			$result = $this->buildFortress($paperPacks);

			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	protected function buildFortress($paperPacks){
		$minBlocks = 3 + (8 * (3 - 2)) + (16 * (3 - 1));
		if( $paperPacks < $minBlocks ){ return 'IMPOSSIBLE'; }

		// Initial config
		$this->maxHeight = 0;
		$this->maxPaperPacks = 0;
		$this->fortress = [
			'tower'=>[
				'width'=>1,
				'length'=>1
			],
			'walls'=>0,
		];

		$walls = 2;
		// Search in layer cache)
		krsort($this->layerCache);
		foreach( $this->layerCache as $blocks=>$cacheWalls ){
			if( $paperPacks > $cacheWalls ){
				$walls = $cacheWalls;
				break;
			}
			continue;
		}

		if(  $paperPacks / $walls > 100000 ){
			$max = 2000000;
			$min = $walls;
			$biggestMin = $min;
			$biggestMax = $max;
			do{
				$this->fortress['walls'] = $max;
				$maxBlocks = $this->calculateBlocks();

				$this->fortress['walls'] = $min;
				$minBlocks = $this->calculateBlocks();


				if( $maxBlocks > $paperPacks ){
					$biggestMax = $max;
				} else { $max = $biggestMax; }

				if( $minBlocks < $paperPacks ){
					$biggestMin = $min;
				} else { $min = $biggestMin; }

				if( $maxBlocks > $paperPacks && ($maxBlocks - $paperPacks) > ($paperPacks - $minBlocks) ){
					$max -= ceil(($max - $min) / 2);
				}

				if( $minBlocks < $paperPacks && ($maxBlocks - $paperPacks) < ($paperPacks - $minBlocks) ){
					$min += ceil(($max - $min) / 2);
				}


				if( $max % 2 != 0 ){ $max += ($max % 2); }
				if( $min % 2 != 0 ){ $min += ($min % 2); }


				if( $max - $min <= 2 ){ $startingWalls = (int)$min; break; }

				continue;
			} while(true);
			$walls = $startingWalls-2;

		}

		do{ 
			$this->fortress['walls'] = $walls;
			$totalBlocks = $this->calculateBlocks();

			if( !isset($this->layerCache[$totalBlocks] ) ){
				$this->layerCache[$totalBlocks] = $walls;
			}

			if( $totalBlocks > $paperPacks ){
				$this->fortress['walls'] -= 2;
				$this->maxPaperPacks = $this->calculateBlocks();
				$this->maxHeight = $this->fortress['tower']['height'];
				break;
			}

			$this->maxPaperPacks = $totalBlocks;
			$this->maxHeight = $this->fortress['tower']['height'];
		}while( $totalBlocks < $paperPacks && $walls += 2 );


		// Grow tower
		if( $paperPacks > $this->maxPaperPacks ){
			do{
				if( $this->fortress['tower']['width'] == $this->fortress['tower']['length'] ){
					$this->fortress['tower']['width']++;
				} else {
					$this->fortress['tower']['length']++;
				}

				$totalBlocks = $this->calculateBlocks();

				if( $totalBlocks > $paperPacks ){
					if( $this->fortress['tower']['width'] == $this->fortress['tower']['length'] ){
						$this->fortress['tower']['width']--;
					} else if( $this->fortress['tower']['width'] > $this->fortress['tower']['length'] ){
						$this->fortress['tower']['width']--;
					} else {
						$this->fortress['tower']['length']--;
					}

					$this->maxPaperPacks = $this->calculateBlocks();
					break;
				}

				$this->maxPaperPacks = $totalBlocks;
			} while( $totalBlocks < $paperPacks );
		}

		return $this->maxHeight.' '.$this->maxPaperPacks;
	}

	protected function calculateBlocks(){
		$noWalls = $this->fortress['walls'];

		$totalBlocks = 0;
		for( $wall = $noWalls; $wall > 0; $wall-- ){
			if( $wall % 2 == 0 ){
				$wallHeight = ( ( ($noWalls - $wall) - 4 ) / 2 ) + 4;
			} else {
				$wallHeight = ( ( ($noWalls - $wall + 1) - 4 ) / 2 ) + 2;
			}

			$wallBlocks = $wall * 8 * $wallHeight;

			$totalBlocks += $wallBlocks;
		}

		$towerHeight = ( ( $noWalls - 4 ) / 2 ) + 4;
		$this->fortress['tower']['height'] = $towerHeight;

		$towerBlocks = $towerHeight * $this->fortress['tower']['width'] * $this->fortress['tower']['length'];

		$totalBlocks += $towerBlocks;

		if( $this->fortress['tower']['width'] > 1 || $this->fortress['tower']['length'] > 1 ){
			// We have a tower bigger than 1x1
			$addingBlocks = 0;
			for( $wall = $noWalls; $wall > 0; $wall-- ){
				if( $wall % 2 == 0 ){
					$wallHeight = ( ( ($noWalls - $wall) - 4 ) / 2 ) + 4;
				} else {
					$wallHeight = ( ( ($noWalls - $wall + 1) - 4 ) / 2 ) + 2;
				}

				$wallBlocks = 0;
				if( $this->fortress['tower']['width'] > 1 ){
					$wallBlocks += 2 * $wallHeight * ($this->fortress['tower']['width'] - 1);
				}
				if( $this->fortress['tower']['length'] > 1 ){
					$wallBlocks += 2 * $wallHeight * ($this->fortress['tower']['length'] - 1);
				}

				$addingBlocks += $wallBlocks;
			}

			$totalBlocks += $addingBlocks;
		}

		return (int)$totalBlocks;
	}

}