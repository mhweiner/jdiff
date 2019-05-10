<?php

require_once('config.php'); /** @var array $routes */
require_once('bootstrap.php');

$process = new Process('/usr/bin/php testscript.php');
print "about to start process...\n";
$process->run();
print "started process...\n";

$running = true;
while($running){
    $status = $process->getStatus();
    var_dump($status);
    if($status['running'] === false || $status === NULL){
        $running = false;
    }
    sleep(1);
}

print "closing process...\n";
$process->close();

print "output:\n";
print $process->getOutput();


var_dump($process->getProcess());
var_dump($process->getStatus());
print "exiting\n";