<?
require_once('config.php');
require_once('bootstrap.php');

if(empty($_GET['compare'])){
    die("Nothing to compare.");
}
$file = DIR_RESULTS . $_GET['compare'];
$comparisons = explode("\n", file_get_contents($file));
foreach($comparisons as $k=>$v){
    $comparisons[$k] = explode("|", $v);
}

//construct data for view
$data = array();
foreach($comparisons as $v){
    if(!empty($v) && $v[0]){
        $data[] = array(
            "url" => $v[0],
            "img1" => str_replace(DIR_JDIFF, URL_JDIFF, $v[1]),
            "img2" => str_replace(DIR_JDIFF, URL_JDIFF, $v[2]),
        );
    }
}

$view = new ResultsView();
$view->printContent($data);