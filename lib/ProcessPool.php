<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mweiner
 * Date: 9/24/13
 * Time: 10:37 AM
 * To change this template use File | Settings | File Templates.
 */
class ProcessPool
{
    /** @var array */
    protected $processes;
    /** @var bool */
    protected $is_running;
    /** @var object */
    protected $callback;
    /** @var int //milliseconds */
    protected $check_interval;

    /**
     * @param boolean $is_running
     */
    public function setIsRunning($is_running) {
        $this->is_running = $is_running;
    }

    /**
     * @return boolean
     */
    public function getIsRunning() {
        return $this->is_running;
    }

    /**
     * @param array $processes
     */
    public function setProcesses($processes) {
        $this->processes = $processes;
    }

    /**
     * @return array
     */
    public function getProcesses() {
        return $this->processes;
    }

    /**
     * If a callback is defined, the class will wait until all processes are finished before calling the callback
     * and finishing.
     * @param object $callback
     */
    public function setCallback($callback) {
        $this->callback = $callback;
    }

    /**
     * If a callback is defined, the class will wait until all processes are finished before calling the callback
     * and finishing.
     * @return object
     */
    public function getCallback() {
        return $this->callback;
    }
}
