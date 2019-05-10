<?php
class JDiff
{
    /** @var \SeleniumClient\WebDriver */
    protected $driver;
    /** @var array */
    protected $urls;
    protected $session_id;
    protected $passes;
    protected $failures = array();
    protected $compare_a;
    protected $compare_b;
    protected $base_url;
    /** @var string */
    protected $on_page_load_script;
    protected $start_time;

    /**
     * @param array $urls
     */
    function __construct($urls = array()){
        $this->urls = $urls;
    }

    /**
     * @param \SeleniumClient\WebDriver $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return \SeleniumClient\WebDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param array $urls
     */
    public function setUrls($urls)
    {
        $this->urls = $urls;
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * Sets up a driver if not already set.
     */
    public function setupWebDriver(){
        if(!$this->driver){
            $desiredCapabilities = new \SeleniumClient\DesiredCapabilities(BROWSER_TYPE);
            $this->driver = new \SeleniumClient\WebDriver($desiredCapabilities);
        }
        //make sure driver is running
        if(!$this->driver->status()){
            die("Error: Selenium Server is not running.\n");
        }
    }

    /**
     * @param $key
     * @param $url
     */
    protected function takeScreenshot_old($key, $url){

        $this->setupWebDriver();

        $dir = DIR_SCREENSHOTS . $this->session_id;

        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
        }


        $this->driver->get($url);
        sleep(5);
        $this->driver->executeScript($this->on_page_load_script);
        $this->passes++;

        sleep(1);
        $this->driver->screenshot_put(DIR_SCREENSHOTS . $this->session_id . "/$key.png");
        $this->driver->quit();
    }

    /**
     * @param $arr
     */
    protected function printScreenshotResults($arr){
        foreach($arr as $v){
            print "-----------------------------------------------------\n";
            print $v['url'] . "\n";
            print "PASS\n";
        }
        $endTime = microtime(true);
        $time = round($endTime - $this->start_time, 3);

        print "-----------------------------------------------------\n";
        print sizeof($arr) . " passes, 0 failures, $time sec \n\n";
    }

    /**
     * @param $url
     */
    public function takeScreenshot($url){

        $this->setupWebDriver();

        if(!file_exists(DIR_TMP)){
            mkdir(DIR_TMP, 0777, true);
        }
        $file = DIR_TMP . AString::randString(null, 20);
        while(file_exists($file)){
            $file = DIR_TMP . AString::randString(null, 20);
        }

        $this->driver->get($url);
        sleep(5);
        $this->driver->executeScript($this->on_page_load_script);
        sleep(1);
        $this->driver->screenshot_put($file);
        $this->driver->quit();

        //send response
        $response = array(
            'success' => true,
            'file' => $file
        );

        print(json_encode($response, true));
    }

    /**
     * Loops through $urls and takes screenshots. Makes sure it's a 200 response.
     * Will spawn background child processes, up to MAX_DRIVERS, to handle each URL request.
     */
    public function getScreenshots(){

        $this->session_id = time();
        $this->start_time = microtime(true);
        $this->failures = array();
        $this->passes = 0;
        $num_drivers = 0;
        $max_drivers = MAX_DRIVERS;
        $results = array();
        $running_processes = array();

        print "Session ID: " . $this->session_id . "\n";
        print "Fetching screenshots...\n";
        foreach($this->urls as $k => $v){
            $url = $this->constructUrl($k);
            $process = new Process(DIR_JDIFF . "./jdiff --screenshotOnly $url");
            $process->run();
            array_push($running_processes, $process);
        }



    }


    /**
     * @param $a
     * @param $b
     */
    public function compareFolders($a, $b=null){
        $b = $b ? $b : $this->session_id;

        $this->compare_a = $a;
        $this->compare_b = $b;
        $this->session_id = time();
        $startTime = microtime(true);
        $this->failures = array();
        $this->passes = 0;

        print "Comparing screenshots from $a to $b...\n";

        //make sure directories exist
        JDiffFile::require_dir(DIR_SCREENSHOTS . $a);
        JDiffFile::require_dir(DIR_SCREENSHOTS . $b);

        //loop through URLs
        for($i = 0; $i < sizeof($this->urls); $i++){

            $route = $this->constructUrl($i);
            print "-----------------------------------------------------\n";
            print "$route\n";

            $result = $this->compareImages(DIR_SCREENSHOTS . $a, DIR_SCREENSHOTS . $b, $i);
            print "$result\n";
        }

        $endTime = microtime(true);
        $time = round($endTime - $startTime, 3);

        print "-----------------------------------------------------\n";
        print $this->passes . " passes, " . sizeof($this->failures) . " failures, $time sec \n\n";

        if(!empty($this->failures)){
            $this->writeResultsFile();
            $this->showResultsPage();
        }

    }

    /**
     * @param $dirA
     * @param $dirB
     * @param $key
     * @return array|bool|string
     */
    protected function compareImages($dirA, $dirB, $key){

        $fileA = $dirA . "/$key.png";
        $fileB = $dirB . "/$key.png";
        $sessionA = str_replace(DIR_SCREENSHOTS, '', $dirA);
        $sessionB = str_replace(DIR_SCREENSHOTS, '', $dirB);

        //see if files exist
        if(!file_exists($fileA) && !file_exists($fileB)){
            return true;
        } else if (!file_exists($fileA) && file_exists($fileB)){
            $this->failures[] = $key;
            return "FAIL: Screenshot for $sessionA does not exist.";
        } else if (file_exists($fileA) && !file_exists($fileB)){
            $this->failures[] = $key;
            return "FAIL: Screenshot for $sessionB does not exist.";
        }

        //see if images match using perceptualdiff
        $pd = new PerceptualDiff();
        $res = $pd->compare($fileA, $fileB);

        if($res){
            $this->passes++;
        } else {
            $this->failures[] = $key;
        }

        return $pd->getResponse();
    }

    protected function showResultsPage(){
        $this->setupWebDriver();
        $this->driver->get(URL_JDIFF . "results.php?compare=" . $this->compare_a . '-' . $this->compare_b);
    }

    /**
     * Writes text file with images that failed for the results page.
     */
    protected function writeResultsFile(){
        if(!file_exists(DIR_RESULTS)) mkdir(DIR_RESULTS, 0777, true);
        $filename = DIR_RESULTS . $this->compare_a . '-' . $this->compare_b;
        $data = "";

        foreach($this->failures as $v){
            $f1 = DIR_SCREENSHOTS . $this->compare_a . "/$v.png";
            $f2 = DIR_SCREENSHOTS . $this->compare_b . "/$v.png";
            $url = $this->constructUrl($v);
            $data .= "$url|$f1|$f2\n";
        }

        file_put_contents($filename, $data);
    }

    /**
     * @param $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->base_url = $baseUrl;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    protected function requireDriver(){
        if(!$this->driver){
            die("Error: You must provide a WebDriver using JDiff::setDriver() or JDiff::setupWebDriver()\n");
        }
    }

    /**
     * Returns full URL path.
     * @param $key
     * @return string
     */
    protected function constructUrl($key){
        return $this->base_url . $this->urls[$key];
    }

    /**
     * String of javascript to be executed on page after DOM is loaded.
     * @param string $onPageLoadScript
     */
    public function setOnPageLoadScript($onPageLoadScript)
    {
        $this->on_page_load_script = $onPageLoadScript;
    }

    /**
     * @return string
     */
    public function getOnPageLoadScript()
    {
        return $this->on_page_load_script;
    }

}
