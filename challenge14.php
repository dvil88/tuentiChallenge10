<?php
/* Challenge 14 - Public Safety Bureau
 *  
 *
 * Diego Villar
 *		Email:		dvil88@gmail.com
*/

$t = new paxos();
$t->run();


class paxos{
	private $sockets = [];

	public function __construct(){
	}

	public function run(){
		for( $i = 0; $i<7; $i++ ){

			$socket = fsockopen('52.49.91.111', 2092);
			stream_set_blocking($socket, 0);
			stream_set_blocking(STDIN, 0);

			$read = array($socket, STDIN);
			$write  = NULL;
			$except = NULL;

			if( !is_resource($socket) ){ return; }
			$num_changed_streams = @stream_select($read, $write, $except, null);
			if( feof($socket) ){ return; }


			if( false === $num_changed_streams ){
				/* Error handling */
				die;
			} elseif ($num_changed_streams > 0) {
				$data = trim(fread($socket, 4096));
				// echo $data.PHP_EOL;


				if( preg_match('/SERVER ID: (?<serverId>[0-9]+)/msi', $data, $m) ){
					$serverId = $m['serverId'];
					
					$this->sockets[$serverId] = $socket;

					$pid = pcntl_fork();
					if( !$pid ){ continue; }

					if( $m['serverId'] != 9 ){
						return $this->slave($serverId);
					}

					return $this->master($serverId);
				}
			}
		}
	}

	private function master($serverId){
		shell_exec('rm -rf slaves/*');
		$serversInjected = 0;

		do {
			$id = (int)(microtime(1)*1000000);

			$read = array($this->sockets[$serverId], STDIN);
			$write  = NULL;
			$except = NULL;

			if( !is_resource($this->sockets[$serverId]) ){ return; }
			$num_changed_streams = @stream_select($read, $write, $except, null);
			if( feof($this->sockets[$serverId]) ){ return; }


			if( false === $num_changed_streams ){
				/* Error handling */
				die;
			} elseif ($num_changed_streams > 0) {
				$data = trim(fread($this->sockets[$serverId], 4096));
				// echo $data.PHP_EOL;


				if( preg_match('/LEARN \{servers: \[[0-9,]+\], secret_owner: 9\}/msi', $data) ){
					preg_match('/SECRET IS: (?<secret>.*?)$/msi', $data, $m);


					echo 'Key: '.$m['secret'].PHP_EOL;

					file_put_contents('testResult.txt', $m['secret']);
					file_put_contents('submitResult.txt', $m['secret']);

					break;
				}

				$this->slaves = array_diff(scandir('slaves'), ['.', '..']);

				if( preg_match('/ROUND (?<round>[0-9]+):.*?LEARN (?<code>\{.*\})/msi', $data, $m) ){
					preg_match('/\[(?<servers>.*?)\], secret_owner: (?<secret>[0-9]+)/msi', $m['code'], $m2);
					$servers = explode(',', $m2['servers']);
					$secret = $m2['secret'];
					$randomSecret = $servers[array_rand($servers)];


					foreach( $servers as $server ){
						if( $server == $serverId ){ continue; }
						$cmd = 'PREPARE {'.$id.','.$serverId.'} -> '.$server."\r\n";
						$this->sendCommand($serverId, $cmd);
					}

					if( in_array($server, $this->slaves ) ){
						unlink('slaves/'.$server);
						$serversInjected++;
					}

					$injectServer = false;
					if( $this->slaves ){
						$slave = array_shift($this->slaves);
						$injectServer = $slave;
					}

					foreach( $servers as $server ){
						$serversInject = $servers;
						if( $injectServer ){
							$serversInject = array_merge($servers, [$injectServer]);
							$serversInject = array_unique($serversInject);
							sort($serversInject);
						}

						if( $server == 9 ){ continue; }
						if( $serversInjected < 6 ){
							$cmd = 'ACCEPT {id: {'.$id.','.$serverId.'}, value: {servers: ['.implode(',',$serversInject).'], secret_owner: '.$secret.'}} -> '.$server."\r\n";
						} else {
							$cmd = 'ACCEPT {id: {'.$id.','.$serverId.'}, value: {servers: ['.implode(',',$serversInject).'], secret_owner: 9}} -> '.$server."\r\n";
						}
						
						$this->sendCommand($serverId, $cmd);
					}
				}
			}
		} while(true);
	}

	private function slave($serverId){
		file_put_contents('slaves/'.$serverId, '');

		do {
			$id = (int)(microtime(1)*1000000);

			$read = array($this->sockets[$serverId], STDIN);
			$write  = NULL;
			$except = NULL;

			if( !is_resource($this->sockets[$serverId]) ){ return; }
			$num_changed_streams = @stream_select($read, $write, $except, null);
			if( feof($this->sockets[$serverId]) ){ return; }


			if( false === $num_changed_streams ){
				/* Error handling */
				die;
			} elseif ($num_changed_streams > 0) {
				$data = trim(fread($this->sockets[$serverId], 4096));

				if( preg_match('/LEARN \{servers: \[[0-9,]+\], secret_owner: 9\}/msi', $data) ){
					break;
				}
			}
		} while(true);
	}

	private function sendCommand($socketId, $cmd){
		fwrite($this->sockets[$socketId], $cmd);
	}
}