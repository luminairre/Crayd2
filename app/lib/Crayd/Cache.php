<?

/**
 * Crayd Framework cache class
 * 
 * @author vee@gate00.net
 */
class Crayd_Cache {

    // Configurations
    var $configmtime;
    var $routemtime;
    var $cacheDir;
    var $expire = 300;
    private static $instance;

    /**
     * Factory
     * @return Crayd_Cache
     */
    public static function factory() {
        if (self::$instance === NULL) {
            self::$instance = new Crayd_Cache();
        }
        return self::$instance;
    }

    /**
     * Cache class constructor
     */
    public function __construct() {
        // gets config.php & route.php mtime, pretty much saves time i guess
        $appDir = Crayd_Registry::get('appDir');
        $this->configmtime = filemtime($appDir . '/config.php');
        $this->routemtime = filemtime($appDir . '/route.php');
        $this->cacheDir = $appDir . DS . 'cache';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777);
        }
    }

    /**
     * Sets expiration time (in second)
     * @param int $seconds
     */
    public function setExpiration($seconds) {
        $seconds = (int) $seconds;
        $this->expire = $seconds;
    }

    /**
     * set cache
     * @param string $key
     * @param mixed $var
     */
    public function set($key, $var, $group = 'cache') {
        $key = Crayd_Helper::alnum($key);
        $mtime = $this->configmtime . $this->routemtime;
        $filename = $this->cacheDir . DS . $group . '-' . $key . '-' . $mtime . '.php';
        // set store variables
        $data['timestamp'] = $_SERVER['REQUEST_TIME'];
        $data['data'] = $var;
        // serialize
        $writeable = serialize($data);
        // write to file
        $file = fopen($filename, 'w');
        if ($file) {
            fwrite($file, $writeable);
            fclose($file);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clears cache, if group is undefined then all cache will be cleared
     * @param string $group 
     */
    public function clear($key = null, $group = null) {
        $handle = opendir($this->cacheDir);
        $key = Crayd_Helper::alnum($key);
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if ($key == null && $group == null) {
                    unlink($this->cacheDir . DS . $file);
                } else if ($group == null) {
                    if (strpos($file, $key) !== false) {
                        unlink($this->cacheDir . DS . $file);
                    }
                } else if ($key == null) {
                    if (strpos($file, $group) !== false) {
                        unlink($this->cacheDir . DS . $file);
                    }
                } else {
                    if (strpos($file, $group . '-' . $key) !== false) {
                        unlink($this->cacheDir . DS . $file);
                    }
                }
            }
        }
        closedir($handle);
    }

    /**
     * @param string $key
     * @param string $group
     * @return mixed
     */
    public function get($key, $group = 'cache') {
        $key = Crayd_Helper::alnum($key);
        $mtime = $this->configmtime . $this->routemtime;
        $filename = $this->cacheDir . DS . $group . '-' . $key . '-' . $mtime . '.php';

        // check file existence
        clearstatcache();
        if (file_exists($filename)) {
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            $data = unserialize($contents);

            if ($data['timestamp'] > $_SERVER['REQUEST_TIME'] - $this->expire) {
                return $data['data'];
            } else {
                $this->clear($key, $group);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Stores the cache in plain text instead of serialized stuff
     * @param type $key
     * @param type $content
     * @param type $group 
     */
    public function plain($key, $content = null, $group = 'cache') {
        // set the variables
        $key = Crayd_Helper::alnum($key);
        $mtime = $this->configmtime . $this->routemtime;
        $filename = $this->cacheDir . DS . $group . '-' . $key . '-' . $mtime . '.php';
        // if content is null, means its a retrieval
        if ($content == null) {
            // check file
            if (file_exists($filename)) {
                $handle = fopen($filename, "r");
                $contents = fread($handle, filesize($filename));
                fclose($handle);
                // validate timestamp
                $timestamp = substr($contents, 0, 10);
                if ($timestamp > $_SERVER['REQUEST_TIME'] - $this->expire) {
                    $contents = substr($contents, 10);
                    return $contents;
                } else {
                    $this->clear($key, $group);
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // content exists, storing data..
            $file = fopen($filename, 'w');
            if ($file) {
                $content = $_SERVER['REQUEST_TIME'].$content;
                fwrite($file, $content);
                fclose($file);
                return true;
            } else {
                return false;
            }
        }
    }

}