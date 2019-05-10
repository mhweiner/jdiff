<?php
class JDiffFile{

    /**
     * Returns a directory listing as an array.
     * @param $directory
     * @param bool $recursive
     * @return array
     */
    public static function directoryToArray($directory, $recursive = false) {
        $array_items = array();
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($directory. "/" . $file)) {
                        if($recursive) {
                            $array_items = array_merge($array_items, self::directoryToArray($directory. "/" . $file, $recursive));
                        }
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    } else {
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    }
                }
            }
            closedir($handle);
        }
        return $array_items;
    }

    /**
     * If directory doesn't exist, dies and cries.
     * @param $dir
     */
    public static function require_dir($dir){
        if(!file_exists($dir)){
            die("Error: Directory $dir does not exist T_T\n");
        }
    }
}