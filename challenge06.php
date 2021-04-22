<?php
/* Challenge 6 - Knight Labyrinth
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

$t = new knightPrincess();
$t->generateMap(['row'=>110, 'col'=>110]);


class knightPrincess{
	protected $map = [];
	protected $mapPositions;
	protected $startingPoint = [];
	protected $castle = [];
	protected $peach = [];

	protected $minJumpsMap = [];
	protected $minJumps = false;


	public function __construct(){
		$this->connectSocket();
	}

	public function generateMap($position, $prevMovements = []){
		$read = array($this->socket, STDIN);
		$write  = NULL;
		$except = NULL;

		if( !is_resource($this->socket) ){ return; }
		$num_changed_streams = @stream_select($read, $write, $except, null);
		if( feof($this->socket) ){ return; }


		if( false === $num_changed_streams ){
			/* Error handling */
			die;
		} elseif ($num_changed_streams > 0) {
			$data = trim(fread($this->socket, 4096));
			$map = array_slice(explode("\n", $data), 0, 5);

			$portionRow = $position['row'];

			foreach( $map as $row ){
				$cols = str_split($row);

				$portionCol = $position['col'];

				foreach( $cols as $block ){
					if( $block == 'K' ){
						$this->startingPoint = ['row'=>$portionRow,'col'=>$portionCol];
						$this->map[$portionRow][$portionCol] = '.';
					} elseif( $block == 'P' ){
						$this->princess = ['row'=>$portionRow,'col'=>$portionCol];
						$this->map[$portionRow][$portionCol] = '.';
					} else {
						$this->map[$portionRow][$portionCol] = $block;
					}
					$portionCol++;
				}
				$portionRow++;
			}

			$pointStr = $this->startingPoint['row'].'#'.$this->startingPoint['col'];
			$this->minJumpsMap[$pointStr] = true;

			// Get possible movements
			$movements = $this->getPossibleMovements($this->startingPoint);
			if( !$movements ){
				$prevMovement = array_pop($prevMovements);

				if( strpos($prevMovement['string'], 'u') !== false ){
					$prevMovement['string'] = str_replace('u', 'd', $prevMovement['string']);
				} else if( strpos($prevMovement['string'], 'd') !== false ){
					$prevMovement['string'] = str_replace('d', 'u', $prevMovement['string']);
				}

				if( strpos($prevMovement['string'], 'r') !== false ){
					$prevMovement['string'] = str_replace('r', 'l', $prevMovement['string']);
				} else if( strpos($prevMovement['string'], 'l') !== false ){
					$prevMovement['string'] = str_replace('l', 'r', $prevMovement['string']);
				}



				$movement = $prevMovement['string'];
				$newPosition = ['row'=>$prevMovement['row'] - 2, 'col'=>$prevMovement['col'] - 2];
				fwrite($this->socket, $movement.PHP_EOL);

				return $this->generateMap($newPosition, $prevMovements);


			}
			while( $nextMovement = array_shift($movements) ){
				$pointStr = $nextMovement['row'].'#'.$nextMovement['col'];

				if( $pointStr == implode('#', $this->princess) ){ 
					$movement = $nextMovement['string'];
					$newPosition = ['row'=>$nextMovement['row'] - 2, 'col'=>$nextMovement['col'] - 2];

					fwrite($this->socket, $movement.PHP_EOL);
				
					$read = array($this->socket, STDIN);
					$write  = NULL;
					$except = NULL;

					if( !is_resource($this->socket) ){ return; }
					$num_changed_streams = @stream_select($read, $write, $except, null);
					if( feof($this->socket) ){ return; }


					if( false === $num_changed_streams ){
						/* Error handling */
						die;
					} elseif ($num_changed_streams > 0) {
						$data = trim(fread($this->socket, 4096));
						preg_match('/^--- Secret key: (?<key>.*?) ---$/', $data, $key);
						
						file_put_contents('testResult.txt', $key['key']);
						file_put_contents('submitResult.txt', $key['key']);
					}

					break;
				}

				if( isset($this->minJumpsMap[$pointStr]) ){ continue; }
				$this->minJumpsMap[$pointStr] = true;

				$movement = $nextMovement['string'];
				$newPosition = ['row'=>$nextMovement['row'] - 2, 'col'=>$nextMovement['col'] - 2];

				fwrite($this->socket, $movement.PHP_EOL);

				$prevMovementsTemp = $prevMovements;
				$prevMovementsTemp[] = array_merge($this->startingPoint, ['string'=>$movement]);

				return $this->generateMap($newPosition, $prevMovementsTemp);
			}
		}
	}

	protected function getPossibleMovements($point){
		$nextMovements = [];
		// top right
		$nextPoint = ['row'=>($point['row'] - 2), 'col'=>($point['col'] + 1), 'string'=>'2u1r'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}

		// top left
		$nextPoint = ['row'=>($point['row'] - 2), 'col'=>($point['col'] - 1), 'string'=>'2u1l'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}

		// bottom right
		$nextPoint = ['row'=>($point['row'] + 2), 'col'=>($point['col'] + 1), 'string'=>'2d1r'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}

		// bottom left
		$nextPoint = ['row'=>($point['row'] + 2), 'col'=>($point['col'] - 1), 'string'=>'2d1l'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}


		// left top
		$nextPoint = ['row'=>($point['row'] - 1), 'col'=>($point['col'] - 2), 'string'=>'1u2l'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}

		// right top
		$nextPoint = ['row'=>($point['row'] - 1), 'col'=>($point['col'] + 2), 'string'=>'1u2r'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}

		// left bottom
		$nextPoint = ['row'=>($point['row'] + 1), 'col'=>($point['col'] - 2), 'string'=>'1d2l'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}

		// right bottom
		$nextPoint = ['row'=>($point['row'] + 1), 'col'=>($point['col'] + 2), 'string'=>'1d2r'];
		if( $this->checkPosition($nextPoint) ){
			$nextMovements[] = $nextPoint;
		}

		return $nextMovements;
	}

	protected function checkPosition($point){
		if( !isset($this->map[$point['row']][$point['col']]) ){
			return false;
		}
		if( $this->map[$point['row']][$point['col']] == '#' ){
			return false;
		}
		if( isset($this->minJumpsMap[$point['row'].'#'.$point['col']]) ){
			return false;
		}
		return true;
	}

	private function connectSocket(){
		$this->socket = fsockopen('52.49.91.111', 2003);
		stream_set_blocking($this->socket, 0);
		stream_set_blocking(STDIN, 0);
	}
}