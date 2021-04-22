<?php


$cmd = 'dig +short steam-origin.contest.tuenti.net';
$serverIp = trim(shell_exec($cmd));

var_dump($serverIp);


$cmd = "curl http://pre.steam-origin.contest.tuenti.net:9876/games/cat_fight/get_key --resolve 'pre.steam-origin.contest.tuenti.net:".$serverIp."'";
$gameKey = trim(shell_exec($cmd));

$key = json_decode($gameKey, 1);


file_put_contents('testResult.txt',$key['key']);
file_put_contents('submitResult.txt',$key['key']);