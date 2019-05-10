<?php
/**
 * Atomi Framework String Helper
 */
class AString
{
    /**
     * Generates a random string of length $length. It produces a human-readable string, with
     * only uppercase letters, numbers, and no 0, O, 1, or L.
     * @static
     * @param $length //default: 6
     * @return string
     */
    public static function randHumanReadableString($length=6){
        return self::randString("ABCDEFGHJKLMNOPQRSTUVWXYZ23456789", $length);
    }

    /**
     * Returns a random string of length $length using the character set $charset.
     * @static
     * @param $charset //default is [A-Za-z0-9]
     * @param $length
     * @return string
     */
    public static function randString($charset=null, $length=6){
        if(!$charset){
            $charset = 'abcdefghijklmnopqrstuvwzyzABCDEFGHIJKLMNOPQRSTUVWXYZ0987654321';
        }
        $output = '';
        if($length < 1) $length=10;
        $len = strlen($charset)-1;
        for($i=0; $i<$length; $i++) $output .= substr($charset, rand(0, $len), 1);
        return $output;
    }

    /**
     * Replaces $limit instances of $needle in $haystack from left to right.
     * @static
     * @param $needle
     * @param $replace
     * @param $haystack
     * @param int $limit
     * @return mixed
     */
    public static function str_replace($needle, $replace, $haystack, $limit=1){
        for($i=0; $i<$limit; $i++){
            $pos = strpos($haystack, $needle);
            if ($pos !== false) {
                $haystack = substr_replace($haystack,$replace,$pos,strlen($needle));
            }
        }
        return $haystack;
    }

    /**
     * Removes whitespace from beginning and end of strings. Capable of taking in an entire array.
     * @param $a
     * @return array|string
     */
    public static function xtrim($a){
        if(is_array($a)){
            foreach($a as $key=>$value)
                $a[$key]=trim($value);
            return $a;
        } else {
            return trim($a);
        }
    }

    /**
     * @param $a
     * @param int $maxlen
     * @return string
     */
    public static function breakupLongString($a, $maxlen = 40){
        $a=str_split($a);
        $count=0;
        foreach($a as $key=>$value){
            if($value==" "){
                $count=0;
            } else {
                $count++;
            }
            if($count > $maxlen){
                $a[$key]=$value."<br>";
                $count=0;
            }
        }
        return implode("",$a);
    }

    /**
     * Returns a url friendly version of a title string for search engine optimization.
     * @param $a
     * @return string
     */
    public static function urlFriendlyTitle($a){
        $result = '';
        $a=str_replace(" ","-",$a);
        $a=str_split($a);
        foreach($a as $v)
            if(preg_match("/[-0-9a-z]/i", $v))
                $result.=$v;
        return $result;
    }

    /**
     * @param $input
     * @param $characterLength
     * @param $break
     * @param string $pad
     * @return string
     */
    public static function truncate($input, $characterLength, $break, $pad = '...'){
        if(strlen($input) <= $characterLength) return $input;
        //if $break is blank, just do substr
        if(empty($break)){
            return substr($input, 0, $characterLength) . $pad;
        }
        if(false !== ($breaktpt=strpos($input, $break, $characterLength))){
            if($breaktpt<strlen($input)-1){
                $input=substr($input, 0, $breaktpt).$pad;
            }
        }
        return $input;
    }

    /**
     * Pads numbers with 0's
     * @static
     * @param $number
     * @param $n
     * @return string
     */
    public static function numberPad($number,$n) {
        return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
    }

    /**
     * Normalizes dollar amounts. Takes in a string or float $a, and outputs similar to $9.95
     * @static
     * @param $a //string or float
     * @param bool $show_dollar_sign
     * @return string
     */
    public static function formatMoney($a, $show_dollar_sign=true){
        $a = number_format($a, 2);
        if($show_dollar_sign)
            return '$' . $a;
        else
            return $a;
    }

    /**
     * Converts underscored strings to capital with spaces
     * ex: first_name to First Name
     * @static
     * @param $str
     * @return string
     */
    public static function underscoreToCapital($str){
        return ucwords(strtolower(str_replace('_', ' ', $str)));
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
     * @param string $str //String in camel case format
     * @return string
     */
    public static function fromCamelCase($str) {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     * @param string $str // String in underscore format
     * @param bool $capitalise_first_char //If true, capitalise the first char in $str
     * @return string
     */
    public static function toCamelCase($str, $capitalise_first_char = false) {
        if($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }

    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function startsWith($needle, $haystack){
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function endsWith($needle, $haystack){
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        $start  = $length * -1; //negative
        return (substr($haystack, $start) === $needle);
    }

    /**
     * Converts text from a textarea into HTML. Converts URLs to hyperlinks and replaces newlines with <br>.
     * @static
     * @param $input
     * @return mixed
     */
    public static function textToHTML($input){
        //---links---//
        $types=array("http://","https://");
        foreach($types as $type){
            $pos=0;
            while($pos<strlen($input)){
                //get the position of the next type
                $a=strpos($input,$type,$pos);
                //if it is not found, increment position by strlen(type)
                if($a===false){
                    //it is not found
                    $pos+=strlen($type);
                } else {
                    //it is found
                    $start=$a;
                    //find the end of the url. that is either the next space, \n, \t, \r, <, >, [, ], {, }, |, @
                    $char_array=array(" ", "\n", "\t", "\r", "<", ">", "[", "]", "{", "}", "|", "@");
                    $b=array();
                    foreach($char_array as $k=>$v){
                        $temp=strpos($input,$v,$a);
                        if($temp!==false)
                            $b[$k]=$temp;
                    }

                    //if $b is empty, that means that the end of the url is the end of the $input string
                    if(sizeof($b)==0){
                        $end=strlen($input);
                    } else {
                        //get the lowest value in the $b array
                        arsort($b);
                        $end=array_pop($b);
                    }

                    //make sure it isn't already a link--must not have a (") in front of it, or be surrounded by (>) and (<)
                    if($input[$start-1]=='"' || ($input[$start-1]==">" && $input[$end]=="<")){
                        $pos=$start+strlen($type);
                    } else {
                        // DO THE REPLACING!
                        $url=substr($input,$start,$end-$start);
                        $link='<a href="'.$url.'" target="_blank">'.$url.'</a>';
                        $input=str_replace($url,$link,$input);
                        $pos=$start+strlen($link);
                    }
                }
            }
        }

        //---new lines---//
        $input=str_replace("\n","<br>",$input);

        return $input;
    }

    /**
     * TODO: Finish this function
     * NOT FINISHED.
     * Returns a multi-dimensional array representing anything.
     * @static
     * @param $o
     * @return string
     */
    public static function varDump($o) {
        $nodes = array();

        if(is_array($o)){
            foreach($o as $k=>$v){
                $nodes[] = array('type' => 'Array', 'key' => $k, 'value' => self::varDump($v));
            }
        } else if(is_object($o)){
            $vars = get_object_vars($o);
            foreach($vars as $k=>$v){
                $nodes[] = array('type' => get_class($o), 'key' => $k, 'value' => self::varDump($v));
            }
        } else {
            return array('type' => '', 'key'=> '', 'value' => strval($o));
        }
        return $nodes;
    }
}
