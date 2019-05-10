<?php


class Process
{
    /** @var string */
    protected $command;
    /** @var string */
    protected $logfile;
    /** @var string */
    protected $errfile;
    protected $output;
    protected $process; //handle to the process
    /** @var array */
    protected $env;
    protected $status;

    /**
     * @param null $command
     * @param array $env
     */
    function __construct($command = null, $env = array()) {
        $this->command = $command;
        $this->env = $env;
    }

    /**
     * Sets the log file to a randomly named file that doesn't already exit.
     */
    protected function setLogfile() {
        if(!file_exists(DIR_TMP)){
            mkdir(DIR_TMP, 0777, true);
        }
        $this->logfile = DIR_TMP . AString::randString(null, 20);
        while(file_exists($this->logfile)){
            $this->logfile = DIR_TMP . AString::randString(null, 20);
        }
    }

    /**
     * @return string
     */
    public function getLogFile(){
        return $this->logfile;
    }

    /**
     * @param array $env
     */
    public function setEnv($env) {
        $this->env = $env;
    }

    /**
     * @return array
     */
    public function getEnv() {
        return $this->env;
    }

    /**
     * @param string $errfile
     */
    public function setErrfile($errfile) {
        $this->errfile = $errfile;
    }

    /**
     * @return string
     */
    public function getErrfile() {
        return $this->errfile;
    }



    /**
     * Returns the output (STDOUT) of the process that has occurred so far.
     * @return mixed
     */
    public function getOutput(){
        if($this->output === NULL){
            $this->output = file_get_contents($this->logfile);
        }
        return $this->output;
    }

    /**
     * Gets the output from the process and attempts to json_decode it. If it contains success key, returns that value.
     * Returns NULL if there is no output, or if the output is not valid JSON.
     * @return bool|NULL
     */
    public function getJSONSuccess(){
        $output = $this->getJSONResponse();
        if($output !== NULL){
            $output = $output['success'] ? true : false;
        }
        return $output;
    }


    /**
     * Gets the output from the process and attempts to json_decode it. Returns NULL if there is no output, or if the
     * output is not valid JSON.
     * @return bool|NULL
     */
    public function getJSONResponse(){
        return json_decode($this->getOutput());
    }

    /**
     * @param string $command
     */
    public function setCommand($command) {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getCommand() {
        return $this->command;
    }

    /**
     * Starts/executes the command in a background child process.
     */
    public function run(){
        $this->setLogfile();
        $this->errfile = $this->logfile . '-err';

        $descriptors = array(
            array("pipe", "/dev/null"),
            array("file", $this->logfile, "w"),
            array("file", $this->errfile, "a")
        );

        $this->process = proc_open($this->command, $descriptors, $pipes, null, $this->env);
    }

    /**
     * @return array|null
     */
    public function getStatus(){
        $this->updateStatus();
        return $this->status;
    }

    /**
     * Updates the status property.
     */
    public function updateStatus(){
        if(!is_resource($this->process)){
            $this->status = null;
            return;
        }
        $this->status = proc_get_status($this->process);
    }

    /**
     * Closes the handle to the resource. Unless it's been forked with &, this will hang the application
     * until the process is finished.
     */
    public function close(){
        if(is_resource($this->process)){
            proc_close($this->process);
        }
    }

    /**
     * @return resource
     */
    public function getProcess(){
        return $this->process;
    }

}
