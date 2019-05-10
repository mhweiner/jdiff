<?php
/**
 * Atomi Framework Autoloader (PSR-0 compliant)
 */

namespace atomi\core;

class AutoLoader
{

    public function __construct($includePath = array()){
        //set include path
        if(is_array($includePath) && sizeof($includePath)){
            set_include_path(implode(PATH_SEPARATOR, $includePath));
        }

        //register autoloader
        $this->register();
    }

    /**
     * Attempts to find and load the file. Uses PSR-0 standard, with the following extra rules:
     * <ul>
     *   <li>Any class that ends in Controller, Model, View, Manager, or Interface is looked for in that folder.</li>
     *   <li>If the class is not found, also look in the relative lib directory (adds /lib to the end of the location).</li>
     * </ul>
     * @param $class
     * @param bool $suppress_errors
     * @return string
     */
    public function load($class, $suppress_errors = false){
        $location = '';
        $namespace = '';

        //get rid of first \
        $class = ltrim($class, '\\');

        //get position of last \
        //if it exists, also get file location based on namespace.
        if($lastSepPos = strrpos($class, '\\')){
            $namespace = substr($class, 0, $lastSepPos);
            $class = substr($class, $lastSepPos + 1);
            $location  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        //if class ends in Controller, Model, View, Manager, or Interface, look in that directory.
        $shortcuts = array('Controller','Model','View','Manager','Interface');
        $shortcut_folder = '';
        require_once('AString.php');
        foreach($shortcuts as $v){
            if(\AString::endsWith($v, $class)){
                $shortcut_folder = $v . DIRECTORY_SEPARATOR;
            }
        }

        $file1 = $location . $shortcut_folder . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        $file2 = $location . 'lib' . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

        if(stream_resolve_include_path($file1)){
            require $file1;
            return true;
        } else if(stream_resolve_include_path($file2)){
            require $file2;
            return true;
        } else {
            if(!$suppress_errors){
                trigger_error('AutoLoader: Cannot find any file for ' . $class . '. Please read the documentation on autoloading.');
            }
            return false;
        }

    }

    public function register(){
        spl_autoload_register(array($this, 'load'));
    }

    public function unRegister(){
        spl_autoload_unregister(array($this, 'load'));
    }

}