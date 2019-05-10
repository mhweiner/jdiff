<?php

require_once('config.php'); /** @var array $routes */
require_once('bootstrap.php');

//instantiate and set up dependencies
$jdiff = new JDiff($routes);
$jdiff->setOnPageloadscript($onPageLoadScript);

run($argv, $jdiff);


/**
 * Controller
 * @param $argv
 * @param $jdiff
 */
function run($argv, JDiff $jdiff){

    $compare = null;
    $url = null;

    //loop through args
    foreach($argv as $k => $v){
        switch($v){
            case "--screenshotOnly": //stores response in log in tmp folder, only called by JDiff
                if(empty($argv[$k + 1])){
                    die("Error: --screenshotOnly requires 1 argument (the url to take screenshot of)\n");
                }
                $jdiff->takeScreenshot($argv[$k + 1]);
                return;
            case "--compareOnly":
                printHeader();
                if(empty($argv[$k + 1]) || empty($argv[$k + 2])){
                    die("Error: --compareOnly requires 2 arguments (2 sessions to compare)\n");
                }
                $jdiff->compareFolders($argv[$k + 1], $argv[$k + 2]);
                return;
            case "--compare":
                if(empty($argv[$k + 1])){
                    die("Error: --compare requires 1 argument (the session to compare the current session to)\n");
                }
                $compare = $argv[$k + 1];
                break;
            case "--url":
                if(empty($argv[$k + 1])){
                    die("Error: --url requires 1 argument (the base url to use)\n");
                }
                if(strpos($argv[$k + 1], 'http') === false){
                    die("Error: --url requires 1 argument, which must start with http\n");
                }
                $url = $argv[$k + 1];
        }
    }

    printHeader();

    //at this point, --url is required, since we're getting screenshots, since --compareOnly isn't present
    if(!$url){
        die("Error: Argument --url (base url) is required\n");
    }

    $jdiff->setBaseUrl($url);
    $jdiff->getScreenshots();

    if($compare){
        $jdiff->compareFolders($compare);
    }
}

function printHeader(){
    print "=====================================================\n";
    print "JDiff 0.5, Visual diff automated testing tool.\n";
    print "=====================================================\n";
}