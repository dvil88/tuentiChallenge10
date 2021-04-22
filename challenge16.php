<?php
/* Challenge 16 - The new office
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

if( $argc == 2 ){
	$t = new theOffice();

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



class theOffice{
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
			list($this->noFloors, $this->noGroups) = explode(' ', trim(array_shift($input)));
			for( $group = 0; $group < $this->noGroups; $group++ ){
				$groupKey = 'group_'.str_pad($group, 3, '0', STR_PAD_LEFT);

				list($this->employees[$groupKey], $this->floorsAccess[$groupKey]) = explode(' ', trim(array_shift($input)));

				$this->groupFloors[$groupKey] = array_map(function($a){ return 'floor_'.str_pad($a, 3, '0', STR_PAD_LEFT); }, explode(' ', trim(array_shift($input))));
			}

			$result = $this->buildBaths();

			$results[] = 'Case #'.$case.': '.$result;
		}

		return $results;
	}

	protected function buildBaths(){
		$employeeFloorRatio = [];
		$floorsGroups = [];
		$totalFloorsAccess = [];
		$this->minBathsInit = false;


		$forbidden = [
			
		];

		$prevGap = false;
		while( true ){
			$whileTotalFloorsAccess = [];

			foreach( $forbidden as $group=>$floor ){
				$this->groupFloors[$group] = array_diff($this->groupFloors[$group], $forbidden[$group]);
			}

			$this->ratiosFloorsGroups = [];
			$this->ratiosGroupsFloors = [];
			foreach( $this->employees as $group=>$employees ){
				$employeeFloorRatio[$group] = $employees / count($this->groupFloors[$group]);

				foreach( $this->groupFloors[$group] as $floor ){
					$ratio = $employees / count($this->groupFloors[$group]);
					$this->ratiosFloorsGroups[$floor][$group] = $ratio; 
					$this->ratiosGroupsFloors[$group][$floor] = $ratio; 

					$floorsGroups[$group][$floor] = $employees;
				}
			}


			ksort($this->ratiosFloorsGroups);
			ksort($this->ratiosGroupsFloors);
			$this->minBaths = max($employeeFloorRatio);
			if( $this->minBathsInit === false ){
				$this->minBathsInit = $this->minBaths;
			}

			$groupMaxRatio = array_keys($employeeFloorRatio, max($employeeFloorRatio))[0];

			$floorSum = array_map('array_sum', $this->ratiosFloorsGroups);


			if( max($floorSum) <= $this->minBaths ){ break; }

			// Select floor with max ratio that doesn't have this->minBaths
			list($groupsIntersect, $maxRatioFloor) = $this->selectMaxMinFloors($floorSum);

			if( !$groupsIntersect ){ break; }

			$maxGroupRatioIntersect = array_keys($groupsIntersect, max($groupsIntersect))[0];
			$forbidden[$maxGroupRatioIntersect][] = $maxRatioFloor;

			$min = array_keys($floorSum, min($floorSum))[0];
			$max = array_keys($floorSum, max($floorSum))[0];
			if( $floorSum[$max] - $floorSum[$min] <= 1 ){
				break;
			}
		}

		// $this->showTable($this->ratiosGroupsFloors);

		$baths = $this->getMinBaths($floorSum);
		return $baths;
	}

	protected function selectMaxMinFloors($floors){
		$floorsMax = $floors;
		$floorsMin = $floors;

		asort($floorsMax);
		arsort($floorsMin);

		foreach( $floorsMax as $floorMax=>$ratioMax ){
			foreach( $floorsMin as $floorMin=>$ratioMin ){
				if( $floorMax == $floorMin ){ continue; }
				$groupsIntersect = array_intersect_key($this->ratiosFloorsGroups[$floorMax], $this->ratiosFloorsGroups[$floorMin]);

				if( $groupsIntersect ){
					return [$groupsIntersect, $floorMin];
				}
			}
		}

		return [false, false];

	}

	protected function getMinBaths($floorSum){
		$max = max($floorSum);
		$min = min($floorSum);
		$median = $this->calculate_median($floorSum);
		if( $max == $min ){ return $max; }

		if( $median < $this->minBathsInit ){
			return ceil($max);
		}
		return round($this->calculate_median($floorSum), 0);

	}

	protected function showTable($floorsGroups){
		ksort($floorsGroups);
		foreach( $floorsGroups as $group=>$floors ){
			// echo $group.' ';
			for( $f = 0; $f < $this->noFloors; $f++ ){
				if( !isset($floors['floor_'.str_pad($f, 3, '0', STR_PAD_LEFT)]) ){
					echo '0 ';
				} else {
					echo str_replace('.',',',$floors['floor_'.str_pad($f, 3, '0', STR_PAD_LEFT)]).' ';
				}
			}
			echo PHP_EOL;
		}
	}
	
	protected function calculate_median($arr) {
		$arr = array_values($arr);
		$count = count($arr);
		$middleval = floor(($count - 1) / 2);
		if( $count % 2 ){
			$median = $arr[$middleval];
		} else {
			$low = $arr[$middleval];
			$high = $arr[$middleval + 1];
			$median = (($low + $high) / 2);
		}
		return $median;
	}
}