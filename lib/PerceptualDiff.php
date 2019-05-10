<?php
/**
 * Wrapper for perceptualdiff
 */
class PerceptualDiff
{

    /** @var string */
    protected $response;

    /**
     * Returns true if they are perceptually identical.
     * @param $a
     * @param $b
     * @return array
     */
    public function compare($a, $b){
        $this->response = shell_exec("./perceptualdiff $a $b");
        if(strpos($this->response, 'FAIL') === false){
            $success = true;
            $this->response = "PASS";
        } else {
            $success = false;
        }

        //clean up response text
        $new_response = array();
        $this->response = explode("\n", $this->response);
        foreach($this->response as $v){
            if($v){
                $new_response[] = $v;
            }
        }
        $this->response = implode("\n", $new_response);

        return $success;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
