<?

/**
 * Crayd Session
 * 
 * Session helper
 * Should be accessible as static functions, like registry
 * 
 * TODO: Consider using serialize..
 * 
 * @author vee@gate00.net
 */
class Crayd_Session {

    /**
     * Getter
     * @param string $key 
     */
    static public function get($key) {
        if (!Crayd_Registry::get('config')->session) {
            iQuit('Session is not enabled');
        }

        return $_SESSION[$key];
    }

    /**
     * Setter
     * @param string $key
     * @param mixed $value 
     */
    static public function set($key, $value) {
        if (!Crayd_Registry::get('config')->session) {
            iQuit('Session is not enabled');
        }

        $_SESSION[$key] = $value;
    }

}