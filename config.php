<?php

define('DIR_JDIFF', __DIR__ . '/'); //location where jdiff is stored
define('DIR_SCREENSHOTS', DIR_JDIFF . 'screenshots/');
define('DIR_RESULTS', DIR_JDIFF . 'results/');
define('BROWSER_TYPE', 'firefox');
define('URL_JDIFF', 'http://jdiff.marvel.com/'); //url where jdiff is served from
define('MAX_DRIVERS', 10); //max number of Selenium Drivers to have running at one time
define('DIR_TMP', DIR_JDIFF . 'tmp/');

$routes = array(
    '/',
    '/comics',
    '/movies/movie/176/iron_man_3'
);

$onPageLoadScript = <<< EOT

//remove ads
$('.ad').html('')
//stop all rotators and go to first slide
$('.pwrSldr').each(function(){var pwrSldr = $(this).data('pwrSldr'); pwrSldr.stop();pwrSldr.scrollToPage(0);});

EOT;
