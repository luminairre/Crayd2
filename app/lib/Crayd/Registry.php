<?
/**
 * Crayd Registry class
 * 
 * Handles registry...
 * I mean global vars
 * 
 * @author vee@gate00.net
 */
class Crayd_Registry {
    private static $data;
    
    /**
     * Returns saved variable named $key
     * @param string $key
     * @return mixed
     */
    public static function get($key) {
        if(!empty(self::$data[$key])) {
            return self::$data[$key];
        }
        return null;
    }
    
    /**
     * Saves variable to registry with the name $key
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public static function set($key, $value) {
        if(!empty($value)) {
            self::$data[$key] = $value;
            return true;
        }
        return false;
    }
}